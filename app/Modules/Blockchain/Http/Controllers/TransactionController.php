<?php

namespace App\Modules\Blockchain\Http\Controllers;

use App\Modules\Api\Http\Controllers\BaseApiController;
use App\Modules\Blockchain\Models\BlockchainTransactionQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionController extends BaseApiController
{
    public function submit(Request $request): JsonResponse
    {
        $txHash = $request->input('txHash');

        if (empty($txHash) || !preg_match('/^0x[a-fA-F0-9]{64}$/', $txHash)) {
            return $this->error('无效的交易哈希', 400);
        }

        try {
            $existing = BlockchainTransactionQueue::where('transaction_hash', $txHash)->first();
            if ($existing) return $this->success(null, '交易哈希已在处理队列中');

            BlockchainTransactionQueue::create([
                // Public endpoint: no login required
                'user_id'          => null,
                'address'          => null,
                'transaction_hash' => $txHash,
                'status'           => BlockchainTransactionQueue::STATUS_PENDING,
                'message'          => '等待后台进程处理',
            ]);

            return $this->success(null, '提交成功，后台正在处理中');
        } catch (\Exception $e) {
            return $this->error('提交失败，请稍后重试', 500);
        }
    }
}

