<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TourModification;
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
                        $start = Carbon::parse($request->start)->addDay()->format('Y-m-d');
                        $end = Carbon::parse($request->end)->addDay()->format('Y-m-d');

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
}
