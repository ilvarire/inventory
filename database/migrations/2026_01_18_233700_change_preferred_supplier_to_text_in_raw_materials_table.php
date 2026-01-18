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
        Schema::table('raw_materials', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['preferred_supplier_id']);

            // Change the column to string (nullable)
            $table->string('preferred_supplier_id', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raw_materials', function (Blueprint $table) {
            // Change back to unsignedBigInteger
            $table->unsignedBigInteger('preferred_supplier_id')->nullable()->change();

            // Re-add the foreign key constraint
            $table->foreign('preferred_supplier_id')->references('id')->on('suppliers');
        });
    }
};
