<?php

namespace App\Modules\Blockchain\Handlers;

/**
 * A no-op handler used to make sure new/unknown events can still be persisted
 * into blockchain_events by EventLogProcessor.
 */
class NoopEventHandler implements EventHandler
{
    public function handle(array $event): void
    {
        // Intentionally do nothing.
    }
}

