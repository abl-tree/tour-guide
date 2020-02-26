<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TourDeparture;
use App\Models\TourTitle;
use App\Models\TourManifest;
use App\Mail\TourManifest as TourManifestMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Markdown;

class ManifestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        abort(404);
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
        $request->validate([
            'content' => 'required',
            'tour.id' => 'required|exists:tour_titles,id'
        ]);

        $tour_manifest = TourManifest::updateOrCreate([
            'tour_title_id' => $request->tour['id']
        ], [
            'content' => $request->content
        ]);

        return $tour_manifest;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $departure = TourDeparture::with('tour.manifest')->find($id);
        $markdown = new Markdown(view(), config('mail.markdown'));
    
        return $markdown->render('emails.tours.lists', compact('departure'));

        return Mail::to('ablamparas@gmail.com')->send(new TourManifestMail());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $tour = TourTitle::with('manifest')->find($id);

        if(!$tour) abort(403, 'Tour not found');

        return view('manifest.index', compact('tour'));
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

    public function send(Request $request, $option = null) {

        if($option === 'date') {
            
        }

        return $request->all();
    }
}
