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
        // Inventory movements indexes
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->index('raw_material_id');
            $table->index('movement_type');
            $table->index('created_at');
            $table->index(['raw_material_id', 'movement_type']);
        });

        // Sales indexes
        Schema::table('sales', function (Blueprint $table) {
            $table->index('section_id');
            $table->index('sale_date');
            $table->index(['section_id', 'sale_date']);
        });

        // Sale items indexes
        Schema::table('sale_items', function (Blueprint $table) {
            $table->index('sale_id');
            $table->index('source_id');
        });

        // Production logs indexes
        Schema::table('production_logs', function (Blueprint $table) {
            $table->index('section_id');
            $table->index('production_date');
            $table->index('chef_id');
            $table->index(['section_id', 'production_date']);
        });

        // Expenses indexes
        Schema::table('expenses', function (Blueprint $table) {
            $table->index('section_id');
            $table->index('expense_date');
            $table->index(['section_id', 'expense_date']);
        });

        // Waste logs indexes
        Schema::table('waste_logs', function (Blueprint $table) {
            $table->index('section_id');
            $table->index('created_at');
            $table->index('approved_by');
            $table->index(['section_id', 'created_at']);
        });

        // Material requests indexes
        Schema::table('material_requests', function (Blueprint $table) {
            $table->index('chef_id');
            $table->index('section_id');
            $table->index('status');
            $table->index('approved_by');
        });

        // Procurement items indexes (for FIFO queries)
        Schema::table('procurement_items', function (Blueprint $table) {
            $table->index('raw_material_id');
            $table->index('procurement_id');
            $table->index('expiry_date');
            $table->index(['raw_material_id', 'created_at']);
        });

        // Prepared inventories indexes
        Schema::table('prepared_inventories', function (Blueprint $table) {
            $table->index('section_id');
            $table->index('production_log_id');
            $table->index('status');
            $table->index('expiry_date');
        });

        // Audit logs indexes
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('model_type');
            $table->index('model_id');
            $table->index('created_at');
            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropIndex(['raw_material_id']);
            $table->dropIndex(['movement_type']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['raw_material_id', 'movement_type']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['section_id']);
            $table->dropIndex(['sale_date']);
            $table->dropIndex(['section_id', 'sale_date']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex(['sale_id']);
            $table->dropIndex(['source_id']);
        });

        Schema::table('production_logs', function (Blueprint $table) {
            $table->dropIndex(['section_id']);
            $table->dropIndex(['production_date']);
            $table->dropIndex(['chef_id']);
            $table->dropIndex(['section_id', 'production_date']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['section_id']);
            $table->dropIndex(['expense_date']);
            $table->dropIndex(['section_id', 'expense_date']);
        });

        Schema::table('waste_logs', function (Blueprint $table) {
            $table->dropIndex(['section_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['approved_by']);
            $table->dropIndex(['section_id', 'created_at']);
        });

        Schema::table('material_requests', function (Blueprint $table) {
            $table->dropIndex(['chef_id']);
            $table->dropIndex(['section_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['approved_by']);
        });

        Schema::table('procurement_items', function (Blueprint $table) {
            $table->dropIndex(['raw_material_id']);
            $table->dropIndex(['procurement_id']);
            $table->dropIndex(['expiry_date']);
            $table->dropIndex(['raw_material_id', 'created_at']);
        });

        Schema::table('prepared_inventories', function (Blueprint $table) {
            $table->dropIndex(['section_id']);
            $table->dropIndex(['production_log_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['expiry_date']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['model_type']);
            $table->dropIndex(['model_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['model_type', 'model_id']);
        });
    }
};
