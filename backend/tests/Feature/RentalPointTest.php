<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\RentalPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RentalPointTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_manage_rental_points(): void
    {
        $this->getJson('/v1/my/rental-points')->assertUnauthorized();
        $this->postJson('/v1/my/rental-points', ['name' => 'Shop'])->assertUnauthorized();
    }

    public function test_renter_cannot_use_the_owner_area(): void
    {
        [, $token] = $this->user('renter@example.com', User::ROLE_RENTER);

        $this->withToken($token)->getJson('/v1/my/rental-points')->assertForbidden();
        $this->withToken($token)->postJson('/v1/my/rental-points', ['name' => 'Shop'])->assertForbidden();
        $this->withToken($token)->getJson('/v1/my/equipment')->assertForbidden();
        $this->withToken($token)->getJson('/v1/my/bookings')->assertForbidden();
        $this->withToken($token)->postJson('/v1/equipment', ['name' => 'Ski'])->assertForbidden();

        $this->assertDatabaseCount('rental_points', 0);
    }

    public function test_user_can_create_and_list_their_rental_points(): void
    {
        [, $token] = $this->user();

        $this->withToken($token)->postJson('/v1/my/rental-points', [
            'name' => 'Sölden Glacier Hub',
            'address' => 'Valley station, Sölden',
            'openingHours' => '08:00–18:30',
        ])->assertCreated()
            ->assertJsonPath('name', 'Sölden Glacier Hub')
            ->assertJsonPath('address', 'Valley station, Sölden')
            ->assertJsonPath('openingHours', '08:00–18:30')
            ->assertJsonPath('equipmentCount', 0);

        $this->withToken($token)->getJson('/v1/my/rental-points')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'Sölden Glacier Hub');
    }

    public function test_rental_point_requires_a_name(): void
    {
        [, $token] = $this->user();

        $this->withToken($token)->postJson('/v1/my/rental-points', ['address' => 'Somewhere'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_user_does_not_see_or_change_other_users_rental_points(): void
    {
        [, $token] = $this->user('alice@example.com');
        [$bob] = $this->user('bob@example.com');
        $bobPoint = $bob->rentalPoints()->create(['name' => 'Bob Shop']);

        $this->withToken($token)->getJson('/v1/my/rental-points')
            ->assertOk()
            ->assertJsonCount(0);

        $this->withToken($token)->putJson("/v1/my/rental-points/{$bobPoint->id}", ['name' => 'Mine now'])
            ->assertNotFound();

        $this->withToken($token)->deleteJson("/v1/my/rental-points/{$bobPoint->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('rental_points', ['id' => $bobPoint->id, 'name' => 'Bob Shop']);
    }

    public function test_user_can_update_and_delete_an_empty_rental_point(): void
    {
        [$user, $token] = $this->user();
        $point = $user->rentalPoints()->create(['name' => 'Old name']);

        $this->withToken($token)->putJson("/v1/my/rental-points/{$point->id}", ['name' => 'New name'])
            ->assertOk()
            ->assertJsonPath('name', 'New name');

        $this->withToken($token)->deleteJson("/v1/my/rental-points/{$point->id}")
            ->assertOk();

        $this->assertDatabaseMissing('rental_points', ['id' => $point->id]);
    }

    public function test_rental_point_with_equipment_cannot_be_deleted(): void
    {
        [$user, $token] = $this->user();
        $point = $user->rentalPoints()->create(['name' => 'Busy shop']);
        $this->createEquipment($point);

        $this->withToken($token)->deleteJson("/v1/my/rental-points/{$point->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rentalPoint');

        $this->assertDatabaseHas('rental_points', ['id' => $point->id]);
        $this->withToken($token)->getJson('/v1/my/rental-points')
            ->assertJsonPath('0.equipmentCount', 1);
    }

    /**
     * @return array{0: User, 1: string}
     */
    private function user(string $email = 'owner@example.com', string $role = User::ROLE_OWNER): array
    {
        $user = User::query()->create([
            'name' => 'Owner',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => $role,
        ]);

        $token = 'token-'.$email;
        $user->tokens()->create(['token' => hash('sha256', $token)]);

        return [$user, $token];
    }

    private function createEquipment(RentalPoint $point): Equipment
    {
        return Equipment::query()->create([
            'id' => 'test-ski',
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
        ]);
    }
}
