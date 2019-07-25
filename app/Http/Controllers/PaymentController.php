<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Payment;
use App\Models\Schedule;
use Carbon\Carbon;
use Validator;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return 'dsadsa';
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
    public function store(Request $request, $category)
    {
        Validator::make($request->all(), [
            'schedule_id' => 'required|exists:schedules,id',
            'amount' => 'required|numeric|gt:0',
            'file' => [
                Rule::requiredIf($category === 'anticipi'),
                'image',
                'max:5120'
            ]
        ], [
            'file.required' => 'The receipt image is required',
            'file.max' => 'The file must not be greater than 5MB'
        ])->validate();

        $payment = new Payment;
        $payment->schedule_id = $request->schedule_id;
        $payment->amount = $request->amount;
        if($category === 'anticipi') {
            $path = $request->file('file')->store('receipts');
            $payment->receipt_url = $path;
            $category = 0;
        } else {
            $category = 1;
        }
        $payment->category = $category;
        $payment->save();

        return json_encode($payment);
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
        $schedule = Schedule::find($id);
        $schedule->paid_at = Carbon::now();
        $schedule->save();

        return $schedule;
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
}
