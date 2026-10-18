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
}
