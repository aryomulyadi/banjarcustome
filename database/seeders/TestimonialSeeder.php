<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Abdul Haris Haris',
                'content' => 'Bajunya nyaman, bahannya bagus, sablonnya kuat, harganya standar terjangkau, kualitas terjamin, bahkan sudah jadi langganan. Luar biasa, makasih Banjar Custom.',
            ],
            [
                'name' => 'syamsuri uwie',
                'content' => 'Bajunya kualitas bagus, jahitan rapi dan pesanan selesai sesuai janji.',
            ],
            [
                'name' => 'marthadys 1103',
                'content' => 'Udah beberapa kali order dan hasilnya selalu nggak gagal, admin fast respon. Pokoknya best banget.',
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::firstOrCreate(
                ['name' => $testimonial['name'], 'content' => $testimonial['content']],
                [
                    'rating' => 5,
                    'city' => null,
                    'role' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
