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
        Schema::create('waste_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('raw_material_id')->nullable()->constrained();
            $table->foreignId('production_log_id')->nullable()->constrained();

            $table->foreignId('section_id')->constrained();

            $table->decimal('quantity', 10, 2);
            $table->string('reason');

            $table->decimal('cost_amount', 12, 2);
            $table->string('status', 20)->default('pending');

            $table->foreignId('logged_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('section_id');
            $table->index('created_at');
            $table->index('approved_by');
            $table->index(['section_id', 'created_at']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_logs');
    }
};
