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

        $availableGuidesM = $this->getAvailableGuidesByShift('Morning', $date);
        $vacantGuidesM = $this->getVacantGuidesByShift('Morning', $date);

        $availableGuidesA = $this->getAvailableGuidesByShift('Afternoon', $date);
        $vacantGuidesA = $this->getVacantGuidesByShift('Afternoon', $date);

        $availableGuidesE = $this->getAvailableGuidesByShift('Evening', $date);
        $vacantGuidesE = $this->getVacantGuidesByShift('Evening', $date);
        
        $tours_today = TourTitle::with(['info.type', 'departures.serial_numbers', 'departures.schedule', 
        'departures' => function($query) use ($date, $request) {

            if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'with_guide' && isset($request->voucher_filter) && $request->voucher_filter === 'incomplete') {
                $query->whereHas('schedule');
                $query->where('complete_voucher', 0);
            } else if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'with_guide' && !isset($request->voucher_filter)) {
                $query->whereHas('schedule');
            } else if(!isset($request->departure_guide_filter) && isset($request->voucher_filter) && $request->voucher_filter === 'incomplete') {
                $query->where('complete_voucher', 0);
            } else if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'without_guide' && isset($request->voucher_filter) && $request->voucher_filter === 'incomplete') {
                $query->whereDoesntHave('schedule');
                $query->where('complete_voucher', 0);
            } else if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'without_guide' && !isset($request->voucher_filter)) {
                $query->whereDoesntHave('schedule');
            }

            $query->where('date', $date->format('Y-m-d'));

        }])->withCount(['departures' => function($query) use ($date, $request) {

            if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'with_guide' && isset($request->voucher_filter) && $request->voucher_filter === 'incomplete') {
                $query->whereHas('schedule');
                $query->where('complete_voucher', 0);
            } else if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'with_guide' && !isset($request->voucher_filter)) {
                $query->whereHas('schedule');
            } else if(!isset($request->departure_guide_filter) && isset($request->voucher_filter) && $request->voucher_filter === 'incomplete') {
                $query->where('complete_voucher', 0);
            } else if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'without_guide' && isset($request->voucher_filter) && $request->voucher_filter === 'incomplete') {
                $query->whereDoesntHave('schedule');
                $query->where('complete_voucher', 0);
            } else if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'without_guide' && !isset($request->voucher_filter)) {
                $query->whereDoesntHave('schedule');
            }

            $query->where('date', $date->format('Y-m-d'));

        }])->whereHas('availabilities', function($query) use ($day) {
            $query->where('day', $day);
        })->whereHas('info', function($query) {
            $query->whereHas('type', function($query) {
                $query->where('code', 'small');
            });
        })->whereHas('histories')->whereNull('suspended_at');

        if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'with_guide' && isset($request->voucher_filter) && $request->voucher_filter === 'incomplete') {
            $tours_today = $tours_today->whereHas('departures', function($query) use ($date) {
                $query->whereHas('schedule');
                $query->where('complete_voucher', 0);
                $query->where('date', $date->copy()->format('Y-m-d'));
            });
        } else if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'with_guide' && !isset($request->voucher_filter)) {
            $tours_today = $tours_today->whereHas('departures', function($query) use ($date) {
                $query->whereHas('schedule');
                $query->where('date', $date->copy()->format('Y-m-d'));
            });
        } else if(!isset($request->departure_guide_filter) && isset($request->voucher_filter) && $request->voucher_filter === 'incomplete') {
            $tours_today = $tours_today->whereHas('departures', function($query) use ($date) {
                $query->where('complete_voucher', 0);
                $query->where('date', $date->copy()->format('Y-m-d'));
            });
        } else if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'without_guide' && isset($request->voucher_filter) && $request->voucher_filter === 'incomplete') {
            $tours_today = $tours_today->whereHas('departures', function($query) use ($date) {
                $query->whereDoesntHave('schedule');
                $query->where('complete_voucher', 0);
                $query->where('date', $date->copy()->format('Y-m-d'));
            });
        } else if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'without_guide' && !isset($request->voucher_filter)) {
            $tours_today = $tours_today->whereHas('departures', function($query) use ($date) {
                $query->whereDoesntHave('schedule');
                $query->where('date', $date->copy()->format('Y-m-d'));
            });
        }

        $tours_today = $tours_today->get();

        foreach ($tours_today as $key => $tour) {
            if($tour->time === 'am') {
                $tour['available'] = $availableGuidesM;
                $tour['vacant'] = $vacantGuidesM;
            } else if($tour->time === 'pm') {
                $tour['available'] = $availableGuidesA;
                $tour['vacant'] = $vacantGuidesA;
            } else if($tour->time === 'evening') {
                $tour['available'] = $availableGuidesE;
                $tour['vacant'] = $vacantGuidesE;
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

            $tours = TourTitle::with('info')->withCount(['departures' => function($query) use ($startDate, $request) {

                if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'with_guide' && isset($request->voucher_filter) && $request->voucher_filter === 'incomplete') {
                    $query->whereHas('schedule');
                    $query->where('complete_voucher', 0);
                } else if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'with_guide' && !isset($request->voucher_filter)) {
                    $query->whereHas('schedule');
                } else if(!isset($request->departure_guide_filter) && isset($request->voucher_filter) && $request->voucher_filter === 'incomplete') {
                    $query->where('complete_voucher', 0);
                } else if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'without_guide' && isset($request->voucher_filter) && $request->voucher_filter === 'incomplete') {
                    $query->whereDoesntHave('schedule');
                    $query->where('complete_voucher', 0);
                } else if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'without_guide' && !isset($request->voucher_filter)) {
                    $query->whereDoesntHave('schedule');
                }

                $query->where('date', $startDate->format('Y-m-d'));

            }])
            ->when($request->departure_guide_filter !== 'with_guide', function($query) use ($startDate, $request) {
                $query->withCount(['departures_incomplete' => function($query) use ($startDate, $request) {

                    $query->whereDoesntHave('schedule');
                    $query->where('date', $startDate->format('Y-m-d'));

                }]);
            })
            ->whereHas('availabilities', function($query) use ($day) {
                $query->where('day', $day);
            })->whereHas('info', function($query) {
                $query->whereHas('type', function($query) {
                    $query->where('code', 'small');
                });
            })->whereHas('histories')
            ->whereNull('suspended_at');

            if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'with_guide' && isset($request->voucher_filter) && $request->voucher_filter === 'incomplete') {
                $tours = $tours->whereHas('departures', function($query) use ($startDate) {
                    $query->whereHas('schedule');
                    $query->where('complete_voucher', 0);
                    $query->where('date', $startDate->copy()->format('Y-m-d'));
                });
            } else if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'with_guide' && !isset($request->voucher_filter)) {
                $tours = $tours->whereHas('departures', function($query) use ($startDate) {
                    $query->whereHas('schedule');
                    $query->where('date', $startDate->copy()->format('Y-m-d'));
                });
            } else if(!isset($request->departure_guide_filter) && isset($request->voucher_filter) && $request->voucher_filter === 'incomplete') {
                $tours = $tours->whereHas('departures', function($query) use ($startDate) {
                    $query->where('complete_voucher', 0);
                    $query->where('date', $startDate->copy()->format('Y-m-d'));
                });
            } else if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'without_guide' && isset($request->voucher_filter) && $request->voucher_filter === 'incomplete') {
                $tours = $tours->whereHas('departures', function($query) use ($startDate) {
                    $query->whereDoesntHave('schedule');
                    $query->where('complete_voucher', 0);
                    $query->where('date', $startDate->copy()->format('Y-m-d'));
                });
            } else if(isset($request->departure_guide_filter) && $request->departure_guide_filter === 'without_guide' && !isset($request->voucher_filter)) {
                $tours = $tours->whereHas('departures', function($query) use ($startDate) {
                    $query->whereDoesntHave('schedule');
                    $query->where('date', $startDate->copy()->format('Y-m-d'));
                });
            }

            $tours = $tours->get();

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

    public function getVacantGuidesByShift($shift, $date) {

        $availableGuides = User::selectRaw('users.*, user_infos.rating, CONCAT(user_infos.last_name, ", ", user_infos.first_name) as raw_full_name')
        ->with('info')
        ->whereDoesntHave('schedules', function($q) use ($date, $shift) {
            $q->where(['available_at' => $date, 'flag' => 1, 'shift' => $shift]);
            $q->whereHas('departure');
        })
        ->whereHas('access_levels', function($q) {
            $q->whereHas('info', function($q) {
                $q->where('code', 'tg');
            });
        })
        ->whereNotNull('accepted_at')
        ->join('user_infos', 'user_infos.id', '=', 'users.user_info_id')
        ->orderBy('user_infos.rating', 'desc')
        ->orderBy('raw_full_name', 'asc')
        ->get();

        return $availableGuides->values()->all();

    }

    public function getAvailableGuidesByShift($shift, $date) {

        $availableGuides = User::selectRaw('users.*, user_infos.rating, CONCAT(user_infos.last_name, ", ", user_infos.first_name) as raw_full_name')
        ->with('info')
        ->whereHas('schedules', function($q) use ($date, $shift) {
            $q->where(['available_at' => $date, 'flag' => 0, 'shift' => $shift]);
            $q->whereDoesntHave('departure');
        })
        ->whereHas('access_levels', function($q) {
            $q->whereHas('info', function($q) {
                $q->where('code', 'tg');
            });
        })
        ->whereNotNull('accepted_at')
        ->join('user_infos', 'user_infos.id', '=', 'users.user_info_id')
        ->orderBy('user_infos.rating', 'desc')
        ->orderBy('raw_full_name', 'asc')
        ->get();

        return $availableGuides->values()->all();

    }
}
