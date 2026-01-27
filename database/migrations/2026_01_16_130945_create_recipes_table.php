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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();

            $table->decimal('expected_yield', 10, 2)->nullable();
            $table->string('yield_unit', 50)->nullable();
            $table->decimal('selling_price', 10, 2)->default(0);

            $table->text('instructions')->nullable();

            $table->foreignId('section_id')->constrained();
            $table->foreignId('created_by')->constrained('users');

            $table->enum('status', ['active', 'archived', 'draft'])->default('active');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
