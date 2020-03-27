<?php

namespace App\Repositories;

use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\CookingClassRepository;
use App\Exports\DepartureExport;
use App\Models\TourTitle;
use App\Models\Schedule;
use App\Models\PaymentType;
use App\User;
use Carbon\Carbon;
use Auth;

class TourStatisticsRepository
{
    protected $carbon,
            $tour,
            $cooking_class_repo;

    public function __construct()
    {
        $this->carbon = new Carbon;
        $this->tour = new TourTitle;
        $this->cooking_class_repo = new CookingClassRepository;
    }

    public function tourTrends($request, $filter) {
        $selected_date = $this->carbon->parse($request->date);

        $data = [
            'data' => [],
            'grand_total' => 0
        ];

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

        $tours = $this->tour->with(['receipts.payment', 'departures.serial_numbers', 'departures' => function($q) use ($request, $date, $week, $filter) {
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
            $weekNo = 1;
    
            while($start->isSameMonth($selected_date)) {
                $tmp_start = $start->copy()->addDay()->format('Y-m-d');
                $tmp_end = $start->copy()->addWeek()->format('Y-m-d');

                $earning = 0;
                $cost = 0;
                
                if($tours) {
                    foreach ($tours as $key => $tour) {

                        $type = $tour->info->type->code;

                        $adult_rate = $tour->other_info && $tour->other_info->participant_rates->where('type', 'adult')->values()->first() ? $tour->other_info->participant_rates->where('type', 'adult')->values()->first()->amount : 0;

                        $child_rate = $tour->other_info && $tour->other_info->participant_rates->where('type', 'child')->values()->first() ? $tour->other_info->participant_rates->where('type', 'child')->values()->first()->amount : 0;

                        $departures = $tour->departures->where('date', '>=', $tmp_start)->where('date', '<=', $tmp_end)->values();

                        foreach ($departures as $key => $departure) {
                            
                            $receipts = $departure->tour()->first()->receipts()->where('event_date', $tmp_start)->get();

                            if($type === 'small') {
                                $earning += $departure->adult_participants * $adult_rate;
    
                                $earning += $departure->child_participants * $child_rate;

                                if($total_participants = $departure->adult_participants + $departure->child_participants >= 7) {
                                    $cost += $total_participants * 1.5;
                                }

                            } else if($type === 'private') {
                                $earning += $departure->earning;
                            }

                            $cost += $departure->custom_rate ? $departure->custom_rate : ($departure->rate && $departure->rate->amount ? $departure->rate->amount : 0);

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

                $total = $earning - $cost;

                $tmp = [
                    'start' => $tmp_start,
                    'date' => $tmp_start,
                    'end' => $tmp_end,
                    'label' => 'Week '.$weekNo,
                    'earning' => $earning,
                    'cost' => $cost,
                    'tours' => $tours
                ];

                $data['grand_total'] += $total;
    
                array_push($data['data'], $tmp);

                $weekNo++;
                $start->addWeek();
                    
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

                                if($total_participants = $departure->adult_participants + $departure->child_participants >= 7) {
                                    $cost += $total_participants * 1.5;
                                }
                            } else if($type === 'private') {
                                $earning += $departure->earning;
                            }

                            $cost += $departure->custom_rate ? $departure->custom_rate : ($departure->rate && $departure->rate->amount ? $departure->rate->amount : 0);

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

                $total = $earning - $cost;

                $tmp = [
                    'date' => $date->copy()->format('Y-m-d'),
                    'start' => $date->copy()->format('Y-m-d'),
                    'label' => $date->copy()->englishDayOfWeek,
                    'earning' => $earning,
                    'cost' => $cost,
                    'tours' => $tours
                ];

                $data['grand_total'] += $total;
    
                array_push($data['data'], $tmp);
    
                $week['start'] = $week['start']->addDay();
            }

        } else if($filter === 'yearly') {
            $selected_date = $this->carbon->createFromDate($date['year'], '01', '01');

            $year = $this->carbon->createFromDate($date['year'], '01', '01');

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

                                if($total_participants = $departure->adult_participants + $departure->child_participants >= 7) {
                                    $cost += $total_participants * 1.5;
                                }
                            } else if($type === 'private') {
                                $earning += $departure->earning;
                            }

                            $cost += ($departure->custom_rate) ? $departure->custom_rate : ($departure->rate && $departure->rate->amount ? $departure->rate->amount : 0);

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

                $total = $earning - $cost;

                $tmp = [
                    'date' => $selected_date->copy()->format('Y-m-d'),
                    'start' => $selected_date->copy()->format('Y-m-d'),
                    'end' => $selected_date->copy()->lastOfMonth()->format('Y-m-d'),
                    'label' => $selected_date->copy()->englishMonth,
                    'earning' => $earning,
                    'cost' => $cost
                ];

                $data['grand_total'] += $total;

                array_push($data['data'], $tmp);
                
                $selected_date->addMonth();
            }

        }

        return $data;
    }

    public function tourTrendsCookingClasses($request, $filter) {
        $request['filter'] = $filter;

        $cooking_stats = $this->cooking_class_repo->statistics($request);

        $tour_stats = $this->tourTrends($request, $filter);

        foreach ($cooking_stats as $key => $cooking) {
            $date = $cooking['date'];
        }

        return $tour_stats;
    }

}