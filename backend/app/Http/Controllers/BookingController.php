<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Equipment;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $bookings = $request->user()
            ->bookings()
            ->with('equipment')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Booking $booking) => $booking->toApiArray());

        return response()->json($bookings);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'equipmentId' => ['required', 'string', 'exists:equipment,id'],
            'size' => ['required', 'string', 'max:100'],
            'startDate' => ['required', 'date_format:Y-m-d'],
            'endDate' => ['required', 'date_format:Y-m-d', 'after:startDate'],
        ]);

        $equipment = Equipment::query()->findOrFail($validated['equipmentId']);

        $this->assertBookable(
            $equipment,
            $validated['size'],
            $validated['startDate'],
            $validated['endDate'],
        );

        $booking = $request->user()->bookings()->create([
            'equipment_id' => $equipment->id,
            'size' => $validated['size'],
            'start_date' => $validated['startDate'],
            'end_date' => $validated['endDate'],
        ]);

        return response()->json($booking->load('equipment')->toApiArray(), 201);
    }

    public function update(Request $request, Booking $booking): JsonResponse
    {
        $this->assertOwned($request, $booking);

        $validated = $request->validate([
            'size' => ['sometimes', 'required', 'string', 'max:100'],
            'startDate' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'endDate' => ['sometimes', 'required', 'date_format:Y-m-d'],
        ]);

        $size = $validated['size'] ?? $booking->size;
        $startDate = $validated['startDate'] ?? $booking->start_date->toDateString();
        $endDate = $validated['endDate'] ?? $booking->end_date->toDateString();

        if ($endDate <= $startDate) {
            throw ValidationException::withMessages([
                'endDate' => ['The end date must be after the start date.'],
            ]);
        }

        $this->assertBookable(
            $booking->equipment()->firstOrFail(),
            $size,
            $startDate,
            $endDate,
            $booking->id,
        );

        $booking->update([
            'size' => $size,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return response()->json($booking->fresh('equipment')->toApiArray());
    }

    public function destroy(Request $request, Booking $booking): JsonResponse
    {
        $this->assertOwned($request, $booking);

        $booking->delete();

        return response()->json([
            'message' => 'Booking cancelled successfully.',
        ]);
    }

    private function assertOwned(Request $request, Booking $booking): void
    {
        if ($booking->user_id !== $request->user()->id) {
            abort(404);
        }
    }

    private function assertBookable(
        Equipment $equipment,
        string $size,
        string $startDate,
        string $endDate,
        ?int $ignoreBookingId = null,
    ): void {
        $sizes = $equipment->sizes ?? [];

        if (! in_array($size, $sizes, true)) {
            throw ValidationException::withMessages([
                'size' => ['The selected size is not available for this equipment.'],
            ]);
        }

        if (! $this->hasStockForPeriod($equipment, $startDate, $endDate)) {
            throw ValidationException::withMessages([
                'startDate' => ['This equipment is not available for the selected dates.'],
            ]);
        }

        $overlapExists = Booking::query()
            ->where('equipment_id', $equipment->id)
            ->where('size', $size)
            ->when($ignoreBookingId, fn ($query) => $query->whereKeyNot($ignoreBookingId))
            ->overlapping($startDate, $endDate)
            ->exists();

        if ($overlapExists) {
            throw ValidationException::withMessages([
                'startDate' => ['This size is already booked for the selected dates.'],
            ]);
        }
    }

    private function hasStockForPeriod(Equipment $equipment, string $startDate, string $endDate): bool
    {
        if ($equipment->available_now) {
            return true;
        }

        $availableDates = $equipment->available_dates ?? [];

        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            if (! in_array($date->toDateString(), $availableDates, true)) {
                return false;
            }
        }

        return true;
    }
};
