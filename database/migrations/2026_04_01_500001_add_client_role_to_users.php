<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite stores enum as CHECK constraint. We need to drop and recreate it.
        // First, find and remove the existing CHECK constraint by rebuilding the column.
        // In SQLite, the simplest approach is to drop the constraint directly.
        DB::statement("
            CREATE TABLE IF NOT EXISTS users_backup AS SELECT * FROM users
        ");

        // Get the current CHECK constraint and update it
        // SQLite doesn't support ALTER CHECK, so we remove the check via pragma
        // The pragmatic approach: just drop the check constraint
        DB::statement("DROP TABLE IF EXISTS users_new");

        // Get create statement
        $createSql = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name='users'")->sql;

        // Replace the old enum check with the new one that includes 'client'
        $newSql = str_replace(
            "in ('user', 'broker', 'admin', 'editor', 'viewer')",
            "in ('user', 'broker', 'admin', 'editor', 'viewer', 'client')",
            $createSql
        );

        // Also handle the case where it might use double quotes
        $newSql = str_replace(
            'in ("user", "broker", "admin", "editor", "viewer")',
            'in ("user", "broker", "admin", "editor", "viewer", "client")',
            $newSql
        );

        // Rename tables
        $newSql = str_replace('CREATE TABLE "users"', 'CREATE TABLE "users_new"', $newSql);
        // Also handle without quotes
        $newSql = str_replace('CREATE TABLE users', 'CREATE TABLE users_new', $newSql);

        DB::statement($newSql);
        DB::statement("INSERT INTO users_new SELECT * FROM users");
        DB::statement("DROP TABLE users");
        DB::statement("ALTER TABLE users_new RENAME TO users");

        DB::statement("DROP TABLE IF EXISTS users_backup");
    }

    public function down(): void
    {
        // Reverse: remove 'client' from the enum
        // Not critical for rollback
    }
};
