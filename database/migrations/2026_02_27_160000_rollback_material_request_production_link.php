<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Rollback: remove material_request_id from production_logs
     * and used_in_production from material_requests.
     */
    public function up(): void
    {
        Schema::table('production_logs', function (Blueprint $table) {
            $table->dropForeign(['material_request_id']);
            $table->dropColumn('material_request_id');
        });

        Schema::table('material_requests', function (Blueprint $table) {
            $table->dropColumn('used_in_production');
        });
    }

    /**
     * Reverse the rollback (re-add columns).
     */
    public function down(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            $table->boolean('used_in_production')->default(false)->after('status');
        });

        Schema::table('production_logs', function (Blueprint $table) {
            $table->foreignId('material_request_id')->nullable()->after('chef_id')
                ->constrained('material_requests')->nullOnDelete();
        });
    }
};
