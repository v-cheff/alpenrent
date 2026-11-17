<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use RuntimeException;
use Throwable;

class EquipmentController extends Controller
{
    /**
     * Public catalog: equipment from every rental point.
     */
    public function index(): JsonResponse
    {
        $equipment = Equipment::query()
            ->with('rentalPoint')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Equipment $item) => $item->toApiArray());

        return response()->json($equipment);
    }

    /**
     * Equipment listed at the authenticated user's own rental points.
     */
    public function mine(Request $request): JsonResponse
    {
        $equipment = Equipment::query()
            ->ownedBy($request->user())
            ->with('rentalPoint')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Equipment $item) => $item->toApiArray());

        return response()->json($equipment);
    }

    public function show(Equipment $equipment): JsonResponse
    {
        return response()->json($equipment->load('rentalPoint')->toApiArray());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rentalPointId' => ['required', 'integer', $this->ownRentalPointRule($request)],
            'id' => ['nullable', 'string', 'max:255', 'alpha_dash:ascii', 'unique:equipment,id'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:ski,board,boots,kids'],
            'categoryLabel' => ['nullable', 'string', 'max:255'],
            'badge' => ['nullable', 'string', 'max:255'],
            'badgeTone' => ['nullable', 'in:new,waxed,info,kids'],
            'image' => ['nullable', 'required_without:imageFile', 'url:http,https', 'max:2048'],
            'imageFile' => ['nullable', 'required_without:image', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'specs' => ['sometimes', 'array', 'max:3'],
            'specs.*' => ['required', 'string', 'max:80'],
            'dailyRate' => ['required', 'numeric', 'between:0,999999.99'],
            'halfDayRate' => ['nullable', 'numeric', 'between:0,999999.99'],
            'deposit' => ['required', 'numeric', 'between:0,999999.99'],
            'sizes' => ['sometimes', 'array'],
            'sizes.*' => ['required', 'string', 'max:100'],
            'availableNow' => ['required', 'boolean'],
            'availableDates' => ['sometimes', 'array'],
            'availableDates.*' => ['required', 'date_format:Y-m-d'],
        ]);

        $id = $validated['id'] ?? $this->uniqueId($validated['name']);
        $imagePath = null;

        if ($request->hasFile('imageFile')) {
            $imagePath = $request->file('imageFile')->store('equipment', 'public');
            if ($imagePath === false) {
                throw new RuntimeException('Unable to store the uploaded equipment image.');
            }
            $image = url(Storage::disk('public')->url($imagePath));
        } else {
            $image = $validated['image'];
        }

        try {
            $equipment = Equipment::query()->create([
                'id' => $id,
                'rental_point_id' => $validated['rentalPointId'],
                'name' => $validated['name'],
                'category' => $validated['category'],
                'category_label' => $validated['categoryLabel'] ?? 'Custom equipment',
                'badge' => $validated['badge'] ?? 'New',
                'badge_tone' => $validated['badgeTone'] ?? 'new',
                'image' => $image,
                'specs' => $validated['specs'] ?? [],
                'daily_rate' => $validated['dailyRate'],
                'half_day_rate' => $validated['halfDayRate'] ?? null,
                'deposit' => $validated['deposit'],
                'sizes' => $validated['sizes'] ?? [],
                'available_now' => $validated['availableNow'],
                'available_dates' => $validated['availableDates'] ?? [],
            ]);
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }

        return response()->json($equipment->load('rentalPoint')->toApiArray(), 201);
    }

    public function update(Request $request, Equipment $equipment): JsonResponse
    {
        $this->assertOwned($request, $equipment);

        $validated = $request->validate([
            'rentalPointId' => ['sometimes', 'required', 'integer', $this->ownRentalPointRule($request)],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'category' => ['sometimes', 'required', 'in:ski,board,boots,kids'],
            'categoryLabel' => ['nullable', 'string', 'max:255'],
            'badge' => ['nullable', 'string', 'max:255'],
            'badgeTone' => ['nullable', 'in:new,waxed,info,kids'],
            'image' => ['nullable', 'url:http,https', 'max:2048'],
            'imageFile' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'specs' => ['sometimes', 'array', 'max:3'],
            'specs.*' => ['required', 'string', 'max:80'],
            'dailyRate' => ['sometimes', 'required', 'numeric', 'between:0,999999.99'],
            'halfDayRate' => ['nullable', 'numeric', 'between:0,999999.99'],
            'deposit' => ['sometimes', 'required', 'numeric', 'between:0,999999.99'],
            'sizes' => ['sometimes', 'array'],
            'sizes.*' => ['required', 'string', 'max:100'],
            'availableNow' => ['sometimes', 'required', 'boolean'],
            'availableDates' => ['sometimes', 'array'],
            'availableDates.*' => ['required', 'date_format:Y-m-d'],
        ]);

        $newStoredPath = null;
        $image = $equipment->image;
        $previousImage = $equipment->image;

        if ($request->hasFile('imageFile')) {
            $newStoredPath = $request->file('imageFile')->store('equipment', 'public');
            if ($newStoredPath === false) {
                throw new RuntimeException('Unable to store the uploaded equipment image.');
            }
            $image = url(Storage::disk('public')->url($newStoredPath));
        } elseif (array_key_exists('image', $validated) && filled($validated['image'])) {
            $image = $validated['image'];
        }

        try {
            $updateData = [];

            if (array_key_exists('rentalPointId', $validated)) {
                $updateData['rental_point_id'] = $validated['rentalPointId'];
            }
            if (array_key_exists('name', $validated)) {
                $updateData['name'] = $validated['name'];
            }
            if (array_key_exists('category', $validated)) {
                $updateData['category'] = $validated['category'];
            }
            if (array_key_exists('categoryLabel', $validated)) {
                $updateData['category_label'] = $validated['categoryLabel'] ?? 'Custom equipment';
            }
            if (array_key_exists('badge', $validated)) {
                $updateData['badge'] = $validated['badge'] ?? 'New';
            }
            if (array_key_exists('badgeTone', $validated)) {
                $updateData['badge_tone'] = $validated['badgeTone'] ?? 'new';
            }

            $updateData['image'] = $image;

            if ($request->has('specs')) {
                $updateData['specs'] = $validated['specs'] ?? [];
            }
            if (array_key_exists('dailyRate', $validated)) {
                $updateData['daily_rate'] = $validated['dailyRate'];
            }
            if (array_key_exists('halfDayRate', $validated)) {
                // An empty value switches half-day rental off.
                $updateData['half_day_rate'] = $validated['halfDayRate'];
            }
            if (array_key_exists('deposit', $validated)) {
                $updateData['deposit'] = $validated['deposit'];
            }
            if ($request->has('sizes')) {
                $updateData['sizes'] = $validated['sizes'] ?? [];
            }
            if (array_key_exists('availableNow', $validated)) {
                $updateData['available_now'] = (bool) $validated['availableNow'];
            }
            if ($request->has('availableDates')) {
                $updateData['available_dates'] = $validated['availableDates'] ?? [];
            }

            $equipment->update($updateData);

            if ($image !== $previousImage) {
                $this->deleteStoredImageIfLocal($previousImage);
            }
        } catch (Throwable $exception) {
            if ($newStoredPath !== null) {
                Storage::disk('public')->delete($newStoredPath);
            }

            throw $exception;
        }

        return response()->json($equipment->fresh('rentalPoint')->toApiArray());
    }

    public function destroy(Request $request, Equipment $equipment): JsonResponse
    {
        $this->assertOwned($request, $equipment);

        $this->deleteStoredImageIfLocal($equipment->image);
        $equipment->delete();

        return response()->json([
            'message' => 'Equipment deleted successfully.',
        ]);
    }

    /**
     * Only the owner of the equipment's rental point may change it; others get a 404
     * so equipment management doesn't reveal what other owners have.
     */
    private function assertOwned(Request $request, Equipment $equipment): void
    {
        if (! $equipment->isOwnedBy($request->user())) {
            abort(404);
        }
    }

    private function ownRentalPointRule(Request $request): Exists
    {
        return Rule::exists('rental_points', 'id')->where('user_id', $request->user()->id);
    }

    private function deleteStoredImageIfLocal(?string $imageUrl): void
    {
        if (! $imageUrl) {
            return;
        }

        $path = parse_url($imageUrl, PHP_URL_PATH);
        if ($path && str_contains($path, '/storage/')) {
            $storagePath = preg_replace('#^.*?/storage/#', '', $path);
            if ($storagePath && Storage::disk('public')->exists($storagePath)) {
                Storage::disk('public')->delete($storagePath);
            }
        }
    }

    private function uniqueId(string $name): string
    {
        $baseId = Str::slug($name) ?: 'equipment';
        $id = $baseId;
        $suffix = 2;

        while (Equipment::query()->whereKey($id)->exists()) {
            $id = "{$baseId}-{$suffix}";
            $suffix++;
        }

        return $id;
    }
}
