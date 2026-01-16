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
        Schema::create('production_materials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('production_log_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained();
            $table->foreignId('procurement_item_id')->constrained();

            $table->decimal('quantity_used', 10, 3);
            $table->decimal('unit_cost', 10, 2); // locked cost

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_materials');
    }
};
