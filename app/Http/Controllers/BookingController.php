<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Booking;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * الحصول على معلومات المقاعد لسيارة في رحلة محددة
     */
    public function getBusSeats(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedule_trip_id' => 'required|exists:schedule_trips,id',
            'bus_id' => 'required|exists:buses,id',
            'from_city' => 'nullable|string',
            'to_city' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->bookingService->getBusSeatsInfo(
            $request->schedule_trip_id,
            $request->bus_id,
            $request->from_city,
            $request->to_city
        );

        return response()->json($result);
    }

    /**
     * التحقق من توفر المقاعد
     */
    public function checkSeatsAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedule_trip_id' => 'required|exists:schedule_trips,id',
            'bus_id' => 'required|exists:buses,id',
            'seat_numbers' => 'required|array|min:1',
            'seat_numbers.*' => 'required|integer|min:1',
            'from_city' => 'nullable|string',
            'to_city' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->bookingService->checkSeatsAvailability(
            $request->schedule_trip_id,
            $request->bus_id,
            $request->seat_numbers,
            $request->from_city,
            $request->to_city
        );

        return response()->json($result);
    }

    /**
     * إنشاء حجز جديد
     */
    public function createBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'subtotal_price' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.schedule_trip_id' => 'required|exists:schedule_trips,id',
            'items.*.bus_id' => 'required|exists:buses,id',
            'items.*.type' => 'required|in:go_way,return_way',
            'items.*.seat_numbers' => 'required|array|min:1',
            'items.*.seat_numbers.*' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.from_city' => 'required|string',
            'items.*.to_city' => 'required|string',
        ], [
            'customer_name.required' => 'يجب إدخال اسم العميل',
            'phone_number.required' => 'يجب إدخال رقم الهاتف',
            'items.required' => 'يجب تحديد عناصر الحجز',
            'items.*.seat_numbers.required' => 'يجب اختيار المقاعد',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->bookingService->createBooking($request->all());

        return response()->json($result);
    }

    /**
     * تطبيق كود خصم
     */
    public function applyDiscount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'discount_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->bookingService->applyDiscountCode(
            $request->booking_id,
            $request->discount_code
        );

        return response()->json($result);
    }

    /**
     * تأكيد الحجز
     */
    public function confirmBooking(Request $request, $id)
    {
        $result = $this->bookingService->confirmBooking($id);
        return response()->json($result);
    }

    /**
     * إلغاء الحجز
     */
    public function cancelBooking(Request $request, $id)
    {
        $result = $this->bookingService->cancelBooking($id);
        return response()->json($result);
    }

    /**
     * عرض تذكرة الحجز للطباعة
     */
    public function printBooking($id)
    {
        $booking = Booking::with('items')->findOrFail($id);
        return view('bookings.ticket', [
            'booking' => $booking,
        ]);
    }

    /**
     * عرض صفحة اختيار المقاعد
     */
    public function showSeatSelection(Request $request)
    {
        return inertia('bookings/SeatSelection', [
            'scheduleTripId' => $request->query('schedule_trip_id'),
            'busId' => $request->query('bus_id'),
            'tripData' => $request->query('trip_data'),
        ]);
    }

    /**
     * عرض صفحة اختيار المقاعد للذهاب والعودة
     */
    public function showRoundTripSeatSelection(Request $request)
    {
        return inertia('bookings/RoundTripSeatSelection', [
            'tripData' => $request->query('trip_data'),
        ]);
    }
}
