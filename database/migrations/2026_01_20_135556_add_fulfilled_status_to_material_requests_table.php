<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to use a raw statement to update the CHECK constraint
        // SQLite doesn't support ALTER COLUMN for ENUM, so we'll drop and recreate the constraint

        // Since SQLite stores ENUM as TEXT with a CHECK constraint, we can just update it
        // The easiest way is to allow any text value by removing the constraint
        // Or we can use a raw SQL to modify it

        // For SQLite compatibility, we'll use DB statement
        // Note: This is a workaround since SQLite doesn't support modifying ENUM
        DB::statement("
            CREATE TABLE material_requests_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                chef_id INTEGER NOT NULL,
                section_id INTEGER NOT NULL,
                status TEXT CHECK(status IN ('pending', 'approved', 'rejected', 'fulfilled')) DEFAULT 'pending',
                approved_by INTEGER,
                approved_at DATETIME,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (chef_id) REFERENCES users(id),
                FOREIGN KEY (section_id) REFERENCES sections(id),
                FOREIGN KEY (approved_by) REFERENCES users(id)
            )
        ");

        // Copy data from old table
        DB::statement("
            INSERT INTO material_requests_new (id, chef_id, section_id, status, approved_by, approved_at, created_at, updated_at)
            SELECT id, chef_id, section_id, status, approved_by, approved_at, created_at, updated_at
            FROM material_requests
        ");

        // Drop old table
        DB::statement("DROP TABLE material_requests");

        // Rename new table
        DB::statement("ALTER TABLE material_requests_new RENAME TO material_requests");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: remove 'fulfilled' from allowed values
        DB::statement("
            CREATE TABLE material_requests_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                chef_id INTEGER NOT NULL,
                section_id INTEGER NOT NULL,
                status TEXT CHECK(status IN ('pending', 'approved', 'rejected')) DEFAULT 'pending',
                approved_by INTEGER,
                approved_at DATETIME,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (chef_id) REFERENCES users(id),
                FOREIGN KEY (section_id) REFERENCES sections(id),
                FOREIGN KEY (approved_by) REFERENCES users(id)
            )
        ");

        DB::statement("
            INSERT INTO material_requests_new (id, chef_id, section_id, status, approved_by, approved_at, created_at, updated_at)
            SELECT id, chef_id, section_id, status, approved_by, approved_at, created_at, updated_at
            FROM material_requests
            WHERE status != 'fulfilled'
        ");

        DB::statement("DROP TABLE material_requests");
        DB::statement("ALTER TABLE material_requests_new RENAME TO material_requests");
    }
};
