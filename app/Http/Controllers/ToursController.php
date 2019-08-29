<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\TourType;
use App\Models\TourInfo;
use App\Models\TourTitle;
use App\Models\Availability;
use Validator;

class ToursController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('tours.index');
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
            'payoneer' => 'required|numeric|gt:0|lt:1000000',
            'paypal' => 'required|numeric|gt:0|lt:1000000',
            'adult' => 'required_if:type,small|numeric|gt:0|lt:1000000',
            'children' => 'required_if:type,small|numeric|gt:0|lt:1000000',
            'duration_day' => 'required|numeric',
            'duration' => 'required|date_format:H:i',
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

        $availabilities = json_decode($request->availabilities);

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

        $info->cash = $request->cash;
        $info->invoice = $request->invoice;
        $info->payoneer = $request->payoneer;
        $info->paypal = $request->paypal;
        $info->adult_price = $request->adult;
        $info->children_price = $request->children;
        $info->duration_day = $request->duration_day;
        $info->duration_time = $request->duration;
        $info->save();

        return response()->json($info);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id = null)
    {
        if($id) {
            $tour = TourTitle::with('info.type', 'availabilities')->find($id);
    
            $types = TourType::all();

            return view('tours.edit')->with(['types' => $types, 'tour' => $tour]);
        }
        
        $tours = TourTitle::with('info.type', 'availabilities')->get();

        return response()->json($tours);
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
            'payoneer' => 'required|numeric|gt:0|lt:1000000',
            'paypal' => 'required|numeric|gt:0|lt:1000000',
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

        $availabilities = json_decode($request->availabilities);

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
            'color' => $request->color,
            'cash' => $request->cash,
            'invoice' => $request->invoice,
            'payoneer' => $request->payoneer,
            'paypal' => $request->paypal,
            'adult_price' => $request->adult,
            'children_price' => $request->children,
            'duration_day' => $request->duration_day,
            'duration_time' => $request->duration
        ];
        
        if($request->hasFile('file')) {
            $path = $request->file('file')->store(env('GOOGLE_DRIVE_TOURS_FOLDER_ID'));
            $url = Storage::url($path);
            $infoData['image_link'] = $url;
        }

        $info = $tour->info()->updateOrCreate(
            ['tour_id' => $tour->id],
            $infoData
        );

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
        //
    }
}
