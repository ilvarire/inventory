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
        Schema::table('prepared_inventories', function (Blueprint $table) {
            $table->foreignId('recipe_id')->nullable()->after('production_log_id')->constrained('recipes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prepared_inventories', function (Blueprint $table) {
            $table->dropForeign(['recipe_id']);
            $table->dropColumn('recipe_id');
        });
    }
};
