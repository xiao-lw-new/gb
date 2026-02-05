<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
echo "APP_ENV: " . env('APP_ENV') . "\n";
echo "DB_CONNECTION: " . env('DB_CONNECTION') . "\n";
echo "Config default: " . config('database.default') . "\n";
echo "Config pgsql host: " . config('database.connections.pgsql.host') . "\n";
