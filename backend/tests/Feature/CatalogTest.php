<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Equipment;
use App\Models\RentalPoint;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_one_renter_and_one_owner_with_two_stocked_rental_points(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(2, User::query()->count());
        $this->assertSame('renter', User::query()->where('email', DemoAccountsSeeder::RENTER_EMAIL)->value('role'));

        $owner = User::query()->where('email', DemoAccountsSeeder::OWNER_EMAIL)->firstOrFail();
        $this->assertTrue($owner->isOwner());
        $this->assertTrue(Hash::check(DemoAccountsSeeder::PASSWORD, $owner->password));

        $points = $owner->rentalPoints()->withCount('equipment')->get();
        $this->assertCount(2, $points);
        $points->each(fn (RentalPoint $point) => $this->assertGreaterThan(0, $point->equipment_count));
        $this->assertSame(0, Equipment::query()->whereNull('rental_point_id')->count());

        // Seeded images are local files, served from backend/public.
        Equipment::query()->pluck('image')->each(
            fn (string $image) => $this->assertFileExists(public_path(ltrim($image, '/'))),
        );
    }

    public function test_seeding_twice_does_not_duplicate_data(): void
    {
        $this->seed(DatabaseSeeder::class);
        $equipmentCount = Equipment::query()->count();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(2, User::query()->count());
        $this->assertSame(2, RentalPoint::query()->count());
        $this->assertSame($equipmentCount, Equipment::query()->count());
    }

    public function test_catalog_returns_absolute_urls_for_seeded_images(): void
    {
        $this->seed(DatabaseSeeder::class);

        $image = $this->getJson('/v1/equipment')->assertOk()->json('0.image');

        $this->assertStringStartsWith(url('/images/equipment/'), $image);
    }

    public function test_public_rental_points_list_only_points_with_equipment(): void
    {
        $owner = $this->owner();
        $stocked = $owner->rentalPoints()->create(['name' => 'Stocked', 'opening_hours' => '08:00–18:00']);
        $owner->rentalPoints()->create(['name' => 'Empty']);
        $this->equipment($stocked, 'ski-1');

        $this->getJson('/v1/rental-points')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'Stocked')
            ->assertJsonPath('0.openingHours', '08:00–18:00')
            ->assertJsonPath('0.equipmentCount', 1);
    }

    public function test_catalog_can_be_filtered_by_rental_point(): void
    {
        $owner = $this->owner();
        $first = $owner->rentalPoints()->create(['name' => 'First']);
        $second = $owner->rentalPoints()->create(['name' => 'Second']);
        $this->equipment($first, 'first-ski');
        $this->equipment($second, 'second-ski');

        $this->getJson("/v1/equipment?rentalPointId={$second->id}")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', 'second-ski');
    }

    public function test_catalog_shows_which_sizes_are_free_for_the_selected_dates(): void
    {
        $owner = $this->owner();
        $renter = User::query()->create(['name' => 'R', 'email' => 'r@example.com', 'password' => 'x']);
        $point = $owner->rentalPoints()->create(['name' => 'Shop']);
        $this->equipment($point, 'test-ski', ['sizes' => ['150', '160']]);

        Booking::query()->create([
            'user_id' => $renter->id,
            'equipment_id' => 'test-ski',
            'size' => '150',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-04',
        ]);

        // Overlaps the booking on its last day: size 150 is taken.
        $this->getJson('/v1/equipment?startDate=2026-12-04&endDate=2026-12-06')
            ->assertOk()
            ->assertJsonPath('0.availableSizes', ['160']);

        // After the booking ends: both sizes are free.
        $this->getJson('/v1/equipment?startDate=2026-12-05&endDate=2026-12-06')
            ->assertOk()
            ->assertJsonPath('0.availableSizes', ['150', '160']);

        // Without dates the catalog doesn't compute availability.
        $this->assertArrayNotHasKey('availableSizes', $this->getJson('/v1/equipment')->json('0'));
    }

    public function test_items_out_of_stock_for_the_period_have_no_free_sizes(): void
    {
        $point = $this->owner()->rentalPoints()->create(['name' => 'Shop']);
        $this->equipment($point, 'seasonal-ski', [
            'available_now' => false,
            'available_dates' => ['2026-12-10', '2026-12-11'],
        ]);

        $this->getJson('/v1/equipment?startDate=2026-12-10&endDate=2026-12-11')
            ->assertJsonPath('0.availableSizes', ['160']);

        $this->getJson('/v1/equipment?startDate=2026-12-10&endDate=2026-12-12')
            ->assertJsonPath('0.availableSizes', []);
    }

    private function owner(): User
    {
        return User::query()->create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_OWNER,
        ]);
    }

    private function equipment(RentalPoint $point, string $id, array $overrides = []): Equipment
    {
        return Equipment::query()->create(array_merge([
            'id' => $id,
            'rental_point_id' => $point->id,
            'name' => 'Test Ski',
            'category' => 'ski',
            'category_label' => 'Ski Set',
            'image' => 'https://example.com/ski.jpg',
            'specs' => [],
            'daily_rate' => 35,
            'deposit' => 100,
            'sizes' => ['160'],
            'available_now' => true,
            'available_dates' => [],
        ], $overrides));
    }
}
