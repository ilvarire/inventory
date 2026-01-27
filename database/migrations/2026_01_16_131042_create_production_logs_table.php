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
        Schema::create('production_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recipe_id')->constrained();
            $table->foreignId('section_id')->constrained();
            $table->foreignId('chef_id')->constrained('users');

            $table->integer('quantity_produced');
            $table->decimal('variance', 10, 2)->nullable();
            $table->text('notes')->nullable();

            $table->date('production_date');

            $table->softDeletes();
            $table->timestamps();

            $table->index('section_id');
            $table->index('production_date');
            $table->index('chef_id');
            $table->index(['section_id', 'production_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_logs');
    }
};
