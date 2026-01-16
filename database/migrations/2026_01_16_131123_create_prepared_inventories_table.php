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
            $table->foreignId('section_id')->constrained();

            $table->string('item_name');
            $table->integer('quantity');

            $table->date('expiry_date')->nullable();

            $table->enum('status', ['available', 'sold', 'expired', 'wasted'])->default('available');

            $table->timestamps();
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
