<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Mail\TourDepartureSummary;
use App\Mail\NotifyGuideDeparture;
use App\Mail\TourModification;
use App\Mail\ToursInfo;
use App\Exports\SummaryExport;
use App\Exports\TourInfoExport;
use App\Models\TourDeparture;
use Carbon\Carbon;
use App\User;

class NotificationController extends Controller
{
    public function index() {
        return view('notification.index');
    }

    public function modification(Request $request) {
        $start = $request->start ? Carbon::parse($request->start)->format('Y-m-d') : Carbon::now()->subDays(3)->format('Y-m-d');

        $guides = User::with(['schedules.departure.tour', 'schedules' => function($q) use ($start, $request) {
                $q->whereHas('departure', function($q) use ($start, $request) {
                    if($request->start && $request->end) {
                        $start = Carbon::parse($request->start)->format('Y-m-d');
                        $end = Carbon::parse($request->end)->format('Y-m-d');

                        $q->whereDate('date', '>=', $start);
                        $q->whereDate('date', '<=', $end);
                    } else $q->whereDate('date', '>=', $start);
                });
            }])
            ->whereHas('access_levels', function($q) {
                $q->whereHas('info', function($q) {
                    $q->where('code', 'tg');
                });
            })->whereNotNull('accepted_at')->get();

        foreach ($guides as $key => $value) {

            Mail::send((new TourModification($value, $start, $request->start ? false : true)));

        }

        return response()->json($guides);
    }

    public function summary(Request $request, $option) {
        if($option === 'download') {

            $departures = $this->getSummary($request->start, $request->end, $request->guide)->get();

            $filename = Carbon::parse($request->start)->format('F').' Tours Updates.xlsx';
    
            $summaryExcel = Excel::download(new SummaryExport($departures), $filename);
    
            return $summaryExcel;

        } else if($option === 'send'){

            Mail::send((new TourDepartureSummary($request->all())));

            return response()->json('Email sent!');

        }
    }

    public function getSummary($start, $end, $guide = false) {
        $departures = TourDeparture::with('tour.info', 'schedule')->whereDate('date', '>=', $start)
                    ->whereDate('date', '<=', $end);

        if(isset($guide) && $guide === 'true') {
            $departures = $departures->whereNotNull('schedule_id');
        } else {
            $departures = $departures->whereNull('schedule_id');
        }

        return $departures;
    }

    public function sendToursWithoutVoucherCodes(Request $request) {
        $start = Carbon::parse($request->start)->format('Y-m-d');
        $end = Carbon::parse($request->end)->format('Y-m-d');

        $departures = TourDeparture::with('tour.info', 'schedule')->whereDate('date', '>=', $start)
                    ->whereDate('date', '<=', $end)
                    ->where(function($q) {
                        $q->whereDoesntHave('serial_numbers');
                        $q->orWhere('complete_voucher', 0);
                    })
                    ->get();
                    
        Mail::send((new ToursInfo($departures, $request->start)));

        return response()->json($departures);
    }

    public function downloadToursWithoutVoucherCodes(Request $request) {
        $start = Carbon::parse($request->start)->format('Y-m-d');
        $end = Carbon::parse($request->end)->format('Y-m-d');

        $departures = TourDeparture::with('tour.info', 'schedule')->whereDate('date', '>=', $start)
                    ->whereDate('date', '<=', $end)
                    ->where(function($q) {
                        $q->whereDoesntHave('serial_numbers');
                        $q->orWhere('complete_voucher', 0);
                    })
                    ->get();

        $filename = Carbon::parse($request->start)->format('F').' Tours with No or Incomplete Voucher.xlsx';

        $summaryExcel = Excel::download(new TourInfoExport($departures), $filename);

        return $summaryExcel;
    }

    public function notifyGuide(Request $request) {
        $request->validate([
            'departure.id' => 'required|exists:tour_departures,id'
        ]);

        $id = $request->departure['id'];

        $departure = TourDeparture::with('schedule.user', 'tour')->find($id);

        $guide = $departure->schedule->user;

        Mail::to($guide)->send((new NotifyGuideDeparture($departure)));
    }

}
