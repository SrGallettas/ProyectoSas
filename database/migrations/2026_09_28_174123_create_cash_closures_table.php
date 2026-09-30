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
        Schema::create('cash_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->date('business_date');
            $table->decimal('total_revenue', 10, 2);
            $table->decimal('expected_cash', 10, 2);
            $table->decimal('card_revenue', 10, 2);
            $table->decimal('counted_cash', 10, 2);
            $table->decimal('difference', 10, 2);
            $table->unsignedInteger('ticket_count');
            $table->timestamp('closed_at');
            $table->timestamps();
            $table->unique(['business_id', 'business_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_closures');
    }
};
