<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_processor_dispatch_logs', function (Blueprint $table) {
            $table->string('notify_transaction_hash', 100)->nullable()->after('transaction_hash');
        });
    }

    public function down(): void
    {
        Schema::table('tax_processor_dispatch_logs', function (Blueprint $table) {
            $table->dropColumn('notify_transaction_hash');
        });
    }
};
