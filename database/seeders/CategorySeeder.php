<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Seminar & Workshop',
                'description' => 'Acara edukatif seperti seminar, pelatihan, dan workshop.',
                'icon' => 'fa-chalkboard-teacher',
                'color' => '#1E88E5',
            ],
            [
                'name' => 'Konser & Musik',
                'description' => 'Event konser musik, pertunjukan band, dan festival musik.',
                'icon' => 'fa-music',
                'color' => '#E53935',
            ],
            [
                'name' => 'Olahraga',
                'description' => 'Pertandingan, turnamen, atau kompetisi olahraga.',
                'icon' => 'fa-futbol',
                'color' => '#43A047',
            ],
            [
                'name' => 'Webinar',
                'description' => 'Seminar online melalui platform digital.',
                'icon' => 'fa-video',
                'color' => '#8E24AA',
            ],
            [
                'name' => 'Kompetisi & Lomba',
                'description' => 'Berbagai jenis kompetisi dan perlombaan.',
                'icon' => 'fa-trophy',
                'color' => '#FB8C00',
            ],
            [
                'name' => 'Pameran',
                'description' => 'Acara pameran produk, jasa, atau karya seni.',
                'icon' => 'fa-image',
                'color' => '#3949AB',
            ],
        ];

        foreach ($categories as $index => $category) {
            DB::table('categories')->insert([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'color' => $category['color'],
                'is_active' => true,
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
