<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\BookingsImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\TourTitle;
use Carbon\Carbon;
use Storage;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tour_titles = TourTitle::whereNull('suspended_at')->get();

        $data = [
            'titles' => $tour_titles
        ];

        return view('booking.index')->with($data);
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

    public function import(Request $request, $option) {
        $request->validate([
            'date' => 'required|date|after:today',
            // 'file' => 'required|file',
            'tour' => 'required|exists:tour_titles,id'
        ]);
        
        $data = [
            'tour' => $request->tour,
            'date' => $request->date
        ];

        $tour = TourTitle::find($request->tour);

        $departures = $tour->departures()
                ->whereDate('date', $request->date)
                ->whereHas('bookings', function($q) use ($option) {
                    $q->where('source', $option);
                })
                ->get();

        $import = new BookingsImport($option, $data);

        $file = $request->file('file');

        if($option === 'airbnb') {

            if($departures) {
                foreach ($departures as $key => $departure) {
                    if($departure->pax_total === $departure->has_airbnb) {
                        $departure->delete();
                    } else {
                        $airbnb_pax = $departure->has_airbnb;

                        if($departure->child_participants > $airbnb_pax) $departure->child_participants - $airbnb_pax;
                        else {
                            $airbnb_pax -= $departure->child_participants;

                            $departure->child_participants = 0;

                            $departure->adult_participants -= $airbnb_pax;
                        }

                        $departure->save();

                        $departure->bookings()->where('source', $option)->delete();
                    }
                }
            }

            try {

                Excel::import($import, $file);

            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                $failures = $e->failures();
                
                foreach ($failures as $failure) {
                    $failure->row(); // row that went wrong
                    $failure->attribute(); // either heading key (if using heading row concern) or column index
                    $failure->errors(); // Actual error messages from Laravel validator
                    $failure->values(); // The values of the row that has failed.
                }
            }
        } else if($option === 'fareharbor') {

            if($departures) {
                foreach ($departures as $key => $departure) {
                    if($departure->pax_total === $departure->has_fareharbor) {
                        $departure->delete();
                    } else {
                        $fareharbor_pax = $departure->has_fareharbor;

                        if($departure->child_participants > $fareharbor_pax) $departure->child_participants - $fareharbor_pax;
                        else {
                            $fareharbor_pax -= $departure->child_participants;

                            $departure->child_participants = 0;

                            $departure->adult_participants -= $fareharbor_pax;
                        }

                        $departure->save();

                        $departure->bookings()->where('source', $option)->delete();
                    }
                }
            }

            try {

                Excel::import($import, $file);

            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                $failures = $e->failures();
                
                foreach ($failures as $failure) {
                    $failure->row(); // row that went wrong
                    $failure->attribute(); // either heading key (if using heading row concern) or column index
                    $failure->errors(); // Actual error messages from Laravel validator
                    $failure->values(); // The values of the row that has failed.
                }
            }

        }
                
        return response()->json('Successfully imported '.$import->getRowCount().' bookings');
    }
}
