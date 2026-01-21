<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to recreate the table with the updated CHECK constraint
        DB::statement("
            CREATE TABLE recipes_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                section_id INTEGER NOT NULL,
                created_by INTEGER NOT NULL,
                status TEXT CHECK(status IN ('active', 'archived', 'draft')) DEFAULT 'active',
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (section_id) REFERENCES sections(id),
                FOREIGN KEY (created_by) REFERENCES users(id)
            )
        ");

        // Copy data from old table
        DB::statement("
            INSERT INTO recipes_new (id, name, section_id, created_by, status, created_at, updated_at)
            SELECT id, name, section_id, created_by, status, created_at, updated_at
            FROM recipes
        ");

        // Drop old table
        DB::statement("DROP TABLE recipes");

        // Rename new table
        DB::statement("ALTER TABLE recipes_new RENAME TO recipes");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: remove 'draft' from allowed values
        DB::statement("
            CREATE TABLE recipes_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                section_id INTEGER NOT NULL,
                created_by INTEGER NOT NULL,
                status TEXT CHECK(status IN ('active', 'archived')) DEFAULT 'active',
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (section_id) REFERENCES sections(id),
                FOREIGN KEY (created_by) REFERENCES users(id)
            )
        ");

        DB::statement("
            INSERT INTO recipes_new (id, name, section_id, created_by, status, created_at, updated_at)
            SELECT id, name, section_id, created_by, status, created_at, updated_at
            FROM recipes
            WHERE status != 'draft'
        ");

        DB::statement("DROP TABLE recipes");
        DB::statement("ALTER TABLE recipes_new RENAME TO recipes");
    }
};
