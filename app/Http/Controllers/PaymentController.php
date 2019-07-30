<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\Receipt;
use App\Models\TourTitle;
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

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tour_titles = TourTitle::all();
        $isAdmin = Auth::user()->access_levels()->whereHas('info', function($q) {
            $q->where('code', 'admin');
            })->first() ? true : false;

        return view('payment.create')->with([
            'titles' => $tour_titles,
            'isAdmin' => $isAdmin
            ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $receipt = null;

        $isPaymentExists = User::with(['info', 'receipts' => function($q) use ($request){
            $q->where('event_date', $request->date);
        }])->whereHas('receipts', function($q) use ($request) {
            $q->where('event_date', $request->date);
        })->find(Auth::id());

        $check = [
            'anticipi' => [
                Rule::requiredIf($request->incassi == null),
                'nullable',
                'numeric',
                'gt:0',
                'lt:1000000'
            ],
            'incassi' => [
                Rule::requiredIf($request->anticipi == null),
                'nullable',
                'numeric',
                'gt:0',
                'lt:1000000'
            ],
            'file' => [
                Rule::requiredIf($request->anticipi > 0),
                'image',
                'max:10240'
            ],
            'title' => 'required|exists:tour_titles,id',
            'date' => [
                'required',
                'date',
                'date_format:"Y-m-d"',
                function ($attribute, $value, $fail) use ($isPaymentExists) {
                    if ($isPaymentExists && $isPaymentExists->receipts->count() && $isPaymentExists->receipts->first()->paid_at) {
                        $fail('The '.$attribute.' is already marked paid by admin');
                    }
                }
            ]
        ];

        $validator = Validator::make($request->all(), $check, [
            'file.required' => 'The receipt image is required',
            'file.max' => 'The file must not be greater than 5MB'
        ])->validate();

        if($isPaymentExists) {
            $receipt = $isPaymentExists->receipts[0];
        } else {
            $receipt = new Receipt;
            $receipt->user_id = Auth::id();
            $receipt->event_date = $request->date;
            $receipt->save();
        }

        $payment = $receipt->payment ? Payment::find($receipt->payment->id) : new Payment;
        $payment->receipt_id = $receipt->id;
        $payment->anticipi = $request->anticipi ? $request->anticipi : 0;
        $payment->incassi = $request->incassi ? $request->incassi : 0;
        if($request->file('file') && $request->anticipi > 0) {
            $path = $request->file('file')->store('/');
            $url = Storage::url($path);
            $payment->receipt_url = $url;
        } else $payment->receipt_url = null;
        $payment->save();

        return json_encode($payment);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id = null)
    {
        Validator::make($request->all(), [
            'date' => 'required'
        ])->validate();

        $date = $request->date;

        $isAdmin = Auth::user()->access_levels()->whereHas('info', function($q) {
            $q->where('code', 'admin');
            })->first();

        $result = array(
            'date' => $date, 
            'formats' => [
                'month' => Carbon::parse($date)->isoFormat('MMMM'),
                'day' => Carbon::parse($date)->format('d'),
                'year' => Carbon::parse($date)->format('Y')
            ],
            'data' => [],
            'isAdmin' => $isAdmin ? true : false
        );

        $guides = User::with(['info', 'receipts' => function($q) use ($date) {
            $year = Carbon::parse($date)->format('Y');
            $month = Carbon::parse($date)->format('m');

            $q->whereMonth('event_date', '=', $month);
            $q->whereYear('event_date', '=', $year);
            $q->whereHas('payment');
        }])->whereHas('access_levels', function($q) {
            $q->whereHas('info', function($q) {
                $q->where('code', 'tg');
            });
        });

        if($isAdmin) {
            $result['data'] = array(
                'tour_guides' => $guides->whereNotNull('accepted_at')->get(),
                'selected_guide' => $id ? $guides->where('id', $id)->whereNotNull('accepted_at')->first() : null
            );
        } else $result['data'] = array('tour_guides' => $guides->find(Auth::id()));

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
        Validator::make($request->all(), [
            'user' => 'required|exists:users,id',
            'date' => 'required|date|date_format:"Y-m"'
        ])->validate();

        $date = Carbon::parse($request->date);

        $user = User::find($request->user);

        $receipts = $user->receipts()
                    ->whereMonth('event_date', $date->format('m'))
                    ->whereYear('event_date', $date->format('Y'))
                    ->whereHas('payment');

        if($id === 'balanced') {
            $receipts = $receipts->update(['paid_at' => Carbon::now()]);
        } else if($id === 'unbalanced') {
            $receipts = $receipts->update(['paid_at' => null]);
        }

        return $user;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $receipt = Receipt::find($id);
        $receipt->delete();
    }
}
