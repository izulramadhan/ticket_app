<?php

namespace Database\Seeders;

use App\Models\Tiket;
use App\Models\Event;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Deterministic tickets for first 3 events to support OrderSeeder dependencies
        $tickets = [
            [
                'event_id' => 1,
                'tipe' => 'premium',
                'harga' => 1500000,
                'stok' => 100,
            ],
            [
                'event_id' => 1,
                'tipe' => 'reguler',
                'harga' => 500000,
                'stok' => 500,
            ],
            [
                'event_id' => 2,
                'tipe' => 'premium',
                'harga' => 200000,
                'stok' => 300,
            ],
            [
                'event_id' => 3,
                'tipe' => 'premium',
                'harga' => 300000,
                'stok' => 200,
            ],
        ];

        foreach ($tickets as $ticket) {
            Tiket::create($ticket);
        }

        // Dynamically seed tickets for remaining events (event_id 4 to 10)
        $events = Event::whereKeyNot([1, 2, 3])->get();
        foreach ($events as $event) {
            // Reguler ticket
            Tiket::create([
                'event_id' => $event->id,
                'tipe' => 'reguler',
                'harga' => rand(5, 15) * 50000, // Rp 250k - Rp 750k
                'stok' => rand(100, 500),
            ]);

            // Premium ticket
            Tiket::create([
                'event_id' => $event->id,
                'tipe' => 'premium',
                'harga' => rand(16, 40) * 50000, // Rp 800k - Rp 2m
                'stok' => rand(20, 100),
            ]);
        }
    }
}
