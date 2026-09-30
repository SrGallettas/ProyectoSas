<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('business_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20)->default('staff');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['business_id', 'user_id']);
        });

        DB::table('businesses')->orderBy('id')->each(function (object $business): void {
            DB::table('business_user')->insert([
                'business_id' => $business->id, 'user_id' => $business->user_id,
                'role' => 'owner', 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_user');
    }
};
