<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->foreignId('closed_by_user_id')->nullable()->after('opened_by_user_id')->constrained('users')->nullOnDelete();
            $table->decimal('expected_cash', 10, 2)->nullable()->after('opening_cash');
            $table->decimal('closing_cash', 10, 2)->nullable()->after('expected_cash');
            $table->decimal('difference', 10, 2)->nullable()->after('closing_cash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('closed_by_user_id');
            $table->dropColumn(['expected_cash', 'closing_cash', 'difference']);
        });
    }
};
