<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class EquipmentController extends Controller
{
    public function index(): JsonResponse
    {
        $equipment = Equipment::query()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Equipment $item) => $item->toApiArray());

        return response()->json($equipment);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
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
                'name' => $validated['name'],
                'category' => $validated['category'],
                'category_label' => $validated['categoryLabel'] ?? 'Custom equipment',
                'badge' => $validated['badge'] ?? 'New',
                'badge_tone' => $validated['badgeTone'] ?? 'new',
                'image' => $image,
                'specs' => $validated['specs'] ?? [],
                'daily_rate' => $validated['dailyRate'],
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

        return response()->json($equipment->toApiArray(), 201);
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
