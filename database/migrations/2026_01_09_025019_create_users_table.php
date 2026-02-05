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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->string('address', 100)->nullable()->unique();
            $table->bigInteger('p_id')->default(0)->index();
            $table->text('path')->nullable();
            $table->string('remark')->nullable();
            $table->smallInteger('status')->default(0)->comment('0:预创建, 1:正常, 2:禁用');
            $table->smallInteger('active')->default(0)->comment('激活状态: 0未激活, 1已激活');
        });

        DB::statement('CREATE UNIQUE INDEX idx_users_path_unique_hash ON users (md5(path))');
        DB::statement('CREATE INDEX idx_users_path_gin_trgm ON users USING gin (path gin_trgm_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_users_path_unique_hash');
        DB::statement('DROP INDEX IF EXISTS idx_users_path_gin_trgm');
        Schema::dropIfExists('users');
    }
};
