<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Schedule;
use App\Models\ParticipantType;
use App\Models\TourType;
use App\Models\TourInfo;
use App\Models\TourTitle;
use App\Models\TourInfoHistory;
use App\Models\TourRate;
use App\Models\TourParticipantRate;
use App\Models\TourDuration;
use App\Models\TourDeparture;
use App\Models\PaymentType;
use App\Models\Availability;
use Validator;
use Carbon\Carbon;
use Auth;

class ToursController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $isAdmin = Auth::user()->access_levels()->whereHas('info', function($q) {
            $q->where('code', 'admin');
            })->first() ? true : false;
        $types = TourType::all();

        return view('tours.index')->with(['isAdmin' => $isAdmin, 'types' => $types]);;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $types = TourType::all();

        return view('tours.create')->with(['types' => $types]);
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
            'name' => 'required|max:255',
            'code' => 'required|max:100|unique:tour_infos,tour_code',
            'type' => 'required|exists:tour_types,code',
            'departure' => 'required',
            'color' => 'required_if:type,small|unique:tour_infos,color',
            'cash' => 'required|numeric|gt:0|lt:1000000',
            'invoice' => 'required|numeric|gt:0|lt:1000000',
            'payoneer' => 'nullable|numeric|lt:1000000',
            'paypal' => 'nullable|numeric|lt:1000000',
            'adult' => 'required_if:type,small|numeric|gt:0|lt:1000000',
            'children' => 'required_if:type,small|numeric|gt:0|lt:1000000',
            'duration_day' => 'required|numeric',
            'duration' => 'required|date_format:H:i',
            'availabilities' => 'required|array|min:1'
        ])->validate();
        
        if($request->hasFile('file'))
        $request->validate([
            'file' => 'image|max:10240'
        ]);

        $url = null;

        $type_id = TourType::where('code', $request->type)->first()->id;

        $tour = new TourTitle;
        $tour->title = $request->name;
        $tour->time = $request->departure;
        $tour->save();

        $availabilities = $request->availabilities;

        foreach ($availabilities as $key => $value) {
            $availability = new Availability;
            $availability->tour_id = $tour->id;
            $availability->day = $value;
            $availability->save();
        }

        $info = new TourInfo;
        $info->tour_code = $request->code;
        $info->tour_id = $tour->id;
        $info->type_id = $type_id;
        $info->color = $request->color;
        
        if($request->file('file')) {
            $path = $request->file('file')->store(env('GOOGLE_DRIVE_TOURS_FOLDER_ID'));
            $url = Storage::url($path);
            $info->image_link = $url;
        }
        
        $info->save();

        $history = new TourInfoHistory;
        $history->tour_id = $info->tour_id;
        $history->save();

        $type = PaymentType::where('code', 'cash')->first();
        $rate = TourRate::updateOrCreate([
            'tour_history_id' => $history->id,
            'payment_type_id' => $type->id,
            'amount' => $request->cash ? $request->cash : 0
        ], [
            'tour_history_id' => $history->id,
            'payment_type_id' => $type->id,
            'amount' => $request->cash ? $request->cash : 0
        ]);
        
        $type = PaymentType::where('code', 'invoice')->first();
        $rate = TourRate::updateOrCreate([
            'tour_history_id' => $history->id,
            'payment_type_id' => $type->id,
            'amount' => $request->invoice ? $request->invoice : 0
        ], [
            'tour_history_id' => $history->id,
            'payment_type_id' => $type->id,
            'amount' => $request->invoice ? $request->invoice : 0
        ]);
        
        $type = PaymentType::where('code', 'payoneer')->first();
        $rate = TourRate::updateOrCreate([
            'tour_history_id' => $history->id,
            'payment_type_id' => $type->id,
            'amount' => $request->payoneer ? $request->payoneer : 0
        ], [
            'tour_history_id' => $history->id,
            'payment_type_id' => $type->id,
            'amount' => $request->payoneer ? $request->payoneer : 0
        ]);
        
        $type = PaymentType::where('code', 'paypal')->first();
        $rate = TourRate::updateOrCreate([
            'tour_history_id' => $history->id,
            'payment_type_id' => $type->id,
            'amount' => $request->paypal ? $request->paypal : 0
        ], [
            'tour_history_id' => $history->id,
            'payment_type_id' => $type->id,
            'amount' => $request->paypal ? $request->paypal : 0
        ]);
        
        if($request->type === 'small') {
            $participant_rate = TourParticipantRate::updateOrCreate([
                'tour_history_id' => $history->id,
                'participant_type_id' => ParticipantType::where('code', 'adult')->first()->id,
                'amount' => $request->adult
            ], [
                'tour_history_id' => $history->id,
                'participant_type_id' => ParticipantType::where('code', 'adult')->first()->id,
                'amount' => $request->adult
            ]);
            
            $participant_rate = TourParticipantRate::updateOrCreate([
                'tour_history_id' => $history->id,
                'participant_type_id' => ParticipantType::where('code', 'child')->first()->id,
                'amount' => $request->children
            ], [
                'tour_history_id' => $history->id,
                'participant_type_id' => ParticipantType::where('code', 'child')->first()->id,
                'amount' => $request->children
            ]);
        }

        $duration = TourDuration::updateOrCreate([
            'tour_history_id' => $history->id,
            'duration_day' => $request->duration_day,
            'duration_time' => $request->duration
        ], [
            'tour_history_id' => $history->id,
            'duration_day' => $request->duration_day,
            'duration_time' => $request->duration
        ]);

        return response()->json($info);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id = null)
    {
        $isAdmin = Auth::user()->access_levels()->whereHas('info', function($q) {
            $q->where('code', 'admin');
            })->first() ? true : false;

        if($id && $isAdmin) {
            $tour = TourTitle::with('info.type', 'availabilities')->find($id);
    
            $types = TourType::all();

            return view('tours.edit')->with(['types' => $types, 'tour' => $tour]);
        }
        
        $tours = TourTitle::with([
            'info.type', 
            'histories',
            'availabilities']);

        if($request->type) {
            $tours->whereHas('info', function($q) use ($request) {
                $q->whereHas('type', function($q) use ($request) {
                    $q->where('id', $request->type);
                });
            });
        }

        if(!$isAdmin) $tours->whereNull('suspended_at');

        return response()->json($tours->get());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'code' => 'required|max:100',
            'type' => 'required|exists:tour_types,code',
            'departure' => 'required',
            'color' => 'required_if:type,small',
            'cash' => 'required|numeric|gt:0|lt:1000000',
            'invoice' => 'required|numeric|gt:0|lt:1000000',
            'payoneer' => 'nullable|numeric|lt:1000000',
            'paypal' => 'nullable|numeric|lt:1000000',
            'adult' => 'required_if:type,small|numeric|gt:0|lt:1000000',
            'children' => 'required_if:type,small|numeric|gt:0|lt:1000000',
            'duration_day' => 'required|numeric',
            'duration' => 'required|date_format:H:i',
        ]);

        $validator->after(function ($validator) use ($id, $request) {
            if ($request->type === 'small' && TourInfo::where('tour_id', '!=', $id)->where('color', $request->color)->first()) {
                $validator->errors()->add('color', 'The color has already been taken');
            }
        });

        $validator->after(function ($validator) use ($id, $request) {
            if (TourInfo::where('tour_id', '!=', $id)->where('tour_code', $request->code)->first()) {
                $validator->errors()->add('code', 'The code has already been taken');
            }
        });

        $validator->validate();

        if($request->hasFile('file'))
        $request->validate([
            'file' => 'image|max:10240'
        ]);

        $url = null;

        $type_id = TourType::where('code', $request->type)->first()->id;

        $tour = TourTitle::find($id);

        $tour->title = $request->name;
        $tour->time = $request->departure;
        $tour->save();

        $availabilities = $request->availabilities;

        foreach ($availabilities as $key => $value) {
            $tour_availabilities = $tour->availabilities();

            $availability = $tour_availabilities->updateOrCreate(
                                    ['tour_id' => $tour->id, 'day' => $value],
                                    ['tour_id' => $tour->id, 'day' => $value]
                                );
        }

        $infoData = [
            'tour_id' => $tour->id,
            'type_id' => $type_id,
            'tour_code' => $request->code,
            'color' => $request->color
        ];
        
        if($request->hasFile('file')) {
            $path = $request->file('file')->store(env('GOOGLE_DRIVE_TOURS_FOLDER_ID'));
            $url = Storage::url($path);
            $infoData['image_link'] = $url;
        }

        $info = TourInfo::updateOrCreate(
            ['tour_id' => $tour->id],
            $infoData
        );

        $history = new TourInfoHistory;
        $history->tour_id = $info->tour_id;
        $history->save();
        
        $type = PaymentType::where('code', 'cash')->first();
        $rate = TourRate::updateOrCreate([
            'tour_history_id' => $history->id,
            'payment_type_id' => $type->id,
            'amount' => $request->cash ? $request->cash : 0
        ], [
            'tour_history_id' => $history->id,
            'payment_type_id' => $type->id,
            'amount' => $request->cash ? $request->cash : 0
        ]);
        
        $type = PaymentType::where('code', 'invoice')->first();
        $rate = TourRate::updateOrCreate([
            'tour_history_id' => $history->id,
            'payment_type_id' => $type->id,
            'amount' => $request->invoice ? $request->invoice : 0
        ], [
            'tour_history_id' => $history->id,
            'payment_type_id' => $type->id,
            'amount' => $request->invoice ? $request->invoice : 0
        ]);
        
        $type = PaymentType::where('code', 'payoneer')->first();
        $rate = TourRate::updateOrCreate([
            'tour_history_id' => $history->id,
            'payment_type_id' => $type->id,
            'amount' => $request->payoneer ? $request->payoneer : 0
        ], [
            'tour_history_id' => $history->id,
            'payment_type_id' => $type->id,
            'amount' => $request->payoneer ? $request->payoneer : 0
        ]);
        
        $type = PaymentType::where('code', 'paypal')->first();
        $rate = TourRate::updateOrCreate([
            'tour_history_id' => $history->id,
            'payment_type_id' => $type->id,
            'amount' => $request->paypal ? $request->paypal : 0
        ], [
            'tour_history_id' => $history->id,
            'payment_type_id' => $type->id,
            'amount' => $request->paypal ? $request->paypal : 0
        ]);
        
        if($request->type === 'small') {
            $participant_rate = TourParticipantRate::updateOrCreate([
                'tour_history_id' => $history->id,
                'participant_type_id' => ParticipantType::where('code', 'adult')->first()->id,
                'amount' => $request->adult
            ], [
                'tour_history_id' => $history->id,
                'participant_type_id' => ParticipantType::where('code', 'adult')->first()->id,
                'amount' => $request->adult
            ]);
            
            $participant_rate = TourParticipantRate::updateOrCreate([
                'tour_history_id' => $history->id,
                'participant_type_id' => ParticipantType::where('code', 'child')->first()->id,
                'amount' => $request->children
            ], [
                'tour_history_id' => $history->id,
                'participant_type_id' => ParticipantType::where('code', 'child')->first()->id,
                'amount' => $request->children
            ]);
        }

        $duration = TourDuration::updateOrCreate([
            'tour_history_id' => $history->id,
            'duration_day' => $request->duration_day,
            'duration_time' => $request->duration
        ], [
            'tour_history_id' => $history->id,
            'duration_day' => $request->duration_day,
            'duration_time' => $request->duration
        ]);

        return response()->json($info);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tour = TourTitle::find($id);

        $tour->delete();

        return response()->json('Success');
    }

    public function suspend($id) {
        $tour = TourTitle::find($id);
        $tour->suspended_at = $tour->suspended_at ? null : Carbon::now();
        $tour->save();

        return response()->json($tour);
    }

    public function profile($id) {
        $isAdmin = Auth::user()->access_levels()->whereHas('info', function($q) {
            $q->where('code', 'admin');
            })->first() ? true : false;
        $tour = TourTitle::with('info.type')->find($id);

        return view('tours.profile')->with(['tour' => $tour, 'isAdmin' => $isAdmin]);
    }

    public function description(Request $request, $id) {
        $tour = TourTitle::find($id);
        $tourInfo = $tour->info()->first();
        $tourInfo->description = $request->description;
        $tourInfo->save();

        return response()->json($tour);
    }

    public function tourRateUpdate(Request $request) {
        $schedule = Schedule::find($request->id);
        $departure = $schedule->departure()->first();
        $departure->custom_rate = $request->rate;
        $departure->save();

        return json_encode($departure);
    }

    public function tourDepartureRateUpdate(Request $request) {
        $request->validate([
            'id' => 'required|exists:tour_departures'
        ]);

        if($request->departure) {
            // $request->validate([
            //     'departure' => 'datetime'
            // ]); 
        }

        $departure = TourDeparture::find($request->id);
        $departure->custom_rate = $request->custom_rate;
        $departure->departure = $request->departure;
        $departure->save();

        return json_encode($departure);
    }

    public function availableTour(Request $request, $date) {
        $request->validate([
            'date' => 'required|date|after:today'
        ]);

        $date = Carbon::parse($request->date);
        
        $tour = TourTitle::whereHas('availabilities', function($q) use ($date) {
                    $q->where('day', $date->format('l'));
                })
                ->get();

        return response()->json($tour);
    }

}
