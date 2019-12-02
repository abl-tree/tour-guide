<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DepartureExport;
use App\Models\TourTitle;
use App\Models\Schedule;
use App\Models\PaymentType;
use App\User;
use Carbon\Carbon;
use Auth;

class StatisticsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $current_month = Carbon::now()->format('Y-m');
        $tours = TourTitle::all();
        $payments = PaymentType::all();
        $isAdmin = Auth::user()->access_levels()->whereHas('info', function($q) {
            $q->where('code', 'admin');
            })->first();

        if($isAdmin) {
            $guides =  User::whereHas('access_levels', function($q) {
                $q->whereHas('info', function($q) {
                    $q->where('code', 'tg');
                });
            })->whereNotNull('accepted_at')
            ->get();
        } else {
            $guides =  Auth::user();
        }

        return view('statistics.index', compact('current_month', 'tours', 'guides', 'payments'));
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
        //
    }

    public function guidestats(Request $request, $filter) {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::now();

        $month = $request->date ? Carbon::parse($request->date)->format('m') : $date->format('m');
        $year = $request->date ? Carbon::parse($request->date)->format('Y') : $date->format('Y');
        $week = [
            'start' => $request->date ? Carbon::parse($request->date)->startOfWeek() : $date->startOfWeek(),
            'end' => $request->date ? Carbon::parse($request->date)->endOfWeek() : $date->endOfWeek()
        ];
        $daily = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : $date->format('Y-m-d');

        $isAdmin = Auth::user()->access_levels()->whereHas('info', function($q) {
            $q->where('code', 'admin');
            })->first();

        if($isAdmin) {
            $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);

            $user = $request->user_id;
        } else {
            $user = Auth::id();
        }

        $statistics = array();

        $count_data = 0;
        $grand_rate_total = 0;
        $grand_payment_total = 0;

        $tours = TourTitle::with(['departures' => function($q) use ($month, $year, $week, $daily, $filter, $user) {
            if($filter === 'monthly') {
                $q->whereMonth('date', $month);
                $q->whereYear('date', $year);
            } else if($filter === 'yearly') {
                $q->whereYear('date', $year);
            } else if($filter === 'weekly') {
                $q->whereDate('date', '>=', $week['start']);
                $q->whereDate('date', '<=', $week['end']);
            } else if($filter === 'daily') {
                $q->whereDate('date', $daily);
            }

            //edit
            $q->with(['schedule' => function($q) use ($month, $year, $week, $daily, $filter, $user) {
                $q->with(['user' => function($q) use ($month, $year, $week, $daily, $filter, $user) {
                    $q->with(['receipts' => function($q) use ($month, $year, $week, $daily, $filter) {
                        if($filter === 'monthly') {
                            $q->whereMonth('event_date', $month);
                            $q->whereYear('event_date', $year);
                        } else if($filter === 'yearly') {
                            $q->whereYear('event_date', $year);
                        } else if($filter === 'weekly') {
                            $q->whereDate('event_date', '>=', $week['start']);
                            $q->whereDate('event_date', '<=', $week['end']);
                        } else if($filter === 'daily') {
                            $q->whereDate('event_date', $daily);
                        }
                    }]);
                }]);
            }]);
            //end edit

            $q->whereHas('schedule', function($q) use ($month, $year, $week, $daily, $filter, $user) {
                $q->whereHas('user', function($q) use ($month, $year, $week, $daily, $filter, $user) {
                    $q->where('user_id', $user);
                    $q->whereHas('receipts', function($q) use ($month, $year, $week, $daily, $filter) {
                        if($filter === 'monthly') {
                            $q->whereMonth('event_date', $month);
                            $q->whereYear('event_date', $year);
                        } else if($filter === 'yearly') {
                            $q->whereYear('event_date', $year);
                        } else if($filter === 'weekly') {
                            $q->whereDate('event_date', '>=', $week['start']);
                            $q->whereDate('event_date', '<=', $week['end']);
                        } else if($filter === 'daily') {
                            $q->whereDate('event_date', $daily);
                        }
                    });
                });
            });
        }])->whereHas('departures', function($q) use ($month, $year, $week, $daily, $filter, $user) {
            if($filter === 'monthly') {
                $q->whereMonth('date', $month);
                $q->whereYear('date', $year);
            } else if($filter === 'yearly') {
                $q->whereYear('date', $year);
            } else if($filter === 'weekly') {
                $q->whereDate('date', '>=', $week['start']);
                $q->whereDate('date', '<=', $week['end']);
            } else if($filter === 'daily') {
                $q->whereDate('date', $daily);
            }

            $q->whereHas('schedule', function($q) use ($month, $year, $week, $daily, $filter, $user) {
                $q->where('user_id', $user);
            });
        })->get();

        foreach ($tours as $key => $tour) {
            $availableSchedules = $tour->departures->groupBy('tour_rate_code');

            $paymentTypes = PaymentType::all();
    
            foreach ($paymentTypes as $key => $type) {
                if(isset($availableSchedules[$type->code])) {
                    $tmp = $availableSchedules[$type->code];
                    $total = 0;
                    
                    foreach ($tmp as $key => $value) {
                        $total += $value->rate ? $value->rate->amount : 0;
                    }

                    $receipts = $tour->departures->first()->schedule->user->receipts->where('payment_info.code', $type->code)->where('title_id', $tour->id);
                    $total_payment = 0;
    
                    foreach ($receipts as $key => $receipt) {
                        $total_payment += $receipt->total;
                    }
    
                    $data = array(
                        'data' => $tour,
                        'rate_total' => $total,
                        'tour' => $tour->title,
                        'payment_data' => $receipts,
                        'payment_total' => $total_payment,
                        'payment_type' => $type->name,
                        'departure' => $tour->departures,
                        'is_balance' => $tour->departures->first()->schedule->user->to_balance
                    );
    
                    $grand_rate_total += $total;
    
                    $grand_payment_total += $data['payment_total'];
    
                    array_push($statistics, $data);
    
                    $count_data++;
                }
            }
        }

        return $statistics;
    }

    public function statistics(Request $request, $filter) {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::now();

        $month = $request->date ? Carbon::parse($request->date)->format('m') : $date->format('m');
        $year = $request->date ? Carbon::parse($request->date)->format('Y') : $date->format('Y');
        $week = [
            'start' => $request->date ? Carbon::parse($request->date)->startOfWeek() : $date->startOfWeek(),
            'end' => $request->date ? Carbon::parse($request->date)->endOfWeek() : $date->endOfWeek()
        ];
        $daily = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : $date->format('Y-m-d');

        $users = User::with(['schedules.departure.tour', 'schedules' => function($q) use ($month, $year, $week, $daily, $filter) {
                    $q->whereHas('departure', function($q) use ($month, $year, $week, $daily, $filter) {
                        if($filter === 'monthly') {
                            $q->whereMonth('date', $month);
                            $q->whereYear('date', $year);
                        } else if($filter === 'yearly') {
                            $q->whereYear('date', $year);
                        } else if($filter === 'weekly') {
                            $q->whereDate('date', '>=', $week['start']);
                            $q->whereDate('date', '<=', $week['end']);
                        } else if($filter === 'daily') {
                            $q->whereDate('date', $daily);
                        }
                    });
                }, 'receipts' => function($q) use ($month, $year, $week, $daily, $filter) {
                    if($filter === 'monthly') {
                        $q->whereMonth('event_date', $month);
                        $q->whereYear('event_date', $year);
                    } else if($filter === 'yearly') {
                        $q->whereYear('event_date', $year);
                    } else if($filter === 'weekly') {
                        $q->whereDate('event_date', '>=', $week['start']);
                        $q->whereDate('event_date', '<=', $week['end']);
                    } else if($filter === 'daily') {
                        $q->whereDate('event_date', $daily);
                    }
                }])
                ->whereHas('schedules', function($q) use ($month, $year, $week, $daily, $filter) {
                    $q->whereHas('departure', function($q) use ($month, $year, $week, $daily, $filter) {
                        if($filter === 'monthly') {
                            $q->whereMonth('date', $month);
                            $q->whereYear('date', $year);
                        } else if($filter === 'yearly') {
                            $q->whereYear('date', $year);
                        } else if($filter === 'weekly') {
                            $q->whereDate('date', '>=', $week['start']);
                            $q->whereDate('date', '<=', $week['end']);
                        } else if($filter === 'daily') {
                            $q->whereDate('date', $daily);
                        }
                    });
                })
                ->get();

        $statistics = array();

        $count_data = 0;
        $grand_rate_total = 0;
        $grand_payment_total = 0;

        foreach ($users as $userKey => $user) {
            $availableSchedules = $user->schedules->groupBy('payment_type_code');

            $anticipi_incassi = [];
                   
            if($filter === 'monthly') {

                array_push($anticipi_incassi, [
                    'date' => $date->format('F Y'),
                    // 'payments' => $user->with(['receipts' => function($q) use ($month, $year) {
                    //     $q->whereMonth('event_date', $month)
                    //         ->whereYear('event_date', $year);
                    // }])->find($user->id)
                ]);

            } else if($filter === 'yearly') {

                $start_month = Carbon::create(`$year-01-01`);

                while ($start_month->isSameYear($date)) {

                    array_push($anticipi_incassi, [
                        'date' => $start_month->copy()->format('F Y'),
                        // 'payments' => $user->with(['receipts' => function($q) use ($start_month) {
                        //     $q->whereMonth('event_date', $start_month->copy()->format('m'))
                        //     ->whereYear('event_date', $start_month->copy()->format('Y'));
                        // }])->find($user->id)
                    ]);

                    $start_month->addMonth();

                }

            } else if($filter === 'weekly') {

                if($week['start']->copy()->isSameMonth($week['end']->copy())) {

                    array_push($anticipi_incassi, [
                        'date' => $week['start']->copy()->format('F Y'),
                        // 'payments' => $user->receipts()
                        //             ->whereMonth('event_date', $week['start']->copy()->format('m'))
                        //             ->whereYear('event_date', $week['start']->copy()->format('Y'))
                        //             ->get()
                    ]);

                } else {

                    array_push($anticipi_incassi, [
                        'date' => $week['start']->copy()->format('F Y'),
                        // 'payments' => $user->receipts()
                        //             ->whereMonth('event_date', $week['start']->copy()->format('m'))
                        //             ->whereYear('event_date', $week['start']->copy()->format('Y'))
                        //             ->get()
                    ]);

                    array_push($anticipi_incassi, [
                        'date' => $week['end']->copy()->format('F Y'),
                        // 'payments' => $user->receipts()
                        //             ->whereMonth('event_date', $week['end']->copy()->format('m'))
                        //             ->whereYear('event_date', $week['end']->copy()->format('Y'))
                        //             ->get()
                    ]);

                }

            } else if($filter === 'daily') {

                $anticipi_incassi = [];

                array_push($anticipi_incassi, [
                    'date' => $daily->copy()->format('F Y'),
                    // 'payments' => $user->receipts()
                    //             ->whereMonth('event_date', $month)
                    //             ->whereYear('event_date', $year)
                    //             ->get()
                ]);

            }

            $paymentTypes = PaymentType::all();

            foreach ($paymentTypes as $key => $type) {
                if(isset($availableSchedules[$type->code])) {
                    $tmp = $availableSchedules[$type->code];
                    $total = 0;
                    
                    foreach ($tmp as $key => $value) {
                        $total += $value->rate;
                    }

                    $receipts = $user->receipts->where('payment_info.code', $type->code);
                    $total_payment = 0;

                    foreach ($receipts as $key => $receipt) {
                        $total_payment += $receipt->balance;
                    }

                    $data = array(
                        'date' => Carbon::parse($request->date)->format('Y-m-d'),
                        'filter' => $filter,
                        'data' => $tmp,
                        'rate_total' => $total,
                        'user' => $user,
                        'guide' => $user->full_name,
                        'payment_data' => $receipts,
                        'payment_total' => $total_payment,
                        'payment_type' => $type->name,
                        'payment_type_id' => $type->id,
                        'is_balance' => $user->to_balance,
                        'monthly_payment' => $anticipi_incassi
                    );

                    $grand_rate_total += $total;

                    $grand_payment_total += $data['payment_total'];

                    array_push($statistics, $data);

                    $count_data++;
                }
            }
        }

        return $statistics;
    }

    public function charts(Request $request, $filter) {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::now();

        $month = $request->date ? Carbon::parse($request->date)->format('m') : $date->format('m');
        $year = $request->date ? Carbon::parse($request->date)->format('Y') : $date->format('Y');
        $week = [
            'start' => $request->date ? Carbon::parse($request->date)->startOfWeek() : $date->startOfWeek(),
            'end' => $request->date ? Carbon::parse($request->date)->endOfWeek() : $date->endOfWeek()
        ];
        $daily = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : $date->format('Y-m-d');

        $guides = User::with(['schedules' => function($q) use ($month, $year, $week, $daily, $filter, $request) {
            $q->whereHas('departure', function($q) use ($month, $year, $week, $daily, $filter, $request) {
                if($filter === 'monthly') {
                    $q->whereMonth('date', $month);
                    $q->whereYear('date', $year);
                } else if($filter === 'yearly') {
                    $q->whereYear('date', $year);
                } else if($filter === 'weekly') {
                    $q->whereDate('date', '>=', $week['start']);
                    $q->whereDate('date', '<=', $week['end']);
                } else if($filter === 'daily') {
                    $q->whereDate('date', $daily);
                }

                $q->whereHas('tour', function($q) use ($request){
                    if($request->tour_id) $q->where('id', $request->tour_id);
                    
                    $q->whereHas('info', function($q) use ($request){
                        $q->whereHas('type', function($q) use ($request){
                            if($request->category)
                            $q->where('code', $request->category);
                        });
                    });
                });
            });
        }])->withCount(['schedules' => function($q) use ($month, $year, $week, $daily, $filter, $request) {
            $q->whereHas('departure', function($q) use ($month, $year, $week, $daily, $filter, $request) {
                if($filter === 'monthly') {
                    $q->whereMonth('date', $month);
                    $q->whereYear('date', $year);
                } else if($filter === 'yearly') {
                    $q->whereYear('date', $year);
                } else if($filter === 'weekly') {
                    $q->whereDate('date', '>=', $week['start']);
                    $q->whereDate('date', '<=', $week['end']);
                } else if($filter === 'daily') {
                    $q->whereDate('date', $daily);
                }

                $q->whereHas('tour', function($q) use ($request){
                    if($request->tour_id) $q->where('id', $request->tour_id);

                    $q->whereHas('info', function($q) use ($request){
                        $q->whereHas('type', function($q) use ($request){
                            if($request->category)
                            $q->where('code', $request->category);
                        });
                    });
                });
            });
        }])->whereHas('access_levels', function($q) {
            $q->whereHas('info', function($q) {
                $q->where('code', 'tg');
            });
        })->whereHas('schedules', function($q) use ($month, $year, $week, $daily, $filter, $request) {
            $q->whereHas('departure', function($q) use ($month, $year, $week, $daily, $filter, $request) {
                if($filter === 'monthly') {
                    $q->whereMonth('date', $month);
                    $q->whereYear('date', $year);
                } else if($filter === 'yearly') {
                    $q->whereYear('date', $year);
                } else if($filter === 'weekly') {
                    $q->whereDate('date', '>=', $week['start']);
                    $q->whereDate('date', '<=', $week['end']);
                } else if($filter === 'daily') {
                    $q->whereDate('date', $daily);
                }

                $q->whereHas('tour', function($q) use ($request){
                    if($request->tour_id) $q->where('id', $request->tour_id);

                    $q->whereHas('info', function($q) use ($request){
                        $q->whereHas('type', function($q) use ($request){
                            if($request->category)
                            $q->where('code', $request->category);
                        });
                    });
                });
            });
        })->get();

        if($request->guide) {
            $request->validate([
                'guide' => 'required|exists:users,id'
            ]);
        }

        if($request->tour_id) {
            $tour = TourTitle::find($request->tour_id);

            $title = $tour->title;

            $departures = $tour->departures()->where(function($q) use ($month, $year, $week, $daily, $filter, $request) {
                if($filter === 'monthly') {
                    $q->whereMonth('date', $month);
                    $q->whereYear('date', $year);
                } else if($filter === 'yearly') {
                    $q->whereYear('date', $year);
                } else if($filter === 'weekly') {
                    $q->whereDate('date', '>=', $week['start']);
                    $q->whereDate('date', '<=', $week['end']);
                } else if($filter === 'daily') {
                    $q->whereDate('date', $daily);
                }

                if($request->guide)
                $q->whereHas('schedule', function($q) use ($request) {
                    $q->whereHas('user', function($q) use ($request) {
                        $q->where('id', $request->guide);
                    });
                });
            })->get();
        } else if($request->category) {
            $tours = TourTitle::with(['departures' => function($q) use ($month, $year, $week, $daily, $filter, $request) {
                if($filter === 'monthly') {
                    $q->whereMonth('date', $month);
                    $q->whereYear('date', $year);
                } else if($filter === 'yearly') {
                    $q->whereYear('date', $year);
                } else if($filter === 'weekly') {
                    $q->whereDate('date', '>=', $week['start']);
                    $q->whereDate('date', '<=', $week['end']);
                } else if($filter === 'daily') {
                    $q->whereDate('date', $daily);
                }

                if($request->guide)
                $q->whereHas('schedule', function($q) use ($request) {
                    $q->whereHas('user', function($q) use ($request) {
                        $q->where('id', $request->guide);
                    });
                });
            }, 'info.type'])->withCount('departures')
            ->whereHas('info', function($q) use ($request){
                $q->whereHas('type', function($q) use ($request) {
                    $q->where('code', $request->category);
                });
            })->get();

            $title = $tours ? $tours[0]->info->type->name : null;
        } else {
            $tours = TourTitle::with(['departures' => function($q) use ($month, $year, $week, $daily, $filter, $request) {
                if($filter === 'monthly') {
                    $q->whereMonth('date', $month);
                    $q->whereYear('date', $year);
                } else if($filter === 'yearly') {
                    $q->whereYear('date', $year);
                } else if($filter === 'weekly') {
                    $q->whereDate('date', '>=', $week['start']);
                    $q->whereDate('date', '<=', $week['end']);
                } else if($filter === 'daily') {
                    $q->whereDate('date', $daily);
                }

                if($request->guide)
                $q->whereHas('schedule', function($q) use ($request) {
                    $q->whereHas('user', function($q) use ($request) {
                        $q->where('id', $request->guide);
                    });
                });
            }])->withCount('departures')->get();
            
            $title = "Small Group and Private Tour";
        }

        $toursStats = [];

        if($filter === 'monthly') {
            $start = $week['start']->isSameMonth($date) ? $week['start'] : $week['end'];
            $end = Carbon::parse($start)->addWeek();
            $weekNo = 1;

            while($start->lt($end)) {                
                if($request->tour_id) {
                    $tmp = [
                        'start' => $start->copy()->addDay()->format('Y-m-d'),
                        'end' => $end->format('Y-m-d'),
                        'label' => 'Week '.$weekNo,
                        'data' => $departures->where('date', '>=', $start->copy()->addDay()->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->count()
                    ];
                } else if($request->category) {
                    $toursTotal = 0;
                    
                    foreach ($tours as $key => $value) {
                        $toursTotal += $value->departures->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->count();
                    }

                    $tmp = [
                        'start' => $start->format('Y-m-d'),
                        'end' => $end->format('Y-m-d'),
                        'label' => 'Week '.$weekNo,
                        'data' => $toursTotal
                    ];
                } else {
                    $toursTotal = 0;
                    
                    foreach ($tours as $key => $value) {
                        $toursTotal += $value->departures->where('date', '>=', $start->copy()->addDay()->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->count();
                    }

                    $tmp = [
                        'start' => $start->copy()->addDay()->format('Y-m-d'),
                        'end' => $end->format('Y-m-d'),
                        'label' => 'Week '.$weekNo,
                        'data' => $toursTotal
                    ];
                }
    
                array_push($toursStats, $tmp);
    
                $weekNo++;
                $start = Carbon::parse($end);
                $end = Carbon::parse($start)->isSameMonth($date) ? Carbon::parse($start)->addWeek() : Carbon::parse($end);
                $end = $end;
                if($weekNo === 5) {
                    break;
                }
            }
            
        } else if($filter === 'yearly') {
            $selected_date = Carbon::createFromDate($year, $month, '01');

            while($selected_date->isSameYear($date)) {
                $start = $selected_date;
                $end = Carbon::parse($start)->lastOfMonth();

                if($request->tour_id) {
                    $tmp = [
                        'start' => $start->format('Y-m-d'),
                        'end' => $end->format('Y-m-d'),
                        'label' => $start->englishMonth,
                        'data' => $departures->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->count()
                    ];
                } else if($request->category) {
                    $toursTotal = 0;
                    
                    foreach ($tours as $key => $value) {
                        $toursTotal += $value->departures->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->count();
                    }

                    $tmp = [
                        'start' => $start->format('Y-m-d'),
                        'end' => $end->format('Y-m-d'),
                        'label' => $start->englishMonth,
                        'data' => $toursTotal
                    ];
                } else {
                    $toursTotal = 0;
                    
                    foreach ($tours as $key => $value) {
                        $toursTotal += $value->departures->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->count();
                    }

                    $tmp = [
                        'start' => $start->format('Y-m-d'),
                        'end' => $end->format('Y-m-d'),
                        'label' => $start->englishMonth,
                        'data' => $toursTotal
                    ];
                }

                array_push($toursStats, $tmp);

                $selected_date = $end->addDay();
            }
        } else if($filter === 'weekly') {

            while($week['start']->lte($week['end'])) {
                $date = $week['start']->copy();

                if($request->tour_id) {
                    $tmp = [
                        'label' => $date->englishDayOfWeek,
                        'date' => $date->format('Y-m-d'),
                        'data' => $departures->where('date', $date->format('Y-m-d'))->count()
                    ];
                } else if($request->category) {
                    $toursTotal = 0;
                    
                    foreach ($tours as $key => $value) {
                        $toursTotal += $value->departures->where('date', $date->format('Y-m-d'))->count();
                    }

                    $tmp = [
                        'label' => $date->englishDayOfWeek,
                        'date' => $date->format('Y-m-d'),
                        'data' => $toursTotal
                    ];
                } else {
                    $toursTotal = 0;
                    
                    foreach ($tours as $key => $value) {
                        $toursTotal += $value->departures->where('date', $date->format('Y-m-d'))->count();
                    }

                    $tmp = [
                        'label' => $date->englishDayOfWeek,
                        'date' => $date->format('Y-m-d'),
                        'data' => $toursTotal
                    ];
                }
    
                array_push($toursStats, $tmp);
    
                $week['start'] = $week['start']->addDay();
            }

        }

        return array(
            'label' => $title ? $title : null,
            'data' => $toursStats, 
            'guides' => $guides
        );
    }

    public function downloadTours(Request $request, $filter) {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::now();

        $month = $request->date ? Carbon::parse($request->date)->format('m') : $date->format('m');
        $year = $request->date ? Carbon::parse($request->date)->format('Y') : $date->format('Y');
        $week = [
            'start' => $request->date ? Carbon::parse($request->date)->startOfWeek() : $date->startOfWeek(),
            'end' => $request->date ? Carbon::parse($request->date)->endOfWeek() : $date->endOfWeek()
        ];
        $daily = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : $date->format('Y-m-d');

        $guides = User::with(['schedules' => function($q) use ($month, $year, $week, $daily, $filter, $request) {
            $q->whereHas('departure', function($q) use ($month, $year, $week, $daily, $filter, $request) {
                if($filter === 'monthly') {
                    $q->whereMonth('date', $month);
                    $q->whereYear('date', $year);
                } else if($filter === 'yearly') {
                    $q->whereYear('date', $year);
                } else if($filter === 'weekly') {
                    $q->whereDate('date', '>=', $week['start']);
                    $q->whereDate('date', '<=', $week['end']);
                } else if($filter === 'daily') {
                    $q->whereDate('date', $daily);
                }

                $q->whereHas('tour', function($q) use ($request){
                    if($request->tour_id) $q->where('id', $request->tour_id);
                    
                    $q->whereHas('info', function($q) use ($request){
                        $q->whereHas('type', function($q) use ($request){
                            if($request->category)
                            $q->where('code', $request->category);
                        });
                    });
                });
            });
        }, 'schedules.departure.tour.info.type'])->withCount(['schedules' => function($q) use ($month, $year, $week, $daily, $filter, $request) {
            $q->whereHas('departure', function($q) use ($month, $year, $week, $daily, $filter, $request) {
                if($filter === 'monthly') {
                    $q->whereMonth('date', $month);
                    $q->whereYear('date', $year);
                } else if($filter === 'yearly') {
                    $q->whereYear('date', $year);
                } else if($filter === 'weekly') {
                    $q->whereDate('date', '>=', $week['start']);
                    $q->whereDate('date', '<=', $week['end']);
                } else if($filter === 'daily') {
                    $q->whereDate('date', $daily);
                }

                $q->whereHas('tour', function($q) use ($request){
                    if($request->tour_id) $q->where('id', $request->tour_id);

                    $q->whereHas('info', function($q) use ($request){
                        $q->whereHas('type', function($q) use ($request){
                            if($request->category)
                            $q->where('code', $request->category);
                        });
                    });
                });
            });
        }])->whereHas('access_levels', function($q) {
            $q->whereHas('info', function($q) {
                $q->where('code', 'tg');
            });
        })->whereHas('schedules', function($q) use ($month, $year, $week, $daily, $filter, $request) {
            $q->whereHas('departure', function($q) use ($month, $year, $week, $daily, $filter, $request) {
                if($filter === 'monthly') {
                    $q->whereMonth('date', $month);
                    $q->whereYear('date', $year);
                } else if($filter === 'yearly') {
                    $q->whereYear('date', $year);
                } else if($filter === 'weekly') {
                    $q->whereDate('date', '>=', $week['start']);
                    $q->whereDate('date', '<=', $week['end']);
                } else if($filter === 'daily') {
                    $q->whereDate('date', $daily);
                }

                $q->whereHas('tour', function($q) use ($request){
                    if($request->tour_id) $q->where('id', $request->tour_id);

                    $q->whereHas('info', function($q) use ($request){
                        $q->whereHas('type', function($q) use ($request){
                            if($request->category)
                            $q->where('code', $request->category);
                        });
                    });
                });
            });
        });

        if($request->guide) {
            $guides = $guides->where('id', $request->guide)->get();
        } else {
            $guides = $guides->get();
        }

        return Excel::download(new DepartureExport($guides), 'Tour Guide Agenda.csv');

        // return array(
        //     'guides' => $guides
        // );
    }

    public function tourTrends(Request $request, $filter) {
        $selected_date = Carbon::parse($request->date);

        $data = [];

        $date = [
            'date' => $selected_date->format('Y-m-d'),
            'year' => $selected_date->format('Y'),
            'month' => $selected_date->format('m'),
            'day' => $selected_date->format('d')
        ];
        
        $week = [
            'start' => $selected_date->copy()->startOfWeek(),
            'end' => $selected_date->copy()->endOfWeek()
        ];

        $tours = TourTitle::with(['receipts.payment', 'departures.serial_numbers', 'departures' => function($q) use ($request, $date, $week, $filter) {
            $q->whereHas('schedule');

            if($filter === 'daily') {
                $q->whereYear('date', $date['year']);
                $q->whereMonth('date', $date['month']);
                $q->whereDay('date', $date['day']);
            } else if($filter === 'weekly') {
                $q->whereDate('date', '>=', $week['start']->format('Y-m-d'));
                $q->whereDate('date', '<=', $week['end']->format('Y-m-d'));
            } else if($filter === 'monthly') {
                $q->whereYear('date', $date['year']);
                $q->whereMonth('date', $date['month']);
            } else if($filter === 'yearly') {
                $q->whereYear('date', $date['year']);
            }
        }, 'info.type', 'histories'])
        ->whereHas('info', function($q) use ($request){
            $q->whereHas('type', function($q) use ($request){
                $q->when(isset($request->category), function($q) use ($request) {
                    $q->where('code', $request->category);
                });
            });
        })
        ->whereHas('departures', function($q) use ($request, $date, $week, $filter) {
            $q->whereHas('schedule');

            if($filter === 'daily') {
                $q->whereYear('date', $date['year']);
                $q->whereMonth('date', $date['month']);
                $q->whereDay('date', $date['day']);
            } else if($filter === 'weekly') {
                $q->whereDate('date', '>=', $week['start']->format('Y-m-d'));
                $q->whereDate('date', '<=', $week['end']->format('Y-m-d'));
            } else if($filter === 'monthly') {
                $q->whereYear('date', $date['year']);
                $q->whereMonth('date', $date['month']);
            } else if($filter === 'yearly') {
                $q->whereYear('date', $date['year']);
            }
        })
        ->when($request->tour_id, function($q) use ($request){
            $q->where('id', $request->tour_id);
        })
        ->get();

        if($filter === 'monthly') {
            $start = $week['start']->isSameMonth($selected_date) ? $week['start'] : $week['end'];
            $end = Carbon::parse($start)->addWeek();
            $weekNo = 1;
    
            while($start->lte($end)) {

                $earning = 0;
                $cost = 0;
                
                if($tours) {
                    foreach ($tours as $key => $tour) {

                        $type = $tour->info->type->code;

                        $adult_rate = $tour->other_info && $tour->other_info->participant_rates->where('type', 'adult')->values()->first() ? $tour->other_info->participant_rates->where('type', 'adult')->values()->first()->amount : 0;

                        $child_rate = $tour->other_info && $tour->other_info->participant_rates->where('type', 'child')->values()->first() ? $tour->other_info->participant_rates->where('type', 'child')->values()->first()->amount : 0;

                        $departures = $tour->departures->where('date', '>=', $start->copy()->addDay()->format('Y-m-d'))->where('date', '<=', $end->copy()->format('Y-m-d'))->values();

                        foreach ($departures as $key => $departure) {
                            
                            $receipts = $departure->tour()->first()->receipts()->where('event_date', $start->copy()->addDay()->format('Y-m-d'))->get();

                            if($type === 'small') {
                                $earning += $departure->adult_participants * $adult_rate;
    
                                $earning += $departure->child_participants * $child_rate;
                            } else if($type === 'private') {
                                $earning += $departure->earning;
                            }

                            $cost += $departure->rate && $departure->rate->amount ? $departure->rate->amount : 0;

                            foreach ($departure->serial_numbers as $key => $voucher) {
                                $cost += $voucher->cost;
                            }

                            foreach ($receipts as $key => $receipt) {
                                $cost += ($receipt->payment ? $receipt->payment->anticipi : 0);
    
                                $earning += ($receipt->payment ? $receipt->payment->incassi : 0);
                            }
                        }
                    }
                }

                $tmp = [
                    'start' => $start->copy()->addDay()->format('Y-m-d'),
                    'end' => $end->format('Y-m-d'),
                    'label' => 'Week '.$weekNo,
                    'earning' => $earning,
                    'cost' => $cost,
                    'tours' => $tours
                ];
    
                array_push($data, $tmp);

                $weekNo++;
                $start = Carbon::parse($end);
                $end = Carbon::parse($start)->isSameMonth($selected_date) ? Carbon::parse($start)->addWeek() : Carbon::parse($end);
                $end = $end;
                if($weekNo === 5) {
                    break;
                }
    
            }

        } else if($filter === 'weekly') {

            while($week['start']->lte($week['end'])) {
                $earning = 0;
                $cost = 0;

                $date = $week['start'];

                if($tours) {
                    foreach ($tours as $key => $tour) {

                        $type = $tour->info->type->code;

                        $adult_rate = $tour->other_info && $tour->other_info->participant_rates->where('type', 'adult')->values()->first() ? $tour->other_info->participant_rates->where('type', 'adult')->values()->first()->amount : 0;

                        $child_rate = $tour->other_info && $tour->other_info->participant_rates->where('type', 'child')->values()->first() ? $tour->other_info->participant_rates->where('type', 'child')->values()->first()->amount : 0;

                        $departures = $tour->departures->where('date', $date->copy()->format('Y-m-d'))->values();

                        foreach ($departures as $key => $departure) {

                            $receipts = $departure->tour()->first()->receipts()->where('event_date', $date->copy()->format('Y-m-d'))->get();

                            if($type === 'small') {
                                $earning += $departure->adult_participants * $adult_rate;
    
                                $earning += $departure->child_participants * $child_rate;
                            } else if($type === 'private') {
                                $earning += $departure->earning;
                            }

                            $cost += $departure->rate && $departure->rate->amount ? $departure->rate->amount : 0;

                            foreach ($departure->serial_numbers as $key => $voucher) {
                                $cost += $voucher->cost;
                            }

                            foreach ($receipts as $key => $receipt) {
                                $cost += ($receipt->payment ? $receipt->payment->anticipi : 0);
    
                                $earning += ($receipt->payment ? $receipt->payment->incassi : 0);
                            }
                        }
                    }
                }

                $tmp = [
                    'start' => $date->copy()->format('Y-m-d'),
                    'label' => $date->copy()->englishDayOfWeek,
                    'earning' => $earning,
                    'cost' => $cost,
                    'tours' => $tours
                ];
    
                array_push($data, $tmp);
    
                $week['start'] = $week['start']->addDay();
            }

        } else if($filter === 'yearly') {
            $selected_date = Carbon::createFromDate($date['year'], '01', '01');

            $year = Carbon::createFromDate($date['year'], '01', '01');

            $tmp = [];

            while ($selected_date->isSameYear($year)) {

                $earning = 0;
                $cost = 0;
                
                if($tours) {
                    foreach ($tours as $key => $tour) {

                        $type = $tour->info->type->code;

                        $adult_rate = $tour->other_info && $tour->other_info->participant_rates->where('type', 'adult')->values()->first() ? $tour->other_info->participant_rates->where('type', 'adult')->values()->first()->amount : 0;

                        $child_rate = $tour->other_info && $tour->other_info->participant_rates->where('type', 'child')->values()->first() ? $tour->other_info->participant_rates->where('type', 'child')->values()->first()->amount : 0;

                        $departures = $tour->departures->where('date', '>=', $selected_date->copy()->format('Y-m-d'))->where('date', '<=', $selected_date->copy()->lastOfMonth()->format('Y-m-d'))->values();

                        foreach ($departures as $key => $departure) {

                            $receipts = $departure->tour()->first()->receipts()->where('event_date', $selected_date->copy()->format('Y-m-d'))->get();

                            if($type === 'small') {
                                $earning += $departure->adult_participants * $adult_rate;
    
                                $earning += $departure->child_participants * $child_rate;
                            } else if($type === 'private') {
                                $earning += $departure->earning;
                            }

                            $cost += $departure->rate && $departure->rate->amount ? $departure->rate->amount : 0;

                            foreach ($departure->serial_numbers as $key => $voucher) {
                                $cost += $voucher->cost;
                            }

                            foreach ($receipts as $key => $receipt) {
                                $cost += ($receipt->payment ? $receipt->payment->anticipi : 0);
    
                                $earning += ($receipt->payment ? $receipt->payment->incassi : 0);
                            }
                        }
                    }
                }

                $tmp = [
                    'start' => $selected_date->copy()->format('Y-m-d'),
                    'end' => $selected_date->copy()->lastOfMonth()->format('Y-m-d'),
                    'label' => $selected_date->copy()->englishMonth,
                    'earning' => $earning,
                    'cost' => $cost
                ];

                array_push($data, $tmp);
                
                $selected_date->addMonth();
            }

        }

        return $data;
    }
}
