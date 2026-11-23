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

    public function test_owner_account_cannot_make_bookings(): void
    {
        [, $token] = $this->authenticatedUser('owner@example.com', User::ROLE_OWNER);
        $this->createEquipment();

        $this->withToken($token)->getJson('/v1/bookings')->assertForbidden();
        $this->withToken($token)->postJson('/v1/bookings', [
            'equipmentId' => 'test-ski',
            'size' => '160',
            'startDate' => '2026-12-01',
            'endDate' => '2026-12-04',
        ])->assertForbidden();

        $this->assertDatabaseCount('bookings', 0);
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

    public function test_booking_rejects_end_date_before_start_date(): void
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

    public function test_booking_cannot_start_in_the_past(): void
    {
        [, $token] = $this->authenticatedUser();
        $this->createEquipment();

        // "Now" is frozen at 2026-11-15 in TestCase.
        $this->withToken($token)
            ->postJson('/v1/bookings', [
                'equipmentId' => 'test-ski',
                'size' => '160',
                'startDate' => '2026-11-14',
                'endDate' => '2026-11-16',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('startDate');

        $this->withToken($token)
            ->postJson('/v1/bookings', [
                'equipmentId' => 'test-ski',
                'size' => '160',
                'startDate' => '2026-11-15',
                'endDate' => '2026-11-15',
            ])
            ->assertCreated();
    }

    public function test_booking_days_are_counted_inclusively(): void
    {
        [, $token] = $this->authenticatedUser();
        $this->createEquipment(['daily_rate' => 35]);

        $this->withToken($token)->postJson('/v1/bookings', [
            'equipmentId' => 'test-ski',
            'size' => '160',
            'startDate' => '2026-12-01',
            'endDate' => '2026-12-04',
        ])->assertCreated()
            ->assertJsonPath('rentalType', 'full_day')
            ->assertJsonPath('days', 4)
            ->assertJsonPath('total', 140);
    }

    public function test_user_can_book_a_single_day(): void
    {
        [, $token] = $this->authenticatedUser();
        $this->createEquipment(['daily_rate' => 35]);

        $this->withToken($token)->postJson('/v1/bookings', [
            'equipmentId' => 'test-ski',
            'size' => '160',
            'startDate' => '2026-12-10',
            'endDate' => '2026-12-10',
        ])->assertCreated()
            ->assertJsonPath('days', 1)
            ->assertJsonPath('total', 35);
    }

    public function test_single_day_booking_conflicts_with_the_last_day_of_another_booking(): void
    {
        [, $token] = $this->authenticatedUser();
        [$otherUser] = $this->authenticatedUser('bob@example.com');
        $this->createEquipment();

        Booking::query()->create([
            'user_id' => $otherUser->id,
            'equipment_id' => 'test-ski',
            'size' => '160',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-04',
        ]);

        $this->withToken($token)->postJson('/v1/bookings', [
            'equipmentId' => 'test-ski',
            'size' => '160',
            'startDate' => '2026-12-04',
            'endDate' => '2026-12-04',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('startDate');
    }

    public function test_user_can_book_a_half_day_when_the_equipment_offers_it(): void
    {
        [, $token] = $this->authenticatedUser();
        $this->createEquipment(['daily_rate' => 35, 'half_day_rate' => 22]);

        $this->withToken($token)->postJson('/v1/bookings', [
            'equipmentId' => 'test-ski',
            'size' => '160',
            'rentalType' => 'half_day',
            'startDate' => '2026-12-10',
            'endDate' => '2026-12-10',
        ])->assertCreated()
            ->assertJsonPath('rentalType', 'half_day')
            ->assertJsonPath('days', 1)
            ->assertJsonPath('total', 22)
            ->assertJsonPath('equipment.halfDayRate', 22);
    }

    public function test_half_day_booking_must_be_a_single_date_and_offered(): void
    {
        [, $token] = $this->authenticatedUser();
        $this->createEquipment(['id' => 'with-half-day', 'half_day_rate' => 22]);
        $this->createEquipment(['id' => 'full-days-only']);

        $this->withToken($token)->postJson('/v1/bookings', [
            'equipmentId' => 'with-half-day',
            'size' => '160',
            'rentalType' => 'half_day',
            'startDate' => '2026-12-10',
            'endDate' => '2026-12-11',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('rentalType');

        $this->withToken($token)->postJson('/v1/bookings', [
            'equipmentId' => 'full-days-only',
            'size' => '160',
            'rentalType' => 'half_day',
            'startDate' => '2026-12-10',
            'endDate' => '2026-12-10',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('rentalType');

        $this->assertDatabaseCount('bookings', 0);
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

    public function test_owner_sees_incoming_bookings_only_for_their_equipment(): void
    {
        [$owner, $ownerToken] = $this->authenticatedUser('owner@example.com', User::ROLE_OWNER);
        [$renter, $renterToken] = $this->authenticatedUser('renter@example.com');
        [$otherOwner] = $this->authenticatedUser('other@example.com', User::ROLE_OWNER);

        $point = $owner->rentalPoints()->create(['name' => 'Innsbruck Store']);
        $otherPoint = $otherOwner->rentalPoints()->create(['name' => 'Other Store']);
        $this->createEquipment(['id' => 'owner-ski', 'rental_point_id' => $point->id]);
        $this->createEquipment(['id' => 'other-ski', 'rental_point_id' => $otherPoint->id]);

        foreach (['owner-ski', 'other-ski'] as $equipmentId) {
            $this->withToken($renterToken)->postJson('/v1/bookings', [
                'equipmentId' => $equipmentId,
                'size' => '160',
                'startDate' => '2026-12-01',
                'endDate' => '2026-12-04',
            ])->assertCreated();
        }

        $this->withToken($ownerToken)
            ->getJson('/v1/my/bookings')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.equipmentId', 'owner-ski')
            ->assertJsonPath('0.equipment.rentalPoint.name', 'Innsbruck Store')
            ->assertJsonPath('0.renter.id', $renter->id)
            ->assertJsonPath('0.renter.email', 'renter@example.com');

        $this->withoutToken()->getJson('/v1/my/bookings')->assertUnauthorized();
    }

    /**
     * @return array{0: User, 1: string}
     */
    private function authenticatedUser(string $email = 'jane@example.com', string $role = User::ROLE_RENTER): array
    {
        $user = User::query()->create([
            'name' => 'Jane Doe',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => $role,
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
