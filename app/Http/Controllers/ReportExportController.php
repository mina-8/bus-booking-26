<?php

namespace App\Http\Controllers;

use App\Models\BookingItem;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ReportExportController extends Controller
{
    public function export(Request $request)
    {
        $from = $request->query('from_date');
        $to = $request->query('to_date');
        $type = $request->query('type', 'both');

        $query = BookingItem::query()->with(['booking.user', 'scheduleTrip']);

        if (!empty($from)) {
            $query->whereHas('scheduleTrip', function ($q) use ($from) {
                $q->where('date', '>=', $from);
            });
        }
        if (!empty($to)) {
            $query->whereHas('scheduleTrip', function ($q) use ($to) {
                $q->where('date', '<=', $to);
            });
        }

        if ($type === 'go') {
            $query->where('type', '\\App\\Enums\\BookTypeEnum::GO_WAY');
        } elseif ($type === 'return') {
            $query->where('type', '\\App\\Enums\\BookTypeEnum::RETURN_WAY');
        }

        $items = $query->get();

        $filename = 'booking_report_' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($items) {
            // BOM for Excel to recognize UTF-8
            echo "\xEF\xBB\xBF";

            $out = fopen('php://output', 'w');

            // header
            fputcsv($out, [
                'المستخدم', 'الهاتف', 'حجز رقم', 'تاريخ الرحلة', 'نوع', 'من', 'إلى', 'مقاعد', 'أرقام المقاعد', 'سعر', 'إجمالي الحجز', 'الحالة'
            ]);

            foreach ($items as $item) {
                $user = $item->booking?->user?->name ?? $item->booking?->customer_name ?? '';
                $phone = $item->booking?->phone_number ?? '';
                $bookingId = $item->booking_id;
                $date = $item->scheduleTrip?->date?->toDateString() ?? '';
                $typeVal = $item->type?->label() ?? ($item->type?->value ?? (string)$item->type);
                $fromCity = $item->from_city;
                $toCity = $item->to_city;
                $seats = $item->number_of_seats;
                $seatNumbers = is_array($item->seat_numbers) ? implode(', ', $item->seat_numbers) : ($item->seat_numbers ?? '');
                $price = $item->price;
                $bookingTotal = $item->booking?->total_price ?? '';
                $status = $item->booking?->status ? (\App\Enums\BookingStatus::tryFrom($item->booking->status)?->label() ?? $item->booking->status) : '';

                fputcsv($out, [$user, $phone, $bookingId, $date, $typeVal, $fromCity, $toCity, $seats, $seatNumbers, $price, $bookingTotal, $status]);
            }

            fclose($out);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
