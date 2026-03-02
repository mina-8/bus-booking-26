<?php

namespace App\Http\Controllers;

use App\Services\TripService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TripController extends Controller
{
    protected TripService $tripService;

    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

    /**
     * البحث عن الرحلات المتاحة
     */
    public function searchTrips(Request $request)
    {
        // التحقق من صحة البيانات المدخلة
        $validator = Validator::make($request->all(), [
            'from_city' => 'required|exists:cities,id',
            'to_city' => 'required|exists:cities,id|different:from_city',
            'trip_date' => 'required|date|after_or_equal:today',
            'trip_type' => 'required|in:go,return,goreturn',
            'passenger_count' => 'required|integer|min:1|max:8',
            'return_date' => 'required_if:trip_type,goreturn|nullable|date|after_or_equal:trip_date',
        ], [
            'from_city.required' => 'يجب اختيار مدينة المغادرة',
            'to_city.required' => 'يجب اختيار مدينة الوصول',
            'to_city.different' => 'مدينة الوصول يجب أن تكون مختلفة عن مدينة المغادرة',
            'trip_date.required' => 'يجب اختيار تاريخ السفر',
            'trip_date.after_or_equal' => 'تاريخ السفر يجب أن يكون اليوم أو تاريخ مستقبلي',
            'trip_type.required' => 'يجب اختيار نوع الرحلة',
            'passenger_count.required' => 'يجب تحديد عدد المسافرين',
            'passenger_count.min' => 'الحد الأدنى للمسافرين هو 1',
            'passenger_count.max' => 'الحد الأقصى للمسافرين هو 8',
            'return_date.required_if' => 'يجب اختيار تاريخ العودة لرحلة ذهاب وعودة',
            'return_date.after_or_equal' => 'تاريخ العودة يجب أن يكون بعد تاريخ الذهاب',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // البحث عن الرحلات المتاحة
        $result = $this->tripService->searchAvailableTrips($request->all());

        return response()->json($result);
    }

    /**
     * عرض صفحة نتائج البحث
     */
    public function showResults(Request $request)
    {
        return inertia('trips/TripResults', [
            'searchData' => $request->query('searchData'),
            'results' => $request->query('results'),
        ]);
    }
}
