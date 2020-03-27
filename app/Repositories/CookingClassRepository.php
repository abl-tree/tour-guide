<?php

namespace App\Repositories;

use App\Imports\CookingClassesImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\CookingClass;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Validator;
use DB;

class CookingClassRepository
{

    protected $carbon,
            $cooking_class,
            $db,
            $validator,
            $rule,
            $excel,
            $cooking_class_import;

    public function __construct()
    {
        $this->carbon = new Carbon();
        $this->cooking_class = new CookingClass();
        $this->db = new DB();
        $this->validator = new Validator();
        $this->rule = new Rule();
        $this->excel = new Excel();
        $this->cooking_class_import = new CookingClassesImport();
    }

    public function statistics($request) {
        $coordinators = new CookingClass;
        
        $results = [];

        if(isset($request->filter) && isset($request->date)) {
            if($request->filter === 'yearly') {
                $request->date = $request->date.'-01-01';
            }

            Validator::make($request->all(), [
                'filter' => [
                    'required',
                    Rule::in(['yearly', 'monthly', 'weekly', 'daily'])
                ],
                'date' => [
                    'required',
                    'date'
                ]
            ])->validate();

            if($request->filter === 'daily') {
                $date = Carbon::parse($request->date)->format('Y-m-d');

                $tmp_coordinators = $coordinators->whereDate('date', $date);

                $chef_cost = $tmp_coordinators->sum(DB::raw('no_of_chef * cost_per_chef'));
                $assistant_cost = $tmp_coordinators->sum(DB::raw('no_of_chef * cost_per_chef'));
                $fuel_cost = $tmp_coordinators->sum('fuel_cost');
                $ingredient_cost = $tmp_coordinators->sum('ingredient_cost');
                $other_cost = $tmp_coordinators->sum('other_cost');
                
                $earnings = $tmp_coordinators->sum(DB::raw('no_of_participant * cost_per_participant'));
                $costs = $chef_cost + $assistant_cost + $fuel_cost + $ingredient_cost + $other_cost;

                $result = [
                    'title' => $date,
                    'earnings' => number_format($earnings, 2),
                    'costs' => number_format($costs, 2)
                ];

                array_push($results, $result);
            } else if($request->filter === 'weekly') {
                $start = Carbon::parse($request->date)->startOfWeek();
                $end = Carbon::parse($request->date)->endOfWeek();

                while ($start->lte($end)) {                    
                    $date = $start->copy()->format('Y-m-d');

                    $tmp_coordinators = $coordinators->whereDate('date', $date);

                    $chef_cost = $tmp_coordinators->sum(DB::raw('no_of_chef * cost_per_chef'));
                    $assistant_cost = $tmp_coordinators->sum(DB::raw('no_of_chef * cost_per_chef'));
                    $fuel_cost = $tmp_coordinators->sum('fuel_cost');
                    $ingredient_cost = $tmp_coordinators->sum('ingredient_cost');
                    $other_cost = $tmp_coordinators->sum('other_cost');
                    
                    $earnings = $tmp_coordinators->sum(DB::raw('no_of_participant * cost_per_participant'));
                    $costs = $chef_cost + $assistant_cost + $fuel_cost + $ingredient_cost + $other_cost;

                    $result = [
                        'title' => $date,
                        'earnings' => number_format($earnings, 2),
                        'costs' => number_format($costs, 2)
                    ];

                    array_push($results, $result);

                    $start->addDay();
                }

            } else if($request->filter === 'monthly') {
                $date = Carbon::parse($request->date);
                $start = $date->copy()->startOfWeek()->isSameMonth($date) ? $date->copy()->startOfWeek() : $date->copy()->endOfWeek();
                $weekNo = 0;
                
                while($start->isSameMonth($date)) {
                    $tmp_start = $start->copy()->addDay()->format('Y-m-d');
                    $tmp_end = $start->copy()->addWeek()->format('Y-m-d');

                    $tmp_coordinators = $coordinators->whereDate('date', '>=', $tmp_start)->whereDate('date', '<=', $tmp_end);

                    $chef_cost = $tmp_coordinators->sum(DB::raw('no_of_chef * cost_per_chef'));
                    $assistant_cost = $tmp_coordinators->sum(DB::raw('no_of_chef * cost_per_chef'));
                    $fuel_cost = $tmp_coordinators->sum('fuel_cost');
                    $ingredient_cost = $tmp_coordinators->sum('ingredient_cost');
                    $other_cost = $tmp_coordinators->sum('other_cost');
                    
                    $earnings = $tmp_coordinators->sum(DB::raw('no_of_participant * cost_per_participant'));
                    $costs = $chef_cost + $assistant_cost + $fuel_cost + $ingredient_cost + $other_cost;

                    $result = [
                        'start' => $tmp_start,
                        'end' => $tmp_end,
                        'title' => 'Week '.++$weekNo,
                        'earnings' => number_format($earnings, 2),
                        'costs' => number_format($costs, 2)
                    ];
    
                    array_push($results, $result);

                    $start->addWeek();
                }
            } else if($request->filter === 'yearly') {
                $date = Carbon::parse($request->date);
                
                $start = Carbon::createFromDate($date->format('Y'), '01', '01');

                while($start->isSameYear($date)) {
                    $tmp_coordinators = $coordinators->whereMonth('date', $start->copy()->format('m'))->whereYear('date', $start->copy()->format('Y'));

                    $chef_cost = $tmp_coordinators->sum(DB::raw('no_of_chef * cost_per_chef'));
                    $assistant_cost = $tmp_coordinators->sum(DB::raw('no_of_chef * cost_per_chef'));
                    $fuel_cost = $tmp_coordinators->sum('fuel_cost');
                    $ingredient_cost = $tmp_coordinators->sum('ingredient_cost');
                    $other_cost = $tmp_coordinators->sum('other_cost');
                    
                    $earnings = $tmp_coordinators->sum(DB::raw('no_of_participant * cost_per_participant'));
                    $costs = $chef_cost + $assistant_cost + $fuel_cost + $ingredient_cost + $other_cost;

                    $result = [
                        'date' => $start->copy()->format('m-Y'),
                        'title' => $start->copy()->format('Y-m'),
                        'earnings' => number_format($earnings, 2),
                        'costs' => number_format($costs, 2)
                    ];
    
                    array_push($results, $result);

                    $start->addMonth();
                }
            }

            $coordinators = $coordinators->where(function($q) use ($request) {
            });
        }

        return $results;
    }

}