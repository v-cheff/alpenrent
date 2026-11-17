<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Equipment;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $bookings = $request->user()
            ->bookings()
            ->with('equipment.rentalPoint')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Booking $booking) => $booking->toApiArray());

        return response()->json($bookings);
    }

    /**
     * Bookings other people made for equipment at the authenticated user's rental points.
     */
    public function incoming(Request $request): JsonResponse
    {
        $bookings = Booking::query()
            ->whereHas('equipment', fn ($equipment) => $equipment->ownedBy($request->user()))
            ->with(['equipment.rentalPoint', 'user'])
            ->orderBy('start_date')
            ->get()
            ->map(fn (Booking $booking) => $booking->toApiArray());

        return response()->json($bookings);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'equipmentId' => ['required', 'string', 'exists:equipment,id'],
            'size' => ['required', 'string', 'max:100'],
            // Rental days are inclusive: 1–4 Dec is four ski days, 1–1 Dec is one.
            'startDate' => ['required', 'date_format:Y-m-d'],
            'endDate' => ['required', 'date_format:Y-m-d', 'after_or_equal:startDate'],
            'rentalType' => ['sometimes', 'string', Rule::in(Booking::RENTAL_TYPES)],
        ]);

        $equipment = Equipment::query()->findOrFail($validated['equipmentId']);
        $rentalType = $validated['rentalType'] ?? Booking::FULL_DAY;

        $this->assertRentalType($equipment, $rentalType, $validated['startDate'], $validated['endDate']);
        $this->assertBookable(
            $equipment,
            $validated['size'],
            $validated['startDate'],
            $validated['endDate'],
        );

        $booking = $request->user()->bookings()->create([
            'equipment_id' => $equipment->id,
            'size' => $validated['size'],
            'rental_type' => $rentalType,
            'start_date' => $validated['startDate'],
            'end_date' => $validated['endDate'],
        ]);

        return response()->json($booking->load('equipment.rentalPoint')->toApiArray(), 201);
    }

    public function update(Request $request, Booking $booking): JsonResponse
    {
        $this->assertOwned($request, $booking);

        $validated = $request->validate([
            'size' => ['sometimes', 'required', 'string', 'max:100'],
            'startDate' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'endDate' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'rentalType' => ['sometimes', 'string', Rule::in(Booking::RENTAL_TYPES)],
        ]);

        $size = $validated['size'] ?? $booking->size;
        $startDate = $validated['startDate'] ?? $booking->start_date->toDateString();
        $endDate = $validated['endDate'] ?? $booking->end_date->toDateString();
        $rentalType = $validated['rentalType'] ?? $booking->rental_type;

        if ($endDate < $startDate) {
            throw ValidationException::withMessages([
                'endDate' => ['The end date must be on or after the start date.'],
            ]);
        }

        $equipment = $booking->equipment()->firstOrFail();

        $this->assertRentalType($equipment, $rentalType, $startDate, $endDate);
        $this->assertBookable(
            $equipment,
            $size,
            $startDate,
            $endDate,
            $booking->id,
        );

        $booking->update([
            'size' => $size,
            'rental_type' => $rentalType,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return response()->json($booking->fresh('equipment.rentalPoint')->toApiArray());
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

    private function assertRentalType(Equipment $equipment, string $rentalType, string $startDate, string $endDate): void
    {
        if ($rentalType !== Booking::HALF_DAY) {
            return;
        }

        if ($startDate !== $endDate) {
            throw ValidationException::withMessages([
                'rentalType' => ['A half-day rental covers a single date.'],
            ]);
        }

        if ($equipment->half_day_rate === null) {
            throw ValidationException::withMessages([
                'rentalType' => ['This equipment is not offered for half-day rental.'],
            ]);
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
