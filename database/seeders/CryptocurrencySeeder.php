<?php

namespace Database\Seeders;

use App\Models\Cryptocurrency;
use Illuminate\Database\Seeder;

class CryptocurrencySeeder extends Seeder
{
    public function run(): void
    {
        $cryptos = [
            ['name' => 'Bitcoin',  'symbol' => 'BTC',  'api_id' => 'bitcoin',  'is_active' => true],
            ['name' => 'Ethereum', 'symbol' => 'ETH',  'api_id' => 'ethereum', 'is_active' => true],
            ['name' => 'Tether',   'symbol' => 'USDT', 'api_id' => 'tether',   'is_active' => true],
            ['name' => 'Solana',   'symbol' => 'SOL',  'api_id' => 'solana',   'is_active' => true],
        ];

        foreach ($cryptos as $crypto) {
            // updateOrCreate evita duplicados si vuelves a correr el seeder
            Cryptocurrency::updateOrCreate(
                ['api_id' => $crypto['api_id']],
                $crypto
            );
        }
    }
}
