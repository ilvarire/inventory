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
        Schema::table('procurements', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['supplier_id']);

            // Change the column to string (nullable)
            $table->string('supplier_id', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            // Change back to unsignedBigInteger
            $table->unsignedBigInteger('supplier_id')->nullable()->change();

            // Re-add the foreign key constraint
            $table->foreign('supplier_id')->references('id')->on('suppliers');
        });
    }
};
