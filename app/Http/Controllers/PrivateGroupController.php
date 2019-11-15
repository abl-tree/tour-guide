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

class PrivateGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('group.private.index');
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

        $availableGuidesM = $this->getAvailableGuidesByShift('Morning', $date);

        $availableGuidesA = $this->getAvailableGuidesByShift('Afternoon', $date);

        $availableGuidesE = $this->getAvailableGuidesByShift('Evening', $date);
        
        $tours_today = TourTitle::with(['info.type', 'departures.serial_numbers', 'departures.schedule', 'departures' => function($query) use ($date) {
            $query->where('date', $date->format('Y-m-d'));
        }])->withCount(['departures' => function($query) use ($date) {
            $query->where('date', $date->format('Y-m-d'));
        }])->whereHas('availabilities', function($query) use ($day) {
            $query->where('day', $day);
        })->whereHas('info', function($query) {
            $query->whereHas('type', function($query) {
                $query->where('code', 'private');
            });
        })->whereHas('histories')
        ->whereNull('suspended_at')->get();

        foreach ($tours_today as $key => $tour) {
            if($tour->time === 'am') {
                $tour['available'] = $availableGuidesM;
            } else if($tour->time === 'pm') {
                $tour['available'] = $availableGuidesA;
            } else if($tour->time === 'evening') {
                $tour['available'] = $availableGuidesE;
            }
        }

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
            }, 'departures_incomplete' => function($query) use ($startDate) {
                $query->whereDoesntHave('schedule');
                $query->where('date', $startDate->format('Y-m-d'));
            }])->whereHas('availabilities', function($query) use ($day) {
                $query->where('day', $day);
            })->whereHas('info', function($query) {
                $query->whereHas('type', function($query) {
                    $query->where('code', 'private');
                });
            })->whereHas('histories')
            ->whereNull('suspended_at')->get();

            foreach ($tours as $index => $value) {
                array_push($events, [
                    'id' => $index,
                    'title' => '('.$value->departures_count.')'.$value->info->tour_code,
                    'name' => $value->title,
                    'date' => $startDate->toDateString(),
                    'color' => $value->departures_incomplete_count ? 'red' : 'white',
                    // 'borderColor' => $value->info->color,
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
                'availables' => $availableGuidesM
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

    public function getAvailableGuidesByShift($shift, $date) {

        $availableGuides = User::with('info')->whereDoesntHave('schedules', function($q) use ($date, $shift) {
            $q->where(['available_at' => $date, 'flag' => 1, 'shift' => $shift]);
            $q->whereHas('departure');
        })->whereHas('access_levels', function($q) {
            $q->whereHas('info', function($q) {
                $q->where('code', 'tg');
            });
        })->whereNotNull('accepted_at')->get();

        $availableGuides = $availableGuides->sortByDesc('info.rating');

        return $availableGuides->values()->all();

    }
}
