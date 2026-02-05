<?php

namespace App\Modules\Blockchain\Services;

use App\Helpers\CommonHelper;
use App\Modules\Blockchain\Models\BlockchainContract;
use App\Modules\Blockchain\Models\BlockchainRpc;
use App\Modules\Blockchain\Models\ContractSenderWallets;
use App\Modules\Blockchain\Models\TaxProcessorDispatchLog;
use App\Services\SystemSettingService;
use Illuminate\Support\Facades\Log;
use Web3\Contract;
use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;

class BonusPoolNotifyService
{
    public function sendPending(int $limit): void
    {
        if ($limit <= 0) {
            return;
        }

        $pending = TaxProcessorDispatchLog::where('status', 0)
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();

        if ($pending->isEmpty()) {
            return;
        }

        foreach ($pending as $item) {
            /** @var TaxProcessorDispatchLog $item */
            try {
                $locked = TaxProcessorDispatchLog::where('id', $item->id)
                    ->where('status', 0)
                    ->update([
                        'status' => 1,
                        'remark' => 'Sending notifyReward',
                    ]);
                if ($locked === 0) {
                    continue;
                }

                $amountWei = CommonHelper::toContractValue($item->market_amount, 18);
                $sender = new ContractSendService('BurnsBonusPool', 'market_amount');
                $txHash = $sender->writeContract('notifyReward', [$amountWei]);

                TaxProcessorDispatchLog::where('id', $item->id)->update([
                    'notify_transaction_hash' => $txHash,
                    'remark' => 'Transaction submitted',
                ]);

                Log::channel('chain_event')->info('notifyReward submitted', [
                    'id' => $item->id,
                    'tx_hash' => $txHash,
                    'amount' => $item->market_amount,
                ]);
            } catch (\Throwable $e) {
                TaxProcessorDispatchLog::where('id', $item->id)->update([
                    'status' => 3,
                    'remark' => $e->getMessage(),
                ]);

                Log::channel('chain_event')->error('notifyReward submit failed', [
                    'id' => $item->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function confirmPending(int $limit): void
    {
        if ($limit <= 0) {
            return;
        }

        $pending = TaxProcessorDispatchLog::where('status', 1)
            ->whereNotNull('notify_transaction_hash')
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();

        if ($pending->isEmpty()) {
            return;
        }

        $web3 = $this->makeWeb3();
        $contract = $this->getContractInfo();
        $fromAddress = $this->getSenderAddress();

        foreach ($pending as $item) {
            /** @var TaxProcessorDispatchLog $item */
            try {
                $receipt = $this->getTransactionReceipt($web3, $item->notify_transaction_hash);
                if (!$receipt) {
                    continue;
                }

                $isSuccess = ($receipt->status ?? '') === '0x1'
                    || (isset($receipt->status) && hexdec($receipt->status) === 1);

                if ($isSuccess) {
                    TaxProcessorDispatchLog::where('id', $item->id)->update([
                        'status' => 2,
                        'remark' => 'Transaction confirmed',
                    ]);
                    continue;
                }

                $amountWei = CommonHelper::toContractValue($item->market_amount, 18);
                $data = $this->buildContractData($web3, $contract, 'notifyReward', [$amountWei]);
                $revertReason = $this->getRevertReason(
                    $web3,
                    $contract->address,
                    $data,
                    $receipt->blockNumber ?? null,
                    $fromAddress
                );

                TaxProcessorDispatchLog::where('id', $item->id)->update([
                    'status' => 3,
                    'remark' => $revertReason ? "Transaction failed: {$revertReason}" : 'Transaction failed',
                ]);
            } catch (\Throwable $e) {
                TaxProcessorDispatchLog::where('id', $item->id)->update([
                    'status' => 3,
                    'remark' => $e->getMessage(),
                ]);
            }
        }
    }

    private function makeWeb3(): Web3
    {
        $chainId = SystemSettingService::getChainId();
        $rpc = BlockchainRpc::where('chain_id', $chainId)
            ->where('status', 1)
            ->first();

        if (!$rpc) {
            throw new \RuntimeException("No active RPC found for chain_id: {$chainId}");
        }

        return new Web3(new HttpProvider(new HttpRequestManager($rpc->provider, 30)));
    }

    private function getTransactionReceipt(Web3 $web3, string $txHash): ?object
    {
        $receipt = null;
        $web3->eth->getTransactionReceipt($txHash, function ($err, $result) use (&$receipt) {
            if (!$err) {
                $receipt = $result;
            }
        });

        $wait = 0;
        while ($receipt === null && $wait < 10) {
            usleep(500000);
            $wait++;
        }

        return $receipt;
    }

    private function getContractInfo(): BlockchainContract
    {
        $chainId = SystemSettingService::getChainId();
        $contract = BlockchainContract::where('name', 'BurnsBonusPool')
            ->where('chain_id', $chainId)
            ->first();

        if (!$contract) {
            throw new \RuntimeException("Blockchain contract 'BurnsBonusPool' not found for chain_id: {$chainId}");
        }

        return $contract;
    }

    private function getSenderAddress(): ?string
    {
        $wallet = ContractSenderWallets::where('wallet_name', 'market_amount')->first();
        if (!$wallet) {
            $wallet = ContractSenderWallets::where('is_default', 1)->first();
        }

        return $wallet?->address;
    }

    private function buildContractData(Web3 $web3, BlockchainContract $contract, string $method, array $params): string
    {
        $abiContent = file_get_contents(base_path($contract->abi_path));
        if (!$abiContent) {
            throw new \RuntimeException("ABI file not found or empty: {$contract->abi_path}");
        }
        $abi = json_decode($abiContent, true);
        if (!is_array($abi)) {
            throw new \RuntimeException("Invalid ABI content: {$contract->abi_path}");
        }

        $instance = new Contract($web3->provider, $abi);
        $data = (string) $instance->getData($method, ...$params);
        if ($data === '') {
            throw new \RuntimeException("Failed to build call data for method: {$method}");
        }

        return '0x' . $data;
    }

    private function getRevertReason(
        Web3 $web3,
        string $to,
        string $data,
        ?string $blockNumber,
        ?string $from
    ): ?string {
        $reason = null;
        $errorMessage = null;

        $tx = [
            'to' => $to,
            'data' => $data,
        ];
        if ($from) {
            $tx['from'] = $from;
        }

        $web3->eth->call($tx, $blockNumber ?? 'latest', function ($err, $result) use (&$reason, &$errorMessage) {
            if ($err !== null) {
                $errorMessage = $err->getMessage();
                return;
            }
            $reason = $result;
        });

        if ($errorMessage) {
            return $this->extractRevertReasonFromMessage($errorMessage) ?? $errorMessage;
        }

        if (!is_string($reason) || $reason === '0x' || $reason === '') {
            return null;
        }

        return $this->decodeRevertReason($reason) ?? null;
    }

    private function extractRevertReasonFromMessage(string $message): ?string
    {
        if (preg_match('/revert(?:ed)?(?: with reason string)?[:\s]+["\']?([^"\']+)["\']?/i', $message, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function decodeRevertReason(string $data): ?string
    {
        $hex = preg_replace('/^0x/', '', $data);
        if (!str_starts_with($hex, '08c379a0')) {
            return null;
        }

        $offset = 8;
        if (strlen($hex) < $offset + 64 + 64) {
            return null;
        }

        $lenHex = substr($hex, $offset + 64, 64);
        $len = hexdec($lenHex);
        if ($len <= 0) {
            return null;
        }

        $strHex = substr($hex, $offset + 64 + 64, $len * 2);
        if ($strHex === '') {
            return null;
        }

        $decoded = hex2bin($strHex);
        return $decoded !== false ? $decoded : null;
    }
}
