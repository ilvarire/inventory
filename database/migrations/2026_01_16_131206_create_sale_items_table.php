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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();

            $table->string('item_name');
            $table->integer('quantity');

            $table->decimal('unit_price', 10, 2);
            $table->decimal('cost_price', 10, 2);

            $table->enum('source_type', ['fresh', 'prepared']);
            $table->unsignedBigInteger('source_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
