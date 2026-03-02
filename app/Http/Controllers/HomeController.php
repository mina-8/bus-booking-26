<?php

namespace App\Http\Controllers;

use App\Services\ReportBooking;
use App\Services\TripService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    protected TripService $tripService;
    protected ReportBooking $reportBooking;
    public function __construct(
        TripService $tripService,
        ReportBooking $reportBooking
        )
    {
        $this->tripService = $tripService;
        $this->reportBooking = $reportBooking;
    }

    public function index()
    {
        $cities = $this->tripService->cities();
        $reportgo = $this->reportBooking->getReportGo();
        $reportreturn = $this->reportBooking->getReportReturn();
        $reporttotal = $this->reportBooking->getReportTotal();
        return Inertia::render('dashboard' , [
            'cities' => $cities,
            'reportgo' => $reportgo,
            'reportreturn' => $reportreturn,
            'reporttotal' => $reporttotal,
        ]);
    }
    public function gettrips(Request $request)
    {
        $id = $request->input('id');
        $tripscitytwo = $this->tripService->gettrips($id);

        return response()->json($tripscitytwo);
    }

    public function ticketReport()
    {
        $perPage = request()->input('perPage', 15);
        $ticketReport = $this->reportBooking->getTicketReport((int) $perPage);
        return Inertia::render('reports/TicketReport', [
            'ticketReport' => $ticketReport,
        ]);
    }
}
