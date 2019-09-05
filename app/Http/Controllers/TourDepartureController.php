<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TourTitle;
use App\Models\TourDeparture;
use App\Models\Schedule;
use App\Models\PaymentType;
use App\User;
use Validator;

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
        
        $availableGuides = Schedule::where([
                'available_at' => $request->date,
                'flag' => 1
            ])->whereDoesntHave('departure')
            ->with('user.info')
            ->get();

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

        $schedule = $user->schedules()->where([
                'available_at' => $departure->date,
                'flag' => 1
            ])->whereDoesntHave('departure')
            ->first();

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
}
