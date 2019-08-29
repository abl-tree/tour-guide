<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\SchedulesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Schedule;
use App\Models\TourTitle;
use App\User;
use Carbon\Carbon;
use Auth;
use Validator;
use DateTime;
use DB;

class ScheduleController extends Controller
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
        $validatedData = $request->validate([
            'id' => 'required|exists:users|numeric',
            'schedule_at' => 'required|date_format:Y-m-d',
            'shift' => 'required|in:Morning,Afternoon,Evening'
        ]);

        $scheduleExists = User::whereNotNull('accepted_at')
        ->whereHas('access_levels', function($q) {
            $q->whereHas('info', function($q) {
                $q->where('code', '!=', 'admin');
            });
        })
        ->whereHas('schedules', function($q) use ($validatedData) {
            $q->where('shift', $validatedData['shift']);
            $q->where('available_at', $validatedData['schedule_at']);
        })
        ->find($validatedData['id']);

        $request->validate([
            'schedule_at' => function ($attribute, $value, $fail) use ($scheduleExists){
                if ($scheduleExists) {
                    $fail('The '.$attribute.' is already exists.');
                }
            },
        ]);

        $isAdmin = Auth::user()->access_levels()->whereHas('info', function($q) {
            $q->where('code', 'admin');
            })->first();

        $data = [
            'user_id' => $validatedData['id'],
            'available_at' => $validatedData['schedule_at'],
            'shift' => $validatedData['shift'],
        ];

        if($isAdmin) {
            $data['flag'] = 1;
        }

        $schedule = Schedule::create($data);

        $day = Carbon::parse($schedule->available_at)->englishDayOfWeek;

        if(!$schedule->flag) {
        
            $tours_today = TourTitle::whereHas('availabilities', function($query) use ($day) {
                $query->where('day', $day);
            })->where(function($query) use ($schedule) {
                $query->whereHas('departures', function($query) use ($schedule) {
                    $query->where('schedule_id', $schedule->id);
                })->orWhereDoesntHave('departures');
            })->first();

            $departures = $tours_today->departures()->where('schedule_id', $schedule->id)->update([
                    'tour_id' => $tours_today->id,
                    'schedule_id' => null
                ]
            );
    
            return response()->json($schedule);

        }
        
        $tours_today = TourTitle::whereHas('availabilities', function($query) use ($day) {
            $query->where('day', $day);
        })->where(function($query) use ($schedule) {
            $query->whereHas('departures', function($query) use ($schedule) {
                $query->where('date', $schedule->available_at);
                $query->whereNull('schedule_id');
            })->orWhereDoesntHave('departures');
        })->first();

        if(!$tours_today) {
            return response()->json($schedule);
        }

        $departures = $tours_today->departures()->firstOrCreate(
            [
                'tour_id' => $tours_today->id,
                'schedule_id' => null,
                'date' => $schedule->available_at
            ],[
                'tour_id' => $tours_today->id,
                'schedule_id' => $schedule->id,
                'date' => $schedule->available_at
            ]
        );

        if(!$departures->schedule_id && $departures->tour_id) {
            $departures->schedule_id = $schedule->id;
            $departures->save();
        }

        return response()->json($schedule);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $date
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

        $date = $date->format('Y-m-d');

        $user = Auth::user();

        $isAdmin = Auth::user()->access_levels()->whereHas('info', function($q) {
            $q->where('code', 'admin');
            })->first();

        $morning = $this->getSchedules($date, 'Morning', $isAdmin, $request->user ? $request->user : null);

        $afternoon = $this->getSchedules($date, 'Afternoon', $isAdmin, $request->user ? $request->user : null);

        $evening = $this->getSchedules($date, 'Evening', $isAdmin, $request->user ? $request->user : null);

        $schedules = $isAdmin ? ($request->user) ? User::find($request->user)->schedules()->select('available_at', 'shift', DB::raw('count(*) as count'))->where('available_at', '>=', $date_range['start'])->where('available_at', '<=', $date_range['end'])->groupBy('available_at', 'shift', 'user_id')->get() : Schedule::select('available_at', 'shift', DB::raw('count(*) as count'))->where('available_at', '>=', $date_range['start'])->where('available_at', '<=', $date_range['end'])->groupBy('available_at', 'shift')->get() : Auth::user()->schedules()->select('available_at', 'shift', DB::raw('count(*) as count'))->where('available_at', '>=', $date_range['start'])->where('available_at', '<=', $date_range['end'])->groupBy('available_at', 'shift', 'user_id')->get();

        $startDate = new Carbon($date_range['start']);

        $endDate = new Carbon($date_range['end']);

        $events = array();

        while ($startDate->lte($endDate)){
            $shifts = array(
                array(
                    'shift' => 'Morning', 
                    'color' => 'blue',
                    'errorColor' => '#dcf4ff'
                ),
                array(
                    'shift' => 'Afternoon', 
                    'color' => 'red',
                    'errorColor' => '#ffd4d4'
                ),
                array(
                    'shift' => 'Evening', 
                    'color' => 'green',
                    'errorColor' => '#c3ffc7'
                )
            );

            foreach ($shifts as $index => $value) {
                $tmp = $schedules->where('available_at', $startDate->toDateString())->where('shift', $value['shift'])->first();

                array_push($events, [
                    'id' => $index,
                    'title' => $tmp ? $isAdmin && !$request->user ? $tmp['count'].' Guides' : 'Available' : '',
                    'date' => $startDate->toDateString(),
                    'color' => $tmp['count'] ? $value['color'] : $value['errorColor'],
                    'textColor' => $tmp['count'] ? 'white' : 'black',
                    'sort' => 3
                ]);
            }

            $startDate->addDay();
        }

        $pending = $isAdmin ? Schedule::where('available_at', $date)->where('available_at', $date)->where('flag', 0)->count() : $user->schedules()->where('available_at', $date)->where('flag', 0)->count();

        $scheduled = $isAdmin ? Schedule::where('available_at', $date)->where('flag', 1)->count() : $user->schedules()->where('available_at', $date)->where('flag', 1)->count();

        $tour_guides = User::whereNotNull('accepted_at')
                        ->whereHas('access_levels', function($q) {
                            $q->whereHas('info', function($q) {
                                $q->where('code', '!=', 'admin');
                            });
                        });

        $data = array(
            'schedules' => $events, 
            'tour_guides' => array(
                'morning' => array(
                    'available' => $morning ? $morning : array($user),
                    'unavailable' => User::whereNotNull('accepted_at')
                    ->whereHas('access_levels', function($q) {
                        $q->whereHas('info', function($q) {
                            $q->where('code', '!=', 'admin');
                        });
                    })->whereDoesntHave('schedules', function($q) use ($date) {
                        $q->where('shift', 'Morning');
                        $q->where('available_at', $date);
                    })->get()
                ), 
                'afternoon' => array(
                    'available' => $afternoon ? $afternoon : array($user),
                    'unavailable' => User::whereNotNull('accepted_at')
                    ->whereHas('access_levels', function($q) {
                        $q->whereHas('info', function($q) {
                            $q->where('code', '!=', 'admin');
                        });
                    })->whereDoesntHave('schedules', function($q) use ($date) {
                        $q->where('shift', 'Afternoon');
                        $q->where('available_at', $date);
                    })->get()
                ), 
                'evening' => array(
                    'available' => $evening ? $evening : array($user),
                    'unavailable' => User::whereNotNull('accepted_at')
                    ->whereHas('access_levels', function($q) {
                        $q->whereHas('info', function($q) {
                            $q->where('code', '!=', 'admin');
                        });
                    })->whereDoesntHave('schedules', function($q) use ($date) {
                        $q->where('shift', 'Evening');
                        $q->where('available_at', $date);
                    })->get()
                )
            ),
            'date' => $date ? $date : $now,
            'isAdmin' => $isAdmin ? true : false,
            'tour_titles' => TourTitle::all(),
            'lists' => $tour_guides->get()
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
        $isAdmin = Auth::user()->access_levels()->whereHas('info', function($q) {
            $q->where('code', 'admin');
            })->first();

        if(!$isAdmin) {
            $request->validate([
                'tour_title' => 'required|exists:tour_titles,id'
            ]);

            $schedule = Schedule::find($id);

            $schedule->tour_title_id = $request->tour_title;
            
            $schedule->save();

            return response()->json($schedule);
        }
        
        $request->validate([
            'flag' => 'required|numeric|in:0,1'
        ]);

        $schedule = Schedule::find($id);

        $schedule->flag = $request->flag;
        
        $schedule->save();

        $day = Carbon::parse($schedule->available_at)->englishDayOfWeek;

        if(!$schedule->flag) {
        
            $tours_today = TourTitle::whereHas('availabilities', function($query) use ($day) {
                $query->where('day', $day);
            })->where(function($query) use ($schedule) {
                $query->whereHas('departures', function($query) use ($schedule) {
                    $query->where('schedule_id', $schedule->id);
                })->orWhereDoesntHave('departures');
            })->first();

            if($tours_today) {
                
                $availableGuides = Schedule::where([
                    'available_at' => $request->date,
                    'flag' => 1
                ])->whereDoesntHave('departure')
                ->first();

                $departures = $tours_today->departures()->where('schedule_id', $schedule->id)->update([
                        'tour_id' => $tours_today->id,
                        'schedule_id' => $availableGuides ? $availableGuides->id : null
                    ]
                );

            }
    
            return response()->json($schedule);

        }
        
        $tours_today = TourTitle::whereHas('availabilities', function($query) use ($day) {
            $query->where('day', $day);
        })->where(function($query) use ($schedule) {
            $query->whereHas('departures', function($query) use ($schedule) {
                $query->where('date', $schedule->available_at);
                $query->whereDoesntHave('schedule');
            })->orWhereDoesntHave('departures');
        })->whereNull('suspended_at')->first();

        if(!$tours_today) {
            return response()->json($schedule);
        }

        $departures = $tours_today->departures()->firstOrCreate(
            [
                'tour_id' => $tours_today->id,
                'schedule_id' => null,
                'date' => $schedule->available_at
            ],[
                'tour_id' => $tours_today->id,
                'schedule_id' => $schedule->id,
                'date' => $schedule->available_at
            ]
        );

        if(!$departures->schedule_id && $departures->tour_id) {
            $departures->schedule_id = $schedule->id;
            $departures->date = $schedule->available_at;
            $departures->save();
        }

        return response()->json($schedule);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $schedule = Schedule::where(['id' => $id, 'flag' => 0])->first();

        return $schedule ? response()->json(Schedule::destroy($id)) : response()->json(['error' => "You can't un-flagged this schedule. It is already confirmed."], 401);
    }

    public function getSchedules($date, $shift, $isAdmin = false, $user_filter = null) {
        $user = User::with(['schedules' => function($q) use ($date, $shift){
            $q->where('available_at', $date)->where('shift', $shift);
        }])->withCount(['schedules' => function($q) use ($date, $shift){
            $q->where('available_at', $date)->where('shift', $shift);
        }]);

        $user = $isAdmin ? $user_filter ? $user->where('id', $user_filter)->whereHas('schedules', function($q) use ($date, $shift){
            $q->where('available_at', $date)->where('shift', $shift);
        })->get() : $user->whereHas('schedules', function($q) use ($date, $shift){
            $q->where('available_at', $date)->where('shift', $shift);
        })->get() : array($user->where('id', Auth::id())->first()->setAttribute('flag', 0)->setAttribute('schedule_at', $date));

        return $user;
    }

    function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }

    function export(Request $request) {
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);
        $isAdmin = Auth::user()->access_levels()->whereHas('info', function($q) {
            $q->where('code', 'admin');
            })->first();

        if($isAdmin) $user = $request->user ? User::find($request->user) : null;
        else $user = Auth::user();

        return Excel::download(new SchedulesExport($start->format('Y-m-d'), $end->format('Y-m-d'), $user ? $user : null), ($user ? $user->full_name.' ' : '') . 'Schedules ('.$start->englishMonth.' '.$start->year.').csv');
    }

}
