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

    public function modification() {
        $previous = Carbon::now()->subDays(3)->format('Y-m-d');

        $guides = User::with(['schedules.departure.tour', 'schedules' => function($q) use ($previous) {
                $q->whereHas('departure', function($q) use ($previous) {
                    $q->whereDate('updated_at', '>=', '2019-09-13');
                });
            }])
            ->whereHas('schedules', function($q) use ($previous) {
                $q->whereHas('departure', function($q) use ($previous) {
                    $q->whereDate('updated_at', '>=', '2019-09-13');
                });
            })->get();

        foreach ($guides as $key => $value) {
            Mail::send((new TourModification($value)));
        }

        return response()->json($guides);
    }
}
