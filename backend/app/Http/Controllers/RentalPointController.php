<?php

namespace App\Http\Controllers;

use App\Models\RentalPoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Rental points (shops / pickup spots) owned by the authenticated user.
 */
class RentalPointController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $points = $request->user()
            ->rentalPoints()
            ->withCount('equipment')
            ->orderBy('name')
            ->get()
            ->map(fn (RentalPoint $point) => $point->toApiArray());

        return response()->json($points);
    }

    public function store(Request $request): JsonResponse
    {
        $point = $request->user()->rentalPoints()->create($this->validated($request));

        return response()->json($point->loadCount('equipment')->toApiArray(), 201);
    }

    public function update(Request $request, RentalPoint $rentalPoint): JsonResponse
    {
        $this->assertOwned($request, $rentalPoint);

        $rentalPoint->update($this->validated($request, partial: true));

        return response()->json($rentalPoint->loadCount('equipment')->toApiArray());
    }

    public function destroy(Request $request, RentalPoint $rentalPoint): JsonResponse
    {
        $this->assertOwned($request, $rentalPoint);

        if ($rentalPoint->equipment()->exists()) {
            throw ValidationException::withMessages([
                'rentalPoint' => ['Move or delete the equipment at this rental point before deleting it.'],
            ]);
        }

        $rentalPoint->delete();

        return response()->json([
            'message' => 'Rental point deleted successfully.',
        ]);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $required = $partial ? ['sometimes', 'required'] : ['required'];

        $validated = $request->validate([
            'name' => [...$required, 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'openingHours' => ['nullable', 'string', 'max:255'],
        ]);

        $data = [];
        if (array_key_exists('name', $validated)) {
            $data['name'] = trim($validated['name']);
        }
        if (array_key_exists('address', $validated)) {
            $data['address'] = $validated['address'];
        }
        if (array_key_exists('openingHours', $validated)) {
            $data['opening_hours'] = $validated['openingHours'];
        }

        return $data;
    }

    private function assertOwned(Request $request, RentalPoint $rentalPoint): void
    {
        if ($rentalPoint->user_id !== $request->user()->id) {
            abort(404);
        }
    }
}
