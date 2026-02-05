<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('token_price_up_chain', function (Blueprint $table) {
            // Store on-chain uint256 value (scaled by 10^decimals). numeric(78,0) covers uint256 (~1e77).
            $table->decimal('token_price_chain', 78, 0)->nullable()->after('token_price');
        });
    }

    public function down(): void
    {
        Schema::table('token_price_up_chain', function (Blueprint $table) {
            $table->dropColumn('token_price_chain');
        });
    }
};

