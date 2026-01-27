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
        Schema::create('procurement_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('procurement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained();

            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('received_quantity', 10, 2)->default(0);

            $table->string('quality_note')->nullable();
            $table->date('expiry_date')->nullable();

            $table->timestamps();

            $table->index('raw_material_id');
            $table->index('procurement_id');
            $table->index('expiry_date');
            $table->index(['raw_material_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_items');
    }
};
