<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\SchedulesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Availability;
use App\Models\Schedule;
use App\Models\TourTitle;
use App\User;
use Carbon\Carbon;
use Auth;
use Validator;
use DateTime;
use DB;

class SmallGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('group.small.index');
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $date = null)
    {
        $validator = Validator::make($request->all(), [
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'user' => 'nullable|exists:users,id'
        ]);
        
        $validator->after(function ($validator) use ($date) {
            if (!$this->validateDate($date) && $date) {
                $validator->errors()->add('date', 'Invalid date.');
            }
        });

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $date_range = array(
            'start' => Carbon::parse($request->start)->format('Y-m-d'),
            'end' => Carbon::parse($request->end)->format('Y-m-d') 
        );

        $date = $date ? Carbon::parse($date) : Carbon::now();
        
        $day = $date->englishDayOfWeek;
        
        $tours_today = TourTitle::with(['info', 'departures.schedule', 'departures' => function($query) use ($date) {
            $query->where('date', $date->format('Y-m-d'));
        }])->withCount(['departures' => function($query) use ($date) {
            $query->where('date', $date->format('Y-m-d'));
        }])->whereHas('availabilities', function($query) use ($day) {
            $query->where('day', $day);
        })->whereHas('info', function($query) {
            $query->whereHas('type', function($query) {
                $query->where('code', 'small');
            });
        })->whereHas('histories')->whereNull('suspended_at')->get();
        
        $availableGuides = Schedule::where([
                'available_at' => $date,
                'flag' => 1
            ])->whereDoesntHave('departure')
            ->with('user.info')
            ->get();

        $availableGuides = $availableGuides->sortByDesc('user.info.rating');

        $availableGuides = $availableGuides->values()->all();

        $date = $date->format('Y-m-d');

        $user = Auth::user();

        $isAdmin = Auth::user()->access_levels()->whereHas('info', function($q) {
            $q->where('code', 'admin');
            })->first();

        $schedules = $isAdmin ? ($request->user) ? User::find($request->user)->schedules()->select('available_at', 'shift', DB::raw('count(*) as count'))->where('available_at', '>=', $date_range['start'])->where('available_at', '<=', $date_range['end'])->groupBy('available_at', 'shift', 'user_id')->get() : Schedule::select('available_at', 'shift', DB::raw('count(*) as count'))->where('available_at', '>=', $date_range['start'])->where('available_at', '<=', $date_range['end'])->groupBy('available_at', 'shift')->get() : Auth::user()->schedules()->select('available_at', 'shift', DB::raw('count(*) as count'))->where('available_at', '>=', $date_range['start'])->where('available_at', '<=', $date_range['end'])->groupBy('available_at', 'shift', 'user_id')->get();

        $startDate = new Carbon($date_range['start']);

        $endDate = new Carbon($date_range['end']);

        $events = array();

        while ($startDate->lte($endDate)){
            $day = $startDate->englishDayOfWeek;

            $tours = TourTitle::with('info')->withCount(['departures' => function($query) use ($startDate) {
                $query->where('date', $startDate->format('Y-m-d'));
            }])->whereHas('availabilities', function($query) use ($day) {
                $query->where('day', $day);
            })->get();

            foreach ($tours as $index => $value) {
                array_push($events, [
                    'id' => $index,
                    'title' => '('.$value->departures_count.') '.$value->info->tour_code,
                    'name' => $value->title,
                    'date' => $startDate->toDateString(),
                    'color' => 'white',
                    'borderColor' => $value->info->color,
                    'textColor' => 'black',
                    'sort' => 3
                ]);
            }

            $startDate->addDay();
        }

        $data = array(
            'schedules' => $events, 
            'selected_date' => [
                'tours_today' => $tours_today,
                'availables' => $availableGuides
            ],
            'date' => $date ? $date : null,
            'isAdmin' => $isAdmin ? true : false,
            'tour_titles' => TourTitle::all()
        );
        
        return response()->json($data);
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
        //
    }

    function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }
}
