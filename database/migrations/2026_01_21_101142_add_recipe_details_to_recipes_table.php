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
        Schema::table('recipes', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->decimal('expected_yield', 10, 2)->nullable()->after('description');
            $table->string('yield_unit', 50)->nullable()->after('expected_yield');
            $table->text('instructions')->nullable()->after('yield_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn(['description', 'expected_yield', 'yield_unit', 'instructions']);
        });
    }
};
