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
        Schema::create('prepared_inventories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('production_log_id')->constrained();
            $table->foreignId('recipe_id')->nullable()->constrained('recipes');
            $table->foreignId('section_id')->constrained();

            $table->string('item_name');
            $table->integer('quantity');
            $table->string('unit', 50)->nullable();

            $table->decimal('selling_price', 10, 2)->default(0);

            $table->date('expiry_date')->nullable();

            $table->enum('status', ['available', 'sold', 'expired', 'wasted'])->default('available');

            $table->timestamps();

            $table->index('section_id');
            $table->index('production_log_id');
            $table->index('status');
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prepared_inventories');
    }
};
