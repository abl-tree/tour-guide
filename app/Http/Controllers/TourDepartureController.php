<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TourDepartureExport;
use App\Models\TourTitle;
use App\Models\TourDeparture;
use App\Models\Schedule;
use App\Models\PaymentType;
use Carbon\Carbon;
use App\User;
use Validator;
use Auth;

class TourDepartureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:tour_titles',
            'date' => 'required|date|date_format:Y-m-d'
        ])->validate();
        
        $tour = TourTitle::withCount(['departures' => function($query) use ($request) {
                    $query->where('date', $request->date);
                }])->find($request->id);

        $departure = $tour->departures();

        $payment_type = PaymentType::first();

        $departure = $departure->create([
            'date' => $request->date,
            'info_id' => $tour->other_info->id,
            'payment_type_id' => $payment_type->id
        ]);

        return $departure;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $departure = TourDeparture::find($id);

        $departure->delete();

        return response()->json('Successully deleted a departure');
    }

    public function autoAssignment(Request $request) {
        $request->validate([
            'id' => 'required|exists:tour_departures,id'
        ]);

        $time = TourDeparture::with('tour')->find($request->id)->tour->time;

        if($time === 'am') $time = 'Morning';
        else if($time === 'pm') $time = 'Afternoon';
        else if($time === 'evening') $time = 'Evening';
        
        $availableGuides = Schedule::where([
                'available_at' => $request->date,
                'flag' => 1,
                'shift' => $time
            ])->whereDoesntHave('departure')
            ->with('user.info')
            ->get();
        
        if(!$availableGuides->count()) return response()->json(['error' => 'No available guides in '.$time.' shift'], 500);

        $sorted = $availableGuides->sortByDesc('user.info.rating');

        $sorted = $sorted->values()->first();

        $departure = TourDeparture::with(['tour.histories', 'tour' => function($q) use ($sorted) {
            $q->with(['histories' => function($q) use ($sorted) {
                $q->with(['tour_rates' => function($q) use ($sorted) {
                    $q->where('payment_type_id', $sorted->payment_type_id);
                }])->first();
            }])->whereHas('histories')->first();
        }])->find($request->id);

        $departure->schedule_id = $sorted ? $sorted->id : null;

        $departure->tour_rate_id = $departure && $departure->tour && $departure->tour->histories->first() && $departure->tour->histories->first()->tour_rates->first() ? $departure->tour->histories->first()->tour_rates->first()->id : null;
        
        $departure->save();

        return response()->json($departure);
    }

    public function manualAssignment(Request $request) {
        $request->validate([
            'id' => 'required|exists:tour_departures,id',
            'guide' => 'required|exists:users,id'
        ]);

        $user = User::with('info')->find($request->guide);

        $departure = TourDeparture::with(['tour.histories', 'tour' => function($q) use ($user) {
            $q->with(['histories' => function($q) use ($user) {
                $q->with(['tour_rates' => function($q) use ($user) {
                    $q->where('payment_type_id', $user->info->payment_type_id);
                }])->first();
            }])->whereHas('histories')->first();
        }])->find($request->id);

        $time = $departure->tour->time;

        if($time === 'am') $time = 'Morning';
        else if($time === 'pm') $time = 'Afternoon';
        else if($time === 'evening') $time = 'Evening';

        $schedule = Schedule::where('user_id', $request->guide)->where('available_at', $departure->date)->where('shift', $time);

        if($schedule->first()) {
            $schedule = $schedule->whereDoesntHave('departure')
                ->first();

            if(!$schedule) return response()->json(['error' => 'Schedule already assigned'], 500);

            $schedule->flag = 1;
            $schedule->save();
        } else {
            $schedule = new Schedule;
            $schedule->tour_title_id = $departure->tour->id;
            $schedule->user_id = $request->guide;
            $schedule->available_at = $departure->date;
            $schedule->shift = $time;
            $schedule->flag = 1;
            $schedule->save();
        }

        $payment_type_id = $departure && $departure->tour && $departure->tour->histories->first() && $departure->tour->histories->first()->tour_rates->first() ? $departure->tour->histories->first()->tour_rates->first()->id : null;

        if(!$payment_type_id) {
            $default_payment_type = PaymentType::first();

            $payment_type_id = TourDeparture::find($request->id)->tour()->first()
                        ->histories()->first()->tour_rates()->where('payment_type_id', $default_payment_type->id)->first()->id;
        }

        $departure->schedule_id = $schedule->id;

        $departure->tour_rate_id = $payment_type_id;
        
        $departure->save();

        return response()->json($departure);
    }

    public function serialNumberAssignment(Request $request) {
        $request->validate([
            'id' => 'required|numeric|exists:tour_departures',
            'serial_number' => 'max:30'
        ]);

        $tour_departure = TourDeparture::find($request->id);
        $tour_departure->serial_number = $request->serial_number;
        $tour_departure->save();

        return response()->json($tour_departure);
    }

    public function export(Request $request) {
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);
        $isAdmin = Auth::user()->access_levels()->whereHas('info', function($q) {
            $q->where('code', 'admin');
            })->first();

        if(!$isAdmin) {
            return response()->json('Access denied', 505);
        }
        
        $tour_departure = TourDeparture::with('tour', 'schedule.user')->whereDate('date', '>=', $start)->whereDate('date', '<=', $end)->get();

        return Excel::download(new TourDepartureExport($tour_departure), 'Tour Departure ('.$start->englishMonth.' '.$start->year.').csv');
    }

    public function payment_method(Request $request) {
        return $request->all();
    }
}
