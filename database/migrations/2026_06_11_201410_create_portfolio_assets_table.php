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
        Schema::create('portfolio_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cryptocurrency_id')->constrained('cryptocurrencies')->cascadeOnDelete();
            $table->decimal('balance', 24, 8)->default(0);
            $table->timestamps();

            // Un portafolio no puede tener dos filas de la misma cripto
            $table->unique(['portfolio_id', 'cryptocurrency_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_assets');
    }
};
