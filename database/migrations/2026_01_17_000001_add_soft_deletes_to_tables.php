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
        // Add soft deletes to critical tables
        Schema::table('sales', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('procurements', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('production_logs', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('waste_logs', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('procurements', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('production_logs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('waste_logs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
