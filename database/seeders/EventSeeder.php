<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            [
                'user_id' => 1,
                'judul' => 'Konser Musik Rock',
                'deskripsi' => 'Nikmati malam penuh energi dengan band rock terkenal.',
                'tanggal_waktu' => '2026-08-15 19:00:00',
                'lokasi' => 'Stadion Utama',
                'kategori_id' => 1,
                'gambar' => 'konser.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'Pameran Seni Kontemporer',
                'deskripsi' => 'Jelajahi karya seni modern dari seniman lokal dan internasional.',
                'tanggal_waktu' => '2026-09-10 10:00:00',
                'lokasi' => 'Galeri Seni Kota',
                'kategori_id' => 2,
                'gambar' => 'konser.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'Festival Makanan Internasional',
                'deskripsi' => 'Cicipi berbagai hidangan lezat dari seluruh dunia.',
                'tanggal_waktu' => '2026-10-05 12:00:00',
                'lokasi' => 'Taman Kota',
                'kategori_id' => 3,
                'gambar' => 'konser.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'Tech Startups Conference',
                'deskripsi' => 'Konferensi startup teknologi terbesar se-Asia Tenggara.',
                'tanggal_waktu' => '2026-07-20 09:00:00',
                'lokasi' => 'Convention Hall',
                'kategori_id' => 2,
                'gambar' => 'konser.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'Jazz Night Live',
                'deskripsi' => 'Malam syahdu penuh irama Jazz bersama musisi ternama.',
                'tanggal_waktu' => '2026-07-18 20:00:00',
                'lokasi' => 'Cafe Terapung',
                'kategori_id' => 1,
                'gambar' => 'konser.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'Laravel Advanced Workshop',
                'deskripsi' => 'Workshop mendalam tentang optimasi performa Laravel.',
                'tanggal_waktu' => '2026-07-15 21:30:00', // Ongoing (dimulai ~1.5 jam lalu)
                'lokasi' => 'Lab Komputer Universitas',
                'kategori_id' => 3,
                'gambar' => 'konser.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'Digital Marketing Seminar',
                'deskripsi' => 'Strategi pemasaran digital terbaru di tahun 2026.',
                'tanggal_waktu' => '2026-07-15 21:00:00', // Ongoing (dimulai ~2 jam lalu)
                'lokasi' => 'Aula Balai Kota',
                'kategori_id' => 2,
                'gambar' => 'konser.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'Photography Masterclass',
                'deskripsi' => 'Pelajari seni fotografi komersial langsung dari ahlinya.',
                'tanggal_waktu' => '2026-07-14 09:00:00', // Completed
                'lokasi' => 'Studio Foto Cahaya',
                'kategori_id' => 3,
                'gambar' => 'konser.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'UI/UX Bootcamp',
                'deskripsi' => 'Kelas intensif UI/UX untuk pemula hingga profesional.',
                'tanggal_waktu' => '2026-07-08 09:00:00', // Completed
                'lokasi' => 'Co-working Space Malang',
                'kategori_id' => 3,
                'gambar' => 'konser.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'Startup Pitch Arena',
                'deskripsi' => 'Saksikan ide-ide startup luar biasa bersaing di panggung besar.',
                'tanggal_waktu' => '2026-06-25 14:00:00', // Completed
                'lokasi' => 'Gedung Inkubator Bisnis',
                'kategori_id' => 2,
                'gambar' => 'konser.jpg',
            ],
        ];

        foreach ($events as $event) {
            Event::create($event);
        }
    }
}
