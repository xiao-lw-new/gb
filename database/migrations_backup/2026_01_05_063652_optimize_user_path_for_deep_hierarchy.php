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
        // 1. 启用 pg_trgm 扩展
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        Schema::table('users', function (Blueprint $table) {
            // 2. 将 path 修改为 text，以支持超长路径
            $table->text('path')->nullable()->change();
            
            // 3. 删除旧索引
            $table->dropUnique(['path']);
        });

        // 4. 创建基于 MD5 的函数唯一索引（解决 B-tree 长度限制）
        DB::statement('CREATE UNIQUE INDEX idx_users_path_unique_hash ON users (md5(path))');

        // 5. 创建 GIN 索引以支持高效的 LIKE 查询
        DB::statement('CREATE INDEX idx_users_path_gin_trgm ON users USING gin (path gin_trgm_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_users_path_gin_trgm');
        DB::statement('DROP INDEX IF EXISTS idx_users_path_unique_hash');
        
        Schema::table('users', function (Blueprint $table) {
            $table->string('path', 255)->nullable()->change();
            $table->unique('path');
        });
    }
};
