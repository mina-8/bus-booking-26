<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use Illuminate\Support\Facades\Auth;

class ReportBooking
{
    public function getReportGo()
    {
        $userId = Auth::id();

        $total = BookingItem::whereHas('booking', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('type', 'go_way')
            ->sum('price');

        return $total;
    }
    public function getReportReturn()
    {
        $userId = Auth::id();

        $total = BookingItem::whereHas('booking', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('type', 'return_way')
            ->sum('price');

        return $total;
    }

    public function getReportTotal()
    {
        $userId = Auth::id();

        $total = Booking::where('user_id', $userId)
            ->sum('total_price');

        return $total;
    }

    public function getTicketReport(int $perPage = 15)
    {
        $userId = Auth::id();

        $report = BookingItem::whereHas('booking', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->with(['booking', 'scheduleTrip', 'bus'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $report;
    }
}
