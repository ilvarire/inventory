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
        // Add used_in_production flag to material_requests
        Schema::table('material_requests', function (Blueprint $table) {
            $table->boolean('used_in_production')->default(false)->after('status');
        });

        // Add material_request_id FK to production_logs
        Schema::table('production_logs', function (Blueprint $table) {
            $table->foreignId('material_request_id')->nullable()->after('chef_id')
                ->constrained('material_requests')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_logs', function (Blueprint $table) {
            $table->dropForeign(['material_request_id']);
            $table->dropColumn('material_request_id');
        });

        Schema::table('material_requests', function (Blueprint $table) {
            $table->dropColumn('used_in_production');
        });
    }
};
