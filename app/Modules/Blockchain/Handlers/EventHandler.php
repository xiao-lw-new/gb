<?php

namespace App\Modules\Blockchain\Handlers;

interface EventHandler
{
    public function handle(array $event): void;
}
