<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\CookingClassesImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\CookingClass;

class CookingClassesController extends Controller
{
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

    public function list(Request $request, $option = null) {
        $coordinators = CookingClass::orderBy('date', 'desc');

        if(isset($request->per_page) && is_numeric($request->per_page)) {
            $coordinators = $coordinators->paginate((int) $request->per_page);
        } else {
            $coordinators = $coordinators->paginate();
        }

        return $coordinators;
    }

}
