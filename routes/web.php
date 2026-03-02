<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::get('dashboard' , [HomeController::class , 'index'])
    ->name('dashboard');
    Route::post('trips' , [HomeController::class , 'gettrips']);

    // البحث عن الرحلات المتاحة
    Route::post('search-trips', [TripController::class, 'searchTrips'])
        ->name('search-trips');

    // صفحة نتائج البحث
    Route::get('trip-results', [TripController::class, 'showResults'])
        ->name('trip-results');

    // ==================== Booking Routes ====================

    // صفحة اختيار المقاعد
    Route::get('seat-selection', [BookingController::class, 'showSeatSelection'])
        ->name('seat-selection');

    // صفحة اختيار المقاعد للذهاب والعودة
    Route::get('round-trip-seat-selection', [BookingController::class, 'showRoundTripSeatSelection'])
        ->name('round-trip-seat-selection');

    // الحصول على معلومات المقاعد
    Route::post('bookings/bus-seats', [BookingController::class, 'getBusSeats'])
        ->name('bookings.bus-seats');

    // التحقق من توفر المقاعد
    Route::post('bookings/check-seats', [BookingController::class, 'checkSeatsAvailability'])
        ->name('bookings.check-seats');

    // إنشاء حجز جديد
    Route::post('bookings/create', [BookingController::class, 'createBooking'])
        ->name('bookings.create');

    // عرض تذكرة للطباعة
    Route::get('bookings/{id}/print', [BookingController::class, 'printBooking'])
        ->name('bookings.print');

    // تطبيق كود الخصم
    Route::post('bookings/apply-discount', [BookingController::class, 'applyDiscount'])
        ->name('bookings.apply-discount');

    // تأكيد الحجز
    Route::post('bookings/{id}/confirm', [BookingController::class, 'confirmBooking'])
        ->name('bookings.confirm');

    // إلغاء الحجز
    Route::post('bookings/{id}/cancel', [BookingController::class, 'cancelBooking'])
        ->name('bookings.cancel');

    // Export booking report (CSV)
    Route::get('filament/reports/bookings/export', [\App\Http\Controllers\ReportExportController::class, 'export'])
        ->name('filament.reports.bookings.export');

    Route::get('reports/ticket' , [HomeController::class , 'ticketReport'])
    ->name('reports.ticket');
});

require __DIR__ . '/settings.php';
