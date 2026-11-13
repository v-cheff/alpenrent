<?php

namespace Tests\Feature;

use App\Models\Equipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EquipmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_equipment_can_be_created_with_an_uploaded_image(): void
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        Storage::fake('public');

        $response = $this->post('/v1/equipment', [
            'name' => 'Test Ski Set',
            'category' => 'ski',
            'categoryLabel' => 'All-Mountain Ski Set',
            'badge' => 'New',
            'badgeTone' => 'new',
            'imageFile' => UploadedFile::fake()->createWithContent(
                'test-ski.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jCecAAAAASUVORK5CYII='),
            ),
            'specs' => ['Radius 14 m', 'Carbon core'],
            'dailyRate' => 35,
            'deposit' => 100,
            'sizes' => ['150', '160'],
            'availableNow' => '1',
            'availableDates' => ['2026-11-01'],
        ], ['Accept' => 'application/json']);

        $response->assertCreated()
            ->assertJsonPath('id', 'test-ski-set')
            ->assertJsonPath('categoryLabel', 'All-Mountain Ski Set')
            ->assertJsonPath('specs.0', 'Radius 14 m')
            ->assertJsonPath('availableNow', true);

        $this->assertDatabaseHas('equipment', [
            'id' => 'test-ski-set',
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
        $response = $this->postJson('/v1/equipment', [
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

    public function test_equipment_creation_requires_an_image_and_valid_category(): void
    {
        $this->postJson('/v1/equipment', [
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
        $this->postJson('/v1/equipment', [
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

    public function test_equipment_list_returns_saved_records(): void
    {
        Equipment::query()->create([
            'id' => 'saved-board',
            'name' => 'Saved Board',
            'category' => 'board',
            'category_label' => 'Snowboard',
            'image' => 'https://example.com/board.jpg',
            'specs' => ['Directional twin'],
            'daily_rate' => 30,
            'deposit' => 90,
            'sizes' => ['156'],
            'available_now' => true,
            'available_dates' => [],
        ]);

        $this->getJson('/v1/equipment')
            ->assertOk()
            ->assertJsonPath('0.id', 'saved-board')
            ->assertJsonPath('0.dailyRate', 30)
            ->assertJsonPath('0.availableDates', []);
    }

    public function test_equipment_can_be_retrieved_by_id(): void
    {
        Equipment::query()->create([
            'id' => 'detail-item',
            'name' => 'Detail Item',
            'category' => 'boots',
            'category_label' => 'Ski Boots',
            'image' => 'https://example.com/boots.jpg',
            'specs' => ['Flex 110'],
            'daily_rate' => 20,
            'deposit' => 60,
            'sizes' => ['27.5'],
            'available_now' => true,
            'available_dates' => ['2026-12-01'],
        ]);

        $this->getJson('/v1/equipment/detail-item')
            ->assertOk()
            ->assertJsonPath('id', 'detail-item')
            ->assertJsonPath('name', 'Detail Item')
            ->assertJsonPath('category', 'boots')
            ->assertJsonPath('dailyRate', 20);
    }

    public function test_equipment_can_be_updated_without_replacing_image(): void
    {
        Equipment::query()->create([
            'id' => 'edit-ski',
            'name' => 'Old Ski Name',
            'category' => 'ski',
            'category_label' => 'Old Label',
            'image' => 'https://example.com/ski.jpg',
            'specs' => ['Old spec'],
            'daily_rate' => 30,
            'deposit' => 100,
            'sizes' => ['160'],
            'available_now' => true,
            'available_dates' => [],
        ]);

        $response = $this->putJson('/v1/equipment/edit-ski', [
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

    public function test_equipment_can_be_updated_with_new_uploaded_image_and_deletes_old_image(): void
    {
        Storage::fake('public');

        $oldImageFile = UploadedFile::fake()->createWithContent('old.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jCecAAAAASUVORK5CYII='));
        $storedOldPath = $oldImageFile->store('equipment', 'public');
        $oldImageUrl = url(Storage::disk('public')->url($storedOldPath));

        Equipment::query()->create([
            'id' => 'edit-image-item',
            'name' => 'Item With Image',
            'category' => 'board',
            'category_label' => 'Snowboard',
            'image' => $oldImageUrl,
            'specs' => [],
            'daily_rate' => 28,
            'deposit' => 85,
            'sizes' => ['158'],
            'available_now' => true,
            'available_dates' => [],
        ]);

        Storage::disk('public')->assertExists($storedOldPath);

        $response = $this->post('/v1/equipment/edit-image-item', [
            '_method' => 'PUT',
            'name' => 'Item With New Image',
            'imageFile' => UploadedFile::fake()->createWithContent('new.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jCecAAAAASUVORK5CYII=')),
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

        $imageFile = UploadedFile::fake()->createWithContent('item-to-delete.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jCecAAAAASUVORK5CYII='));
        $storedPath = $imageFile->store('equipment', 'public');
        $imageUrl = url(Storage::disk('public')->url($storedPath));

        Equipment::query()->create([
            'id' => 'to-delete-item',
            'name' => 'To Delete Item',
            'category' => 'kids',
            'category_label' => 'Kids Gear',
            'image' => $imageUrl,
            'specs' => [],
            'daily_rate' => 15,
            'deposit' => 50,
            'sizes' => ['120'],
            'available_now' => true,
            'available_dates' => [],
        ]);

        Storage::disk('public')->assertExists($storedPath);

        $response = $this->deleteJson('/v1/equipment/to-delete-item');
        $response->assertOk()
            ->assertJsonPath('message', 'Equipment deleted successfully.');

        $this->assertDatabaseMissing('equipment', [
            'id' => 'to-delete-item',
        ]);

        Storage::disk('public')->assertMissing($storedPath);
    }
}
