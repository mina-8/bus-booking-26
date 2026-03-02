<?php

namespace App\Filament\Pages;

use App\Enums\BookTypeEnum;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingItem;
use Carbon\Carbon;
use Filament\Pages\Page;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
class ReportBooking extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartBar;

    protected static ?string $navigationLabel = 'تقرير الحجوزات';

    protected static ?string $title = 'تقرير الحجوزات';
    protected string $view = 'filament.pages.report-booking';

    // Filter inputs
    public $from_date;
    public $to_date;
    public $type = 'both'; // 'go', 'return', 'both'

    // Results
    public $results = [];
    public $total_seats = 0;
    public $statusUpdates = []; // [booking_id => selected_status]
    public $showBookingModal = false;
    public $selectedBooking = null; // array or null

    public function mount(): void
    {
        $this->from_date = now()->startOfMonth()->toDateString();
        $this->to_date = now()->toDateString();
    }

    public function generate(): void
    {
        $query = BookingItem::query()->with(['booking.user', 'scheduleTrip', 'bus'])->orderBy('created_at', 'desc');

        if (!empty($this->from_date)) {
            $from = Carbon::parse($this->from_date)->startOfDay()->toDateString();
            $query->whereHas('scheduleTrip', function ($q) use ($from) {
                $q->where('date', '>=', $from);
            });
        }

        if (!empty($this->to_date)) {
            $to = Carbon::parse($this->to_date)->endOfDay()->toDateString();
            $query->whereHas('scheduleTrip', function ($q) use ($to) {
                $q->where('date', '<=', $to);
            });
        }

        if ($this->type === 'go') {
            $query->where('type', BookTypeEnum::GO_WAY);
        } elseif ($this->type === 'return') {
            $query->where('type', BookTypeEnum::RETURN_WAY);
        }

        $items = $query->get();

        $this->total_seats = $items->sum('number_of_seats');

        // prepare status updates defaults
        $this->statusUpdates = [];
        foreach ($items as $item) {
            if ($item->booking) {
                $this->statusUpdates[$item->booking->id] = $item->booking->status;
            }
        }

        $this->results = $items->map(function (BookingItem $item) {
            return [
                'user_name' => $item->booking?->user?->name ?? $item->booking?->customer_name ?? '-',
                'booking_id' => $item->booking_id,
                'date' => $item->scheduleTrip?->date?->toDateString(),
                'type' => $item->type?->label() ?? ($item->type?->value ?? (string) $item->type),
                'from_city' => $item->from_city,
                'to_city' => $item->to_city,
                'seats' => $item->number_of_seats,
                'seat_numbers' => is_array($item->seat_numbers) ? implode(', ', $item->seat_numbers) : ($item->seat_numbers ?? ''),
                'price' => $item->price,
                'booking_total' => $item->booking?->total_price,
                'current_status' => $item->booking ? (\App\Enums\BookingStatus::tryFrom($item->booking->status)?->label() ?? $item->booking->status) : null,
            ];
        })->toArray();
    }

    public function updateBookingStatus(int $bookingId): void
    {
        $status = $this->statusUpdates[$bookingId] ?? null;

        if (!$status) {
            return;
        }

        $valid = array_map(fn($c) => $c->value, BookingStatus::cases());
        if (!in_array($status, $valid, true)) {
            return;
        }

        $booking = Booking::find($bookingId);
        if (!$booking) {
            return;
        }

        $booking->status = $status;
        $booking->save();

        // refresh results to reflect change
        $this->generate();
    }

    public function openBooking(int $bookingId): void
    {
        $booking = Booking::with(['user', 'items.scheduleTrip', 'items.bus'])->find($bookingId);
        if (!$booking) {
            $this->selectedBooking = null;
            $this->showBookingModal = false;
            return;
        }

        $this->selectedBooking = [
            'id' => $booking->id,
            'customer_name' =>  $booking->customer_name ?? $booking->user?->name,
            'phone' => $booking->phone_number,
            'status' => $booking->status,
            'total' => $booking->total_price,
            'items' => $booking->items->map(fn($it) => [
                'date' => $it->scheduleTrip?->date?->toDateString(),
                'type' => $it->type?->value ?? (string)$it->type,
                'from' => $it->from_city,
                'to' => $it->to_city,
                'seats' => $it->number_of_seats,
                'seat_numbers' => is_array($it->seat_numbers) ? implode(', ', $it->seat_numbers) : ($it->seat_numbers ?? ''),
                'price' => $it->price,
                'bus' => $it->bus?->name ?? null,
            ])->toArray(),
        ];

        $this->showBookingModal = true;
    }

    public function closeBooking(): void
    {
        $this->showBookingModal = false;
        $this->selectedBooking = null;
    }
}
