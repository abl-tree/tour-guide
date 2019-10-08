<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Exports\SummaryExport;
use App\Mail\TourModification;
use App\Models\TourDeparture;
use Carbon\Carbon;
use App\User;

class NotificationController extends Controller
{
    public function index() {
        return view('notification.index');
    }

    public function modification(Request $request) {
        $start = Carbon::now()->subDays(3)->format('Y-m-d');

        $guides = User::with(['schedules.departure.tour', 'schedules' => function($q) use ($start, $request) {
                $q->whereHas('departure', function($q) use ($start, $request) {
                    if($request->start && $request->end) {
                        $start = Carbon::parse($request->start)->format('Y-m-d');
                        $end = Carbon::parse($request->end)->format('Y-m-d');

                        $q->whereDate('updated_at', '>=', $start);
                        $q->whereDate('updated_at', '<=', $end);
                    } else $q->whereDate('updated_at', '>=', $start);
                });
            }])
            ->whereHas('access_levels', function($q) {
                $q->whereHas('info', function($q) {
                    $q->where('code', 'tg');
                });
            })->whereNotNull('accepted_at')->get();

        foreach ($guides as $key => $value) {
            Mail::send((new TourModification($value, $start)));
        }

        return response()->json($guides);
    }

    public function summaryDownload(Request $request) {
        $departures = TourDeparture::with('tour.info', 'schedule')->whereDate('date', '>=', $request->start)
                    ->whereDate('date', '<=', $request->end);

        if(isset($request->guide) && $request->guide) {
            $departures = $departures->whereNotNull('schedule_id');
        } else {
            $departures = $departures->whereNull('schedule_id');
        }

        $departures = $departures->get();

        // if($option === 'download') {
            return Excel::download(new SummaryExport($departures), 'invoices.xlsx');
        // } else {

        // }
    }
}
