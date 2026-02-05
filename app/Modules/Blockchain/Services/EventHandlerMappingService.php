<?php

namespace App\Modules\Blockchain\Services;

use App\Modules\Blockchain\Models\BlockchainContractEvent;
use Illuminate\Support\Facades\Cache;

class EventHandlerMappingService
{
    public function getHandlerByTopic(string $topic): ?string
    {
        return Cache::remember("event_handler_topic:$topic", 3600, function () use ($topic) {
            $event = BlockchainContractEvent::where('topic', $topic)->where('status', 1)->first();
            return $event ? $event->handler : null;
        });
    }

    public function getContractIdByTopic(string $topic): ?int
    {
        return Cache::remember("event_contract_id_topic:$topic", 3600, function () use ($topic) {
            $event = BlockchainContractEvent::where('topic', $topic)->where('status', 1)->first();
            return $event ? $event->contract_id : null;
        });
    }

    public function getContractIdByHandler(string $handler): ?int
    {
        return Cache::remember("event_contract_id_handler:$handler", 3600, function () use ($handler) {
            $event = BlockchainContractEvent::where('handler', $handler)->where('status', 1)->first();
            return $event ? $event->contract_id : null;
        });
    }
}

