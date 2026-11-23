<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\RentalPoint;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Two rental points of the demo owner, each with its own equipment.
 * Images live in backend/public/images/equipment (photos from Unsplash, Unsplash License).
 */
class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->where('email', DemoAccountsSeeder::OWNER_EMAIL)->firstOrFail();

        foreach ($this->catalog() as $pointData) {
            $items = $pointData['equipment'];
            unset($pointData['equipment']);

            $point = RentalPoint::query()->updateOrCreate(
                ['user_id' => $owner->id, 'name' => $pointData['name']],
                $pointData,
            );

            foreach ($items as $item) {
                Equipment::query()->updateOrCreate(
                    ['id' => $item['id']],
                    [
                        'category_label' => 'Equipment',
                        'badge_tone' => 'new',
                        'available_now' => true,
                        'available_dates' => [],
                        ...$item,
                        'rental_point_id' => $point->id,
                    ],
                );
            }
        }
    }

    private function catalog(): array
    {
        return [
            [
                'name' => 'Innsbruck City Store',
                'address' => 'Maria-Theresien-Straße 18, 6020 Innsbruck',
                'opening_hours' => '08:00–19:00',
                'equipment' => [
                    [
                        'id' => 'atomic-redster-s9',
                        'name' => 'Atomic Redster S9 Revoshock',
                        'category' => 'ski',
                        'category_label' => 'Performance Ski Set',
                        'badge' => 'Model 2026',
                        'badge_tone' => 'new',
                        'image' => '/images/equipment/ski-carving.jpg',
                        'specs' => ['Radius 13.5 m', 'Race camber', 'Poles included'],
                        'daily_rate' => 49,
                        'half_day_rate' => 32,
                        'deposit' => 150,
                        'sizes' => ['150', '157', '165', '172'],
                    ],
                    [
                        'id' => 'rossignol-experience-86',
                        'name' => 'Rossignol Experience 86 Basalt',
                        'category' => 'ski',
                        'category_label' => 'All-Mountain Ski Set',
                        'badge' => 'Freshly Waxed',
                        'badge_tone' => 'waxed',
                        'image' => '/images/equipment/ski-allmountain.jpg',
                        'specs' => ['Radius 16 m', 'Air Tip rocker', 'ISO 11088 bindings'],
                        'daily_rate' => 39,
                        'half_day_rate' => 25,
                        'deposit' => 80,
                        'sizes' => ['146', '154', '162', '170', '178'],
                    ],
                    [
                        'id' => 'lange-shadow-120',
                        'name' => 'Lange Shadow 120 LV',
                        'category' => 'boots',
                        'category_label' => 'Alpine Ski Boots',
                        'badge' => 'Heat-moldable',
                        'badge_tone' => 'info',
                        'image' => '/images/equipment/ski-boots.jpg',
                        'specs' => ['Flex 120', 'Last 97 mm', 'GripWalk soles'],
                        'daily_rate' => 22,
                        'half_day_rate' => 14,
                        'deposit' => 50,
                        'sizes' => ['24.5', '25.5', '26.5', '27.5', '28.5'],
                    ],
                    [
                        'id' => 'atomic-redster-junior',
                        'name' => 'Atomic Redster J Kids Set',
                        'category' => 'kids',
                        'category_label' => 'Kids Ski Set',
                        'badge' => 'Kids 4–12',
                        'badge_tone' => 'kids',
                        'image' => '/images/equipment/kids-ski-school.jpg',
                        'specs' => ['Soft-flex bindings', 'Helmet included', 'Ski school ready'],
                        'daily_rate' => 24,
                        'half_day_rate' => 15,
                        'deposit' => 40,
                        'sizes' => ['90', '100', '110', '120', '130'],
                    ],
                ],
            ],
            [
                'name' => 'St. Anton Base Station',
                'address' => 'Dorfstraße 3, 6580 St. Anton am Arlberg',
                'opening_hours' => '07:30–18:00',
                'equipment' => [
                    [
                        'id' => 'head-kore-93',
                        'name' => 'HEAD Kore 93',
                        'category' => 'ski',
                        'category_label' => 'Freeride Ski Set',
                        'badge' => 'Freshly Waxed',
                        'badge_tone' => 'waxed',
                        'image' => '/images/equipment/ski-set-slope.jpg',
                        'specs' => ['Radius 17.8 m', 'Tip & tail rocker', 'Carbon sandwich'],
                        'daily_rate' => 45,
                        'half_day_rate' => 29,
                        'deposit' => 120,
                        'sizes' => ['156', '163', '170', '177', '184'],
                    ],
                    [
                        'id' => 'burton-custom-camber',
                        'name' => 'Burton Custom Camber',
                        'category' => 'board',
                        'category_label' => 'All-Mountain Snowboard',
                        'badge' => 'Model 2026',
                        'badge_tone' => 'new',
                        'image' => '/images/equipment/snowboard-carry.jpg',
                        'specs' => ['Camber pop', 'Directional twin', 'Bindings included'],
                        'daily_rate' => 42,
                        'half_day_rate' => 27,
                        'deposit' => 100,
                        'sizes' => ['148', '152', '156', '158W', '162'],
                    ],
                    [
                        'id' => 'jones-mind-expander',
                        'name' => 'Jones Mind Expander',
                        'category' => 'board',
                        'category_label' => 'Powder Snowboard',
                        'badge' => 'Freshly Waxed',
                        'badge_tone' => 'waxed',
                        'image' => '/images/equipment/snowboard-ride.jpg',
                        'specs' => ['Spoon nose', '3D contour base', 'Swallow tail'],
                        'daily_rate' => 48,
                        'half_day_rate' => null,
                        'deposit' => 130,
                        'sizes' => ['146', '150', '154', '158'],
                    ],
                    [
                        'id' => 'poc-obex-mips',
                        'name' => 'POC Obex MIPS Helmet + Goggles',
                        'category' => 'boots',
                        'category_label' => 'Helmet & Goggles',
                        'badge' => 'MIPS',
                        'badge_tone' => 'info',
                        'image' => '/images/equipment/helmet-goggles.jpg',
                        'specs' => ['MIPS liner', 'RECCO reflector', 'Category 3 lens'],
                        'daily_rate' => 12,
                        'half_day_rate' => 8,
                        'deposit' => 40,
                        'sizes' => ['XS/S', 'M/L', 'XL'],
                    ],
                    [
                        'id' => 'elan-jett-kids',
                        'name' => 'Elan Jett Kids Set',
                        'category' => 'kids',
                        'category_label' => 'Kids Ski Set',
                        'badge' => 'Kids 3–10',
                        'badge_tone' => 'kids',
                        'image' => '/images/equipment/kids-ski-lesson.jpg',
                        'specs' => ['Easy-turn sidecut', 'Helmet included', 'Parent-friendly sizing'],
                        'daily_rate' => 22,
                        'half_day_rate' => 14,
                        'deposit' => 40,
                        'sizes' => ['80', '90', '100', '110', '120'],
                    ],
                ],
            ],
        ];
    }
}
