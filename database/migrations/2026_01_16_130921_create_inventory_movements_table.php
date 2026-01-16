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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('raw_material_id')->constrained();
            $table->foreignId('procurement_item_id')->nullable()->constrained();

            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();

            $table->decimal('quantity', 10, 2);

            $table->enum('movement_type', [
                'procurement',
                'issue_to_chef',
                'return_to_store',
                'prepared_to_manager',
                'sale',
                'waste',
                'adjustment'
            ]);

            $table->unsignedBigInteger('reference_id')->nullable();

            $table->foreignId('performed_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
