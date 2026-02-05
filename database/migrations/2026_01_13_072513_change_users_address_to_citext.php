<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Enable CITEXT extension if not exists
        DB::statement('CREATE EXTENSION IF NOT EXISTS citext');

        // 2. Change column type
        // Note: We use raw SQL because Laravel Schema builder doesn't support 'citext' natively in all versions/drivers perfectly via change()
        DB::statement('ALTER TABLE users ALTER COLUMN address TYPE citext');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to string (varchar)
        DB::statement('ALTER TABLE users ALTER COLUMN address TYPE varchar(255)');
    }
};
