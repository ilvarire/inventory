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
        Schema::table('material_requests', function (Blueprint $table) {
            $table->foreignId('fulfilled_by')->nullable()->after('approved_at')->constrained('users');
            $table->timestamp('fulfilled_at')->nullable()->after('fulfilled_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            $table->dropForeign(['fulfilled_by']);
            $table->dropColumn(['fulfilled_by', 'fulfilled_at']);
        });
    }
};
