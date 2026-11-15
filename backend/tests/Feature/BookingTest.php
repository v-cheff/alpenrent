<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_list_bookings(): void
    {
        $this->getJson('/v1/bookings')->assertUnauthorized();
    }

    public function test_user_can_create_and_list_their_bookings(): void
    {
        [$user, $token] = $this->authenticatedUser();
        $this->createEquipment();

        $response = $this->withToken($token)->postJson('/v1/bookings', [
            'equipmentId' => 'test-ski',
            'size' => '160',
            'startDate' => '2026-12-01',
            'endDate' => '2026-12-04',
        ]);

        $response->assertCreated()
            ->assertJsonPath('equipmentId', 'test-ski')
            ->assertJsonPath('size', '160')
            ->assertJsonPath('startDate', '2026-12-01')
            ->assertJsonPath('endDate', '2026-12-04')
            ->assertJsonPath('equipment.id', 'test-ski');

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'equipment_id' => 'test-ski',
            'size' => '160',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-04',
        ]);

        $this->withToken($token)
            ->getJson('/v1/bookings')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.equipmentId', 'test-ski')
            ->assertJsonPath('0.size', '160');
    }

    public function test_user_does_not_see_other_users_bookings(): void
    {
        [, $token] = $this->authenticatedUser('alice@example.com');
        [$otherUser] = $this->authenticatedUser('bob@example.com');
        $this->createEquipment();

        Booking::query()->create([
            'user_id' => $otherUser->id,
            'equipment_id' => 'test-ski',
            'size' => '160',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-04',
        ]);

        $this->withToken($token)
            ->getJson('/v1/bookings')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_booking_requires_end_date_after_start_date(): void
    {
        [, $token] = $this->authenticatedUser();
        $this->createEquipment();

        $this->withToken($token)
            ->postJson('/v1/bookings', [
                'equipmentId' => 'test-ski',
                'size' => '160',
                'startDate' => '2026-12-04',
                'endDate' => '2026-12-01',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('endDate');
    }

    public function test_booking_rejects_unavailable_size(): void
    {
        [, $token] = $this->authenticatedUser();
        $this->createEquipment();

        $this->withToken($token)
            ->postJson('/v1/bookings', [
                'equipmentId' => 'test-ski',
                'size' => '999',
                'startDate' => '2026-12-01',
                'endDate' => '2026-12-04',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('size');
    }

    public function test_booking_rejects_dates_when_equipment_is_not_in_stock(): void
    {
        [, $token] = $this->authenticatedUser();
        $this->createEquipment([
            'available_now' => false,
            'available_dates' => ['2026-12-01', '2026-12-02'],
        ]);

        $this->withToken($token)
            ->postJson('/v1/bookings', [
                'equipmentId' => 'test-ski',
                'size' => '160',
                'startDate' => '2026-12-01',
                'endDate' => '2026-12-04',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('startDate');
    }

    public function test_booking_rejects_overlapping_dates_for_the_same_item_and_size(): void
    {
        [$user, $token] = $this->authenticatedUser();
        $this->createEquipment();

        Booking::query()->create([
            'user_id' => $user->id,
            'equipment_id' => 'test-ski',
            'size' => '160',
            'start_date' => '2026-12-02',
            'end_date' => '2026-12-05',
        ]);

        $this->withToken($token)
            ->postJson('/v1/bookings', [
                'equipmentId' => 'test-ski',
                'size' => '160',
                'startDate' => '2026-12-01',
                'endDate' => '2026-12-03',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('startDate');
    }

    public function test_booking_allows_same_dates_for_a_different_size(): void
    {
        [$user, $token] = $this->authenticatedUser();
        $this->createEquipment();

        Booking::query()->create([
            'user_id' => $user->id,
            'equipment_id' => 'test-ski',
            'size' => '150',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-04',
        ]);

        $this->withToken($token)
            ->postJson('/v1/bookings', [
                'equipmentId' => 'test-ski',
                'size' => '160',
                'startDate' => '2026-12-01',
                'endDate' => '2026-12-04',
            ])
            ->assertCreated()
            ->assertJsonPath('size', '160');
    }

    public function test_user_can_update_booking_dates_and_size(): void
    {
        [$user, $token] = $this->authenticatedUser();
        $this->createEquipment();

        $booking = Booking::query()->create([
            'user_id' => $user->id,
            'equipment_id' => 'test-ski',
            'size' => '150',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-04',
        ]);

        $this->withToken($token)
            ->putJson("/v1/bookings/{$booking->id}", [
                'size' => '160',
                'startDate' => '2026-12-10',
                'endDate' => '2026-12-12',
            ])
            ->assertOk()
            ->assertJsonPath('size', '160')
            ->assertJsonPath('startDate', '2026-12-10')
            ->assertJsonPath('endDate', '2026-12-12');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'size' => '160',
            'start_date' => '2026-12-10',
            'end_date' => '2026-12-12',
        ]);
    }

    public function test_updating_a_booking_does_not_conflict_with_itself(): void
    {
        [$user, $token] = $this->authenticatedUser();
        $this->createEquipment();

        $booking = Booking::query()->create([
            'user_id' => $user->id,
            'equipment_id' => 'test-ski',
            'size' => '160',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-04',
        ]);

        $this->withToken($token)
            ->putJson("/v1/bookings/{$booking->id}", [
                'endDate' => '2026-12-06',
            ])
            ->assertOk()
            ->assertJsonPath('endDate', '2026-12-06');
    }

    public function test_user_cannot_update_another_users_booking(): void
    {
        [, $token] = $this->authenticatedUser('alice@example.com');
        [$otherUser] = $this->authenticatedUser('bob@example.com');
        $this->createEquipment();

        $booking = Booking::query()->create([
            'user_id' => $otherUser->id,
            'equipment_id' => 'test-ski',
            'size' => '160',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-04',
        ]);

        $this->withToken($token)
            ->putJson("/v1/bookings/{$booking->id}", [
                'size' => '150',
            ])
            ->assertNotFound();
    }

    public function test_user_can_cancel_a_booking(): void
    {
        [$user, $token] = $this->authenticatedUser();
        $this->createEquipment();

        $booking = Booking::query()->create([
            'user_id' => $user->id,
            'equipment_id' => 'test-ski',
            'size' => '160',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-04',
        ]);

        $this->withToken($token)
            ->deleteJson("/v1/bookings/{$booking->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Booking cancelled successfully.');

        $this->assertDatabaseMissing('bookings', [
            'id' => $booking->id,
        ]);
    }

    /**
     * @return array{0: User, 1: string}
     */
    private function authenticatedUser(string $email = 'jane@example.com'): array
    {
        $user = User::query()->create([
            'name' => 'Jane Doe',
            'email' => $email,
            'password' => Hash::make('password123'),
        ]);

        $token = 'token-'.$email;
        $user->tokens()->create([
            'token' => hash('sha256', $token),
        ]);

        return [$user, $token];
    }

    private function createEquipment(array $overrides = []): Equipment
    {
        return Equipment::query()->create(array_merge([
            'id' => 'test-ski',
            'name' => 'Test Ski',
            'category' => 'ski',
            'category_label' => 'Ski Set',
            'image' => 'https://example.com/ski.jpg',
            'specs' => [],
            'daily_rate' => 35,
            'deposit' => 100,
            'sizes' => ['150', '160'],
            'available_now' => true,
            'available_dates' => [],
        ], $overrides));
    }
};
