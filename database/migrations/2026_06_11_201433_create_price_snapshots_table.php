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
        Schema::create('price_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cryptocurrency_id')->constrained('cryptocurrencies')->cascadeOnDelete();
            $table->decimal('price_usd', 24, 8);
            $table->decimal('price_gtq', 24, 8)->nullable();
            $table->decimal('market_cap', 24, 8)->nullable();
            $table->decimal('volume_24h', 24, 8)->nullable();
            $table->timestamp('captured_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_snapshots');
    }
};
