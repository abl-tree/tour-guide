<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\CookingClassesImport;
use App\Repositories\CookingClassRepository;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\CookingClass;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Validator;
use DB;

class CookingClassesController extends Controller
{
    protected $cooking_class_repo;
    
    public function __construct()
    {
        $this->cooking_class_repo = new CookingClassRepository();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('cooking_classes.index');
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
        $cooking = CookingClass::find($id);
        $cooking->delete();

        return response()->json($cooking);
    }

    public function import(Request $request) {
        $request->validate([
            'file' => 'required|file'
        ]);

        $file = $request->file('file');

        $import = new CookingClassesImport;

        try {

            $import->import($file);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $errors = [];
            
            foreach ($failures as $failure) {
                $row = $failure->row(); // row that went wrong
                $failure->attribute(); // either heading key (if using heading row concern) or column index
                $error = $failure->errors(); // Actual error messages from Laravel validator
                $failure->values(); // The values of the row that has failed.

                array_push($errors, [
                    'row' => $row,
                    'error' => $error
                    ]);
            }

            return response()->json($errors, 403);
        }

        return response()->json('Successfully imported '.$import->getRowCount().' bookings');

    }

    public function list(Request $request) {
        $coordinators = CookingClass::orderBy('date', 'desc');

        if(isset($request->filter) && isset($request->date)) {
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

            $coordinators = $coordinators->where(function($q) use ($request) {
                if($request->filter === 'daily') {
                    $date = Carbon::parse($request->date)->format('Y-m-d');

                    $q->whereDate('date', $date);
                } else if($request->filter === 'weekly') {
                    $start = Carbon::parse($request->date)->startOfWeek()->format('Y-m-d');
                    $end = Carbon::parse($request->date)->endOfWeek()->format('Y-m-d');

                    $q->whereDate('date', '>=', $start);
                    $q->whereDate('date', '<=', $end);
                } else if($request->filter === 'monthly') {
                    $date = Carbon::parse($request->date);

                    $q->whereMonth('date', $date->copy()->format('m'));
                    $q->whereYear('date', $date->copy()->format('Y'));
                } else if($request->filter === 'yearly') {
                    $date = Carbon::parse($request->date);

                    $q->whereYear('date', $date->copy()->format('Y'));
                }
            });
        }

        $chef_cost = $coordinators->sum(DB::raw('no_of_chef * cost_per_chef'));
        $assistant_cost = $coordinators->sum(DB::raw('no_of_assistant * cost_per_assistant'));
        $fuel_cost = $coordinators->sum('fuel_cost');
        $ingredient_cost = $coordinators->sum('ingredient_cost');
        $other_cost = $coordinators->sum('other_cost');

        $earnings = $coordinators->sum(DB::raw('no_of_participant * cost_per_participant'));
        $costs = $chef_cost + $assistant_cost + $fuel_cost + $ingredient_cost + $other_cost;
        $balance = $earnings - $costs;

        $grand_total = [
            'no_of_chef' => $coordinators->sum('no_of_chef'),
            'cost_per_chef' => number_format($chef_cost, 2),
            'no_of_assistant' => $coordinators->sum('no_of_assistant'),
            'cost_per_assistant' => number_format($assistant_cost, 2),
            'fuel_cost' => number_format($fuel_cost, 2),
            'ingredient_cost' => number_format($ingredient_cost, 2),
            'other_cost' => number_format($other_cost, 2),
            'no_of_participant' => $coordinators->sum('no_of_participant'),
            'cost_per_participant' => number_format($coordinators->sum('cost_per_participant'), 2),
            'earnings' => number_format($earnings, 2),
            'costs' => number_format($costs, 2),
            'balance' => number_format($balance, 2),
            'actions' => false,
            '_rowVariant' => 'success'
        ];

        if(isset($request->per_page) && is_numeric($request->per_page)) {
            $coordinators = $coordinators->paginate((int) $request->per_page);
        } else {
            $coordinators = $coordinators->paginate();
        }

        return response()->json([
            'result' => $coordinators,
            'total' => $grand_total
        ]);

    }

    public function statistics(Request $request) {
        $results = $this->cooking_class_repo->statistics($request);

        return response()->json([
            'result' => $results
        ]);
    }

}
