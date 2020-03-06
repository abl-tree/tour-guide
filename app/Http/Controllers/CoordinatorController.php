<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TourCoordinator;
use Validator;

class CoordinatorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('coordinator.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('coordinator.create');
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:tour_coordinators']
        ])->validate();

        $coordinator = new TourCoordinator;
        $coordinator->first_name = $request->first_name;
        $coordinator->last_name = $request->last_name;
        $coordinator->contact = $request->contact;
        $coordinator->email = $request->email;
        
        if($coordinator->save()) {
            $request->session()->flash('status', 'Tour coordinator was added successfully.');
        } else {
            $request->session()->flash('error', 'Tour coordinator was not added. Please try again.');
        }

        return back();
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
        $coordinator = TourCoordinator::find($id);

        if(!$coordinator) {
            return abort(403, 'Coordinator Not Found');
        }

        return view('coordinator.edit', compact('coordinator'));
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
        $coordinator = TourCoordinator::find($id);
        $coordinator->first_name = $request->first_name;
        $coordinator->last_name = $request->last_name;
        $coordinator->contact = $request->contact;
        $coordinator->email = $request->email;

        if($coordinator->save()) {
            $request->session()->flash('status', 'Tour coordinator was updated successfully.');
        } else {
            $request->session()->flash('error', 'Tour coordinator was not updated. Please try again.');
        }

        return back();
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

    public function list(Request $request, $option = null) {
        if($option === 'search') {
            $query = explode(" ", $request->q);

            // $coordinators = TourCoordinator::where(function($q) use ($query) {
            //     foreach ($query as $key => $value) {
            //         $q->orWhere('first_name', 'like', '%'.$value.'%');
            //         $q->orWhere('last_name', 'like', '%'.$value.'%');
            //     }
            // });

            return TourCoordinator::get();

        } else {
            $coordinators = new TourCoordinator;
        }

        if(isset($request->per_page) && is_numeric($request->per_page)) {
            $coordinators = $coordinators->paginate((int) $request->per_page);
        } else {
            $coordinators = $coordinators->paginate();
        }

        return $coordinators;
    }
}
