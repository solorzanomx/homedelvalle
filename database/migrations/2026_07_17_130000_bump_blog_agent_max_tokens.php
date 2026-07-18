<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 4096 tokens truncaba el JSON de un mapa de 30 temas (la config en DB manda
 * sobre cualquier default del código, por eso se corrige con UPDATE aquí).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('ai_agent_configs')->where('key', 'blog.topics')
            ->where('max_tokens', '<', 16000)->update(['max_tokens' => 16000]);

        DB::table('ai_agent_configs')->where('key', 'blog.generation')
            ->where('max_tokens', '<', 16000)->update(['max_tokens' => 16000]);
    }

    public function down(): void
    {
        DB::table('ai_agent_configs')->where('key', 'blog.topics')->update(['max_tokens' => 4096]);
        DB::table('ai_agent_configs')->where('key', 'blog.generation')->update(['max_tokens' => 8192]);
    }
};
