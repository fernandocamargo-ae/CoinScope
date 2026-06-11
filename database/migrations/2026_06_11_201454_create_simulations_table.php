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
        Schema::create('simulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Nullable: una compra solo usa target, una venta solo source, un intercambio usa ambos
            $table->foreignId('source_crypto_id')->nullable()->constrained('cryptocurrencies')->nullOnDelete();
            $table->foreignId('target_crypto_id')->nullable()->constrained('cryptocurrencies')->nullOnDelete();

            $table->enum('type', ['BUY', 'SELL', 'EXCHANGE']);
            $table->decimal('source_amount', 24, 8)->nullable();
            $table->decimal('target_amount', 24, 8)->nullable();
            $table->decimal('source_price_usd', 24, 8)->nullable();
            $table->decimal('target_price_usd', 24, 8)->nullable();
            $table->decimal('usd_equivalent', 24, 8)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulations');
    }
};
