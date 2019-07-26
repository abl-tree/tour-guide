<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\Receipt;
use App\User;
use Carbon\Carbon;
use Validator;
use Auth;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('payment');
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
        $receipt = null;
        $check = [
            'amount' => 'required|numeric|gt:0',
            'file' => [
                Rule::requiredIf($category === 'anticipi'),
                'image',
                'max:5120'
            ]
        ];

        if($request->receipt) {
            $check['receipt'] = 'exists:receipts,id';
        }

        Validator::make($request->all(), $check, [
            'file.required' => 'The receipt image is required',
            'file.max' => 'The file must not be greater than 5MB'
        ])->validate();

        if(!$request->receipt) {
            $receipt = new Receipt;
            $receipt->user_id = Auth::id();
            $receipt->event_date = $request->date;
            $receipt->save();
        }

        $payment = new Payment;
        $payment->receipt_id = $receipt ? $receipt->id : $request->receipt;
        $payment->amount = $request->amount;
        if($category === 'anticipi') {
            $path = $request->file('file')->store('/');
            $url = Storage::url($path);
            $payment->receipt_url = $url;
            $category = 0;
        } else {
            $category = 1;
        }
        $payment->category = $category;
        $payment->save();

        return json_encode($receipt);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $schedule = null)
    {
        $isAdmin = Auth::user()->access_levels()->whereHas('info', function($q) {
            $q->where('code', 'admin');
            })->first();

        $result = array(
            'date' => $schedule, 
            'data' => [],
            'isAdmin' => $isAdmin ? true : false
        );

        $guides = User::with(['info', 'receipts' => function($q) use ($schedule) {
            $q->where('event_date', $schedule);
        }])->whereHas('access_levels', function($q) {
            $q->whereHas('info', function($q) {
                $q->where('code', 'tg');
            });
        });

        if($isAdmin) $result['data'] = array('tour_guides' => $guides->whereNotNull('accepted_at')->get());
        else $result['data'] = array('tour_guides' => $guides->find(Auth::id()));

        return response()->json($result);
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
        $schedule = Receipt::find($id);
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
