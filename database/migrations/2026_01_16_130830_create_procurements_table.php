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
        Schema::create('procurements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('procurement_user_id')->constrained('users');

            // Changed from foreign key to string in later migration
            $table->string('supplier_id')->nullable();

            $table->foreignId('section_id')->nullable()->constrained();

            $table->date('purchase_date');
            $table->enum('status', ['pending', 'received', 'approved', 'rejected'])->default('pending');

            // Approval fields
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurements');
    }
};
