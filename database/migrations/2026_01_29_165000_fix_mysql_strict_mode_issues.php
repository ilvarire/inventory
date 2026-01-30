<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix notification_logs
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->string('recipient_email')->nullable()->change();
        });

        // Fix production_materials
        Schema::table('production_materials', function (Blueprint $table) {
            $table->foreignId('procurement_item_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert production_materials
        Schema::table('production_materials', function (Blueprint $table) {
            // Note: This might fail if there are null values, so we can't strictly revert to nullable(false) easily without data cleanup.
            // But we will try to revert the definition.
            $table->foreignId('procurement_item_id')->nullable(false)->change();
        });

        // Revert notification_logs
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->string('recipient_email')->nullable(false)->change();
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
