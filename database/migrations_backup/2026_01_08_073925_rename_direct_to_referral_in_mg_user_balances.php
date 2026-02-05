<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mg_user_balances', function (Blueprint $table) {
            $table->renameColumn('direct', 'referral');
            $table->renameColumn('claim_direct', 'claim_referral');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mg_user_balances', function (Blueprint $table) {
            $table->renameColumn('referral', 'direct');
            $table->renameColumn('claim_referral', 'claim_direct');
        });
    }
};
