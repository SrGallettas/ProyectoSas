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
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('voided_by_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->string('void_reason')->nullable()->after('total');
            $table->timestamp('voided_at')->nullable()->after('sold_at');
            $table->index(['business_id', 'voided_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['business_id', 'voided_at']);
            $table->dropConstrainedForeignId('voided_by_user_id');
            $table->dropColumn(['void_reason', 'voided_at']);
        });
    }
};
