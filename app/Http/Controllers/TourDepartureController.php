<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TourTitle;
use App\Models\TourDeparture;
use App\Models\Schedule;
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

        if(!$tour->departures_count) {
            $availableGuides = Schedule::where([
                    'available_at' => $request->date,
                    'flag' => 1
                ])->whereDoesntHave('departure')
                ->first();

            $departure->create([
                'schedule_id' => $availableGuides ? $availableGuides->id : null,
                'date' => $request->date
            ]);
        }

        $availableGuides = Schedule::where([
                'available_at' => $request->date,
                'flag' => 1
            ])->whereDoesntHave('departure')
            ->first();

        $departure = $departure->create([
            'schedule_id' => $availableGuides ? $availableGuides->id : null,
            'date' => $request->date
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
}
