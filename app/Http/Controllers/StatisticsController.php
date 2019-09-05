<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        return view('statistics.index', compact('current_month', 'tours'));
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

        $statistics = array();

        $count_data = 0;
        $grand_rate_total = 0;
        $grand_payment_total = 0;

        $tours = TourTitle::with(['departures' => function($q) use ($month, $year, $week, $daily, $filter) {
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

            $q->with('schedule');

            $q->whereHas('schedule', function($q) use ($month, $year, $week, $daily, $filter) {
                $q->whereHas('user', function($q) use ($month, $year, $week, $daily, $filter) {
                    $q->where('user_id', Auth::id());
                    $q->with('receipts');
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
        }])->whereHas('departures', function($q) use ($month, $year, $week, $daily, $filter) {
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

            $q->whereHas('schedule', function($q) use ($month, $year, $week, $daily, $filter) {
                $q->where('user_id', Auth::id());
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

                    $receipts = $tour->departures->first()->schedule->user->receipts->where('payment_info.code', $type->code);
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

        $user = User::with(['schedules.departure.tour', 'schedules' => function($q) use ($month, $year, $week, $daily, $filter) {
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
                })->where('id', Auth::id())->first();

        if($user && $user->schedules) {
            $availableSchedules = $user->schedules->groupBy('payment_type_code');
    
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
                        $total_payment += $receipt->total;
                    }
    
                    $data = array(
                        'data' => $tmp,
                        'rate_total' => $total,
                        'guide' => $user->full_name,
                        'payment_data' => $receipts,
                        'payment_total' => $total_payment,
                        'payment_type' => $type->name,
                        'is_balance' => $user->to_balance
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

            $paymentTypes = PaymentType::all();

            foreach ($paymentTypes as $key => $type) {
                if(isset($availableSchedules[$type->code])) {
                    $tmp = $availableSchedules[$type->code];;
                    $total = 0;
                    
                    foreach ($tmp as $key => $value) {
                        $total += $value->rate;
                    }

                    $receipts = $user->receipts->where('payment_info.code', $type->code);
                    $total_payment = 0;

                    foreach ($receipts as $key => $receipt) {
                        $total_payment += $receipt->total;
                    }

                    $data = array(
                        'data' => $tmp,
                        'rate_total' => $total,
                        'guide' => $user->full_name,
                        'payment_data' => $receipts,
                        'payment_total' => $total_payment,
                        'payment_type' => $type->name,
                        'is_balance' => $user->to_balance
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

        $guides = User::with(['schedules' => function($q) use ($month, $year, $week, $daily, $filter) {
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
        }])->withCount(['schedules' => function($q) use ($month, $year, $week, $daily, $filter) {
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
        }])->whereHas('access_levels', function($q) {
            $q->whereHas('info', function($q) {
                $q->where('code', 'tg');
            });
        })->whereHas('schedules', function($q) use ($month, $year, $week, $daily, $filter) {
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
        })->get();

        if($request->tour_id) {
            $tour = TourTitle::find($request->tour_id);

            $title = $tour->title;

            $departures = $tour->departures()->where(function($q) use ($month, $year, $week, $daily, $filter) {
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
            })->get();
        } else if($request->category) {
            $tours = TourTitle::with(['departures' => function($q) use ($month, $year, $week, $daily, $filter) {
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
            }, 'info.type'])->withCount('departures')
            ->whereHas('info', function($q) use ($request){
                $q->whereHas('type', function($q) use ($request) {
                    $q->where('code', $request->category);
                });
            })->get();

            $title = $tours ? $tours[0]->info->type->name : null;
        } else {
            $tours = TourTitle::with(['departures' => function($q) use ($month, $year, $week, $daily, $filter) {
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
                        'start' => $start->format('Y-m-d'),
                        'end' => $end->format('Y-m-d'),
                        'label' => 'Week '.$weekNo,
                        'data' => $departures->where('date', '>=', $start->format('Y-m-d'))->where('date', '<', $end->format('Y-m-d'))->count()
                    ];
                } else if($request->category) {
                    $toursTotal = 0;
                    
                    foreach ($tours as $key => $value) {
                        $toursTotal += $value->departures->where('date', '>=', $start->format('Y-m-d'))->where('date', '<', $end->format('Y-m-d'))->count();
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
                        $toursTotal += $value->departures->where('date', '>=', $start->format('Y-m-d'))->where('date', '<', $end->format('Y-m-d'))->count();
                    }

                    $tmp = [
                        'start' => $start->format('Y-m-d'),
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
                $date = $week['start'];

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
}