<?php

namespace App\Modules\Blockchain\Services;

use App\Modules\Blockchain\Models\BlockchainContract;
use App\Modules\Blockchain\Models\ContractSenderWallets;
use App\Modules\Blockchain\Models\BlockchainRpc;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3\Web3;
use Web3p\EthereumTx\Transaction;
use Illuminate\Support\Facades\Cache;
use App\Services\SystemSettingService;

class ContractSendService
{
    protected $web3;
    protected $contract;
    protected $config;


    public function __construct($contract_name, $wallet_name = 'merkle_tree')
    {
        // Use SystemSettingService to get Chain ID, falling back to env if needed
        $chainId = SystemSettingService::getChainId();

        // 轮询获取可用 RPC
        $eth_rpc = BlockchainRpc::where('chain_id', $chainId)
            ->whereNotIn('id', Cache::get('_BLOCK_RPC', []))
            ->where('status', 1)
            ->first();

        if (!$eth_rpc) {
            // 如果所有 RPC 都在排除列表中，清除缓存重试
            Cache::forget('_BLOCK_RPC');
            $eth_rpc = BlockchainRpc::where('chain_id', $chainId)
                ->whereNotIn('id', Cache::get('_BLOCK_RPC', []))
                ->where('status', 1)
                ->first();
        }

        if (!$eth_rpc) {
            throw new \RuntimeException("No available RPC found for chain ID {$chainId}");
        }

        $current_rpc_id = $eth_rpc->id;
        Cache::put('_CURRENT_RPC_ID', $current_rpc_id);
        
        $this->web3 = new Web3(new HttpProvider(new HttpRequestManager($eth_rpc->provider)));

        $blockchain_contract = BlockchainContract::where('name', $contract_name)->first();
        if (!$blockchain_contract) {
            throw new \RuntimeException("Blockchain contract '{$contract_name}' not found. 请先在 blockchain_contract 表配置对应合约。");
        }

        // 查找钱包：优先按 name 查，如果没传 name 或查不到，尝试查默认钱包
        $contract_sender_wallets = ContractSenderWallets::where('wallet_name', $wallet_name)->first();
        if (!$contract_sender_wallets && $wallet_name === 'merkle_tree') {
             // Fallback to default wallet if specific one not found
             $contract_sender_wallets = ContractSenderWallets::where('is_default', 1)->first();
        }

        if (!$contract_sender_wallets) {
            throw new \RuntimeException("Contract sender wallet '{$wallet_name}' not configured. 请在 contract_sender_wallets 表中配置发送钱包。");
        }

        $this->config = [
            'contract_address' => $blockchain_contract->address,
            'from_address' => $contract_sender_wallets->address,
            'gas_limit' => $eth_rpc->gas_limit ?? 300000,
            'gas_price' => $eth_rpc->gas_price ?? 5, // Gwei unit from DB usually? Code below converts it.
            'chain_id' => $eth_rpc->chain_id,
            'private_key' => trim(Crypt::decryptString($contract_sender_wallets->encrypted_private_key))
        ];

        // Log::info("Loading ABI from: " . base_path($blockchain_contract->abi_path));
        
        $abiContent = file_get_contents(base_path($blockchain_contract->abi_path));
        if (!$abiContent) {
             throw new \RuntimeException("ABI file not found or empty at: " . base_path($blockchain_contract->abi_path));
        }
        
        $abi = json_decode($abiContent, true);
        $this->contract = new Contract($this->web3->provider, $abi);
        $this->contract->at($this->config['contract_address']);
    }

    /**
     * 调用合约写入方法
     */
    public function writeContract($method, $parameters = [])
    {
        try {
            // 存储异步操作结果
            $nonce = null;
            $nonceError = null;

            // 获取nonce
            $this->web3->eth->getTransactionCount(
                $this->config['from_address'],
                'latest',
                function ($err, $count) use (&$nonce, &$nonceError) {
                    if ($err !== null) {
                        $nonceError = $err;
                        return;
                    }
                    $nonce = $count;
                }
            );

            if ($nonceError !== null) {
                throw new \Exception('获取nonce失败: ' . $nonceError->getMessage());
            }

            // nonce might be BigInteger or GMP object depending on web3 version
            if (!is_object($nonce) && !is_numeric($nonce)) {
                 // Try one more time/wait? Usually callback is sync for HttpProvider but good to be safe.
                 throw new \Exception('获取nonce失败: 返回结果无效');
            }

            $nonceStr = $nonce->toString();

            $contractInstance = $this->contract->at($this->config['contract_address']);
            
            // Encode parameters for the function call
            $data = '0x' . $contractInstance->getData($method, ...$parameters);
            
            if (empty($data)) {
                throw new \Exception('获取合约数据失败: data 为空');
            }

            // 准备交易数据
            $gasPriceWei = bcmul($this->config['gas_price'], '1000000000', 0); // Gwei to Wei

            $transaction = [
                'nonce' => '0x' . dechex((int)$nonceStr),
                'from' => $this->config['from_address'],
                'to' => $this->config['contract_address'],
                'data' => $data,
                'gas' => '0x' . dechex($this->config['gas_limit']),
                'gasPrice' => '0x' . dechex((int)$gasPriceWei),
                'chainId' => $this->config['chain_id']
            ];

            $privateKey = trim($this->config['private_key']);
            // 签名交易
            $privateKey = preg_replace('/^0x/', '', $privateKey);
            
            $tx = new Transaction($transaction);
            $tx->sign($privateKey);
            $rawTransaction = $tx->serialize();

            // 发送交易
            $txHash = null;
            $txError = null;

            $this->web3->eth->sendRawTransaction(
                '0x' . $rawTransaction,
                function ($err, $hash) use (&$txHash, &$txError) {
                    if ($err !== null) {
                        $txError = $err;
                        Log::error('SendRawTransaction Error', ['err' => $err]);
                        return;
                    }
                    $txHash = $hash;
                }
            );

            if ($txError !== null) {
                throw new \Exception('发送交易失败: ' . $txError->getMessage());
            }

            Log::channel('chain_event')->info("Transaction sent: $method", ['hash' => $txHash]);
            return $txHash;

        } catch (\Exception $e) {
            throw new \Exception('合约调用失败: ' . $e->getMessage());
        }
    }

    /**
     * 读取合约数据
     */
    public function readContract($method, $parameters = [])
    {
        try {
            $result = null;
            $error = null;

            // 构造方法参数数组
            // Note: web3.php call method usually expects callback as last arg
            $parameters[] = function ($err, $data) use (&$result, &$error) {
                if ($err !== null) {
                    $error = $err;
                    return;
                }
                $result = $data;
            };

            $this->contract->call($method, ...$parameters);

            if ($error !== null) {
                throw new \Exception($error->getMessage());
            }

            return $result;
        } catch (\Exception $e) {
            throw new \Exception('读取合约数据失败: ' . $e->getMessage());
        }
    }
}
