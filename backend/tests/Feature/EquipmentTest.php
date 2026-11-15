<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\RentalPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EquipmentTest extends TestCase
{
    use RefreshDatabase;

    private const PIXEL_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jCecAAAAASUVORK5CYII=';

    public function test_equipment_can_be_created_with_an_uploaded_image(): void
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        Storage::fake('public');
        [, $token, $point] = $this->owner();

        $response = $this->withToken($token)->post('/v1/equipment', [
            'rentalPointId' => $point->id,
            'name' => 'Test Ski Set',
            'category' => 'ski',
            'categoryLabel' => 'All-Mountain Ski Set',
            'badge' => 'New',
            'badgeTone' => 'new',
            'imageFile' => $this->pixel('test-ski.png'),
            'specs' => ['Radius 14 m', 'Carbon core'],
            'dailyRate' => 35,
            'deposit' => 100,
            'sizes' => ['150', '160'],
            'availableNow' => '1',
            'availableDates' => ['2026-11-01'],
        ], ['Accept' => 'application/json']);

        $response->assertCreated()
            ->assertJsonPath('id', 'test-ski-set')
            ->assertJsonPath('rentalPointId', $point->id)
            ->assertJsonPath('rentalPoint.name', 'Innsbruck Store')
            ->assertJsonPath('categoryLabel', 'All-Mountain Ski Set')
            ->assertJsonPath('specs.0', 'Radius 14 m')
            ->assertJsonPath('availableNow', true);

        $this->assertDatabaseHas('equipment', [
            'id' => 'test-ski-set',
            'rental_point_id' => $point->id,
            'name' => 'Test Ski Set',
            'category' => 'ski',
        ]);

        $equipment = Equipment::query()->findOrFail('test-ski-set');
        $this->assertStringContainsString('/storage/equipment/', $equipment->image);
        Storage::disk('public')->assertExists(
            str_replace('/storage/', '', parse_url($equipment->image, PHP_URL_PATH)),
        );
    }

    public function test_equipment_can_be_created_with_an_image_url(): void
    {
        [, $token, $point] = $this->owner();

        $response = $this->withToken($token)->postJson('/v1/equipment', [
            'rentalPointId' => $point->id,
            'name' => 'Test Snowboard',
            'category' => 'board',
            'image' => 'https://example.com/snowboard.jpg',
            'dailyRate' => 25,
            'deposit' => 80,
            'availableNow' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('id', 'test-snowboard')
            ->assertJsonPath('image', 'https://example.com/snowboard.jpg')
            ->assertJsonPath('specs', [])
            ->assertJsonPath('sizes', [])
            ->assertJsonPath('availableDates', []);
    }

    public function test_guest_cannot_create_equipment(): void
    {
        $this->postJson('/v1/equipment', [
            'name' => 'Guest Ski',
            'category' => 'ski',
            'image' => 'https://example.com/ski.jpg',
            'dailyRate' => 25,
            'deposit' => 80,
            'availableNow' => true,
        ])->assertUnauthorized();

        $this->assertDatabaseCount('equipment', 0);
    }

    public function test_equipment_requires_one_of_the_users_own_rental_points(): void
    {
        [, $token] = $this->owner('alice@example.com');
        [, , $otherPoint] = $this->owner('bob@example.com');

        $payload = [
            'name' => 'Sneaky Ski',
            'category' => 'ski',
            'image' => 'https://example.com/ski.jpg',
            'dailyRate' => 25,
            'deposit' => 80,
            'availableNow' => true,
        ];

        $this->withToken($token)->postJson('/v1/equipment', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rentalPointId');

        $this->withToken($token)->postJson('/v1/equipment', [...$payload, 'rentalPointId' => $otherPoint->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rentalPointId');
    }

    public function test_equipment_creation_requires_an_image_and_valid_category(): void
    {
        [, $token, $point] = $this->owner();

        $this->withToken($token)->postJson('/v1/equipment', [
            'rentalPointId' => $point->id,
            'name' => 'Invalid Equipment',
            'category' => 'unknown',
            'specs' => [],
            'dailyRate' => 25,
            'deposit' => 80,
            'sizes' => [],
            'availableNow' => true,
            'availableDates' => [],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['category', 'image', 'imageFile']);
    }

    public function test_equipment_creation_rejects_more_than_three_specs(): void
    {
        [, $token, $point] = $this->owner();

        $this->withToken($token)->postJson('/v1/equipment', [
            'rentalPointId' => $point->id,
            'name' => 'Too Many Specs',
            'category' => 'ski',
            'image' => 'https://example.com/ski.jpg',
            'specs' => ['One', 'Two', 'Three', 'Four'],
            'dailyRate' => 25,
            'deposit' => 80,
            'availableNow' => true,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('specs');
    }

    public function test_equipment_list_returns_saved_records_with_their_rental_point(): void
    {
        [, , $point] = $this->owner();
        $this->createEquipment($point, [
            'id' => 'saved-board',
            'category' => 'board',
            'daily_rate' => 30,
        ]);

        $this->getJson('/v1/equipment')
            ->assertOk()
            ->assertJsonPath('0.id', 'saved-board')
            ->assertJsonPath('0.dailyRate', 30)
            ->assertJsonPath('0.rentalPoint.name', 'Innsbruck Store')
            ->assertJsonPath('0.availableDates', []);
    }

    public function test_equipment_can_be_retrieved_by_id(): void
    {
        [, , $point] = $this->owner();
        $this->createEquipment($point, [
            'id' => 'detail-item',
            'name' => 'Detail Item',
            'category' => 'boots',
            'daily_rate' => 20,
        ]);

        $this->getJson('/v1/equipment/detail-item')
            ->assertOk()
            ->assertJsonPath('id', 'detail-item')
            ->assertJsonPath('name', 'Detail Item')
            ->assertJsonPath('category', 'boots')
            ->assertJsonPath('rentalPointId', $point->id)
            ->assertJsonPath('dailyRate', 20);
    }

    public function test_owner_sees_only_equipment_at_their_rental_points(): void
    {
        [, $token, $point] = $this->owner('alice@example.com');
        [, , $otherPoint] = $this->owner('bob@example.com');
        $this->createEquipment($point, ['id' => 'alice-ski']);
        $this->createEquipment($otherPoint, ['id' => 'bob-ski']);

        $this->withToken($token)
            ->getJson('/v1/my/equipment')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', 'alice-ski');

        $this->withoutToken()->getJson('/v1/my/equipment')->assertUnauthorized();
    }

    public function test_equipment_can_be_updated_without_replacing_image(): void
    {
        [, $token, $point] = $this->owner();
        $this->createEquipment($point, [
            'id' => 'edit-ski',
            'name' => 'Old Ski Name',
            'daily_rate' => 30,
            'specs' => ['Old spec'],
        ]);

        $response = $this->withToken($token)->putJson('/v1/equipment/edit-ski', [
            'name' => 'Updated Ski Name',
            'dailyRate' => 45.5,
            'specs' => ['New spec 1', 'New spec 2'],
            'availableNow' => false,
        ]);

        $response->assertOk()
            ->assertJsonPath('id', 'edit-ski')
            ->assertJsonPath('name', 'Updated Ski Name')
            ->assertJsonPath('dailyRate', 45.5)
            ->assertJsonPath('availableNow', false)
            ->assertJsonPath('image', 'https://example.com/ski.jpg')
            ->assertJsonPath('specs.0', 'New spec 1');

        $this->assertDatabaseHas('equipment', [
            'id' => 'edit-ski',
            'name' => 'Updated Ski Name',
            'daily_rate' => 45.5,
            'available_now' => false,
        ]);
    }

    public function test_owner_can_move_equipment_between_their_rental_points(): void
    {
        [$user, $token, $point] = $this->owner();
        $secondPoint = $user->rentalPoints()->create(['name' => 'Second Store']);
        $this->createEquipment($point, ['id' => 'moving-ski']);

        $this->withToken($token)
            ->putJson('/v1/equipment/moving-ski', ['rentalPointId' => $secondPoint->id])
            ->assertOk()
            ->assertJsonPath('rentalPointId', $secondPoint->id)
            ->assertJsonPath('rentalPoint.name', 'Second Store');
    }

    public function test_user_cannot_update_or_delete_another_owners_equipment(): void
    {
        [, $token] = $this->owner('alice@example.com');
        [, , $otherPoint] = $this->owner('bob@example.com');
        $this->createEquipment($otherPoint, ['id' => 'bob-ski', 'name' => 'Bob Ski']);

        $this->withToken($token)
            ->putJson('/v1/equipment/bob-ski', ['name' => 'Hijacked'])
            ->assertNotFound();

        $this->withToken($token)
            ->deleteJson('/v1/equipment/bob-ski')
            ->assertNotFound();

        $this->assertDatabaseHas('equipment', ['id' => 'bob-ski', 'name' => 'Bob Ski']);
    }

    public function test_equipment_can_be_updated_with_new_uploaded_image_and_deletes_old_image(): void
    {
        Storage::fake('public');
        [, $token, $point] = $this->owner();

        $storedOldPath = $this->pixel('old.png')->store('equipment', 'public');
        $oldImageUrl = url(Storage::disk('public')->url($storedOldPath));

        $this->createEquipment($point, [
            'id' => 'edit-image-item',
            'name' => 'Item With Image',
            'image' => $oldImageUrl,
        ]);

        Storage::disk('public')->assertExists($storedOldPath);

        $response = $this->withToken($token)->post('/v1/equipment/edit-image-item', [
            '_method' => 'PUT',
            'name' => 'Item With New Image',
            'imageFile' => $this->pixel('new.png'),
        ], ['Accept' => 'application/json']);

        $response->assertOk()
            ->assertJsonPath('name', 'Item With New Image');

        $equipment = Equipment::query()->findOrFail('edit-image-item');
        $this->assertNotSame($oldImageUrl, $equipment->image);

        // Old image file should have been deleted from storage
        Storage::disk('public')->assertMissing($storedOldPath);

        // New image file should exist in storage
        $newPath = str_replace('/storage/', '', parse_url($equipment->image, PHP_URL_PATH));
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_equipment_can_be_deleted_and_removes_stored_image(): void
    {
        Storage::fake('public');
        [, $token, $point] = $this->owner();

        $storedPath = $this->pixel('item-to-delete.png')->store('equipment', 'public');
        $imageUrl = url(Storage::disk('public')->url($storedPath));

        $this->createEquipment($point, [
            'id' => 'to-delete-item',
            'image' => $imageUrl,
        ]);

        Storage::disk('public')->assertExists($storedPath);

        $this->withToken($token)
            ->deleteJson('/v1/equipment/to-delete-item')
            ->assertOk()
            ->assertJsonPath('message', 'Equipment deleted successfully.');

        $this->assertDatabaseMissing('equipment', [
            'id' => 'to-delete-item',
        ]);

        Storage::disk('public')->assertMissing($storedPath);
    }

    /**
     * @return array{0: User, 1: string, 2: RentalPoint}
     */
    private function owner(string $email = 'owner@example.com'): array
    {
        $user = User::query()->create([
            'name' => 'Owner',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => User::ROLE_OWNER,
        ]);

        $token = 'token-'.$email;
        $user->tokens()->create(['token' => hash('sha256', $token)]);

        $point = $user->rentalPoints()->create(['name' => 'Innsbruck Store']);

        return [$user, $token, $point];
    }

    private function createEquipment(RentalPoint $point, array $overrides = []): Equipment
    {
        return Equipment::query()->create(array_merge([
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
        ], $overrides));
    }

    private function pixel(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(self::PIXEL_PNG));
    }
}
