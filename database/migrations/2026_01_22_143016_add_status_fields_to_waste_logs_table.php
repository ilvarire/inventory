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
        Schema::table('waste_logs', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('cost_amount');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waste_logs', function (Blueprint $table) {
            $table->dropColumn(['status', 'approved_at', 'rejected_at', 'rejection_reason']);
        });
    }
};
