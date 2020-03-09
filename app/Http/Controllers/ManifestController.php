<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TourDeparture;
use App\Models\TourDepartureCoordinator;
use App\Models\TourTitle;
use App\Models\TourManifest;
use App\Mail\TourManifest as TourManifestMail;
use App\Mail\TourList;
use App\Mail\TourCoordinatorMail;
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
        $departures = TourDeparture::with('tour.manifest')->where('date', '2020-03-06')->get();
        $markdown = new Markdown(view(), config('mail.markdown'));
    
        return $markdown->render('emails.tours.coordinator', compact('departures'));

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
        
        $errors = [];

        if($option === 'date') {
            $request->validate([
                'date' => 'required|date|date_format:Y-m-d',
                'tour.id' => 'required|exists:tour_titles,id'
            ], [
                'tour.id.required' => 'The tour is required.',
                'tour.id.exists' => 'The selected tour does not exist.'
            ]);

            $sent = 0;

            $not_sent = 0;

            $departures = TourDeparture::where(['tour_id' => $request->tour['id'], 'date' => $request->date])->get();

            $coordinator = TourDepartureCoordinator::where(['tour_id' => $request->tour['id'], 'date' => $request->date])->first();

            if($coordinator && $coordinator->coordinator && $cEmail = $coordinator->coordinator->email) {

                Mail::to($cEmail)->send(new TourCoordinatorMail($departures));

            } else {

                $errors['coordinator'] = ['No coordinator found.'];

            }

            if(!$departures->count()) {

                $errors['title'] = ['No tour departures found.'];

                $error = \Illuminate\Validation\ValidationException::withMessages($errors);
    
                throw $error;

            }

            foreach ($departures as $key => $departure) {

                $email = $departure && $departure->schedule && $departure->schedule->user && $departure->schedule->user->email ? $departure->schedule->user->email : null;

                if(is_null($email)){ 
                    $not_sent+=1; 

                    continue;
                }

                Mail::to($email)->send(new TourList($departure));
                Mail::to($email)->send(new TourManifestMail($departure));

                $sent+=1;
            }

            if($sent && !$not_sent) {
                return response()->json([
                    'title' => 'Tour manifest sent.',
                    'sent' => [$sent.' sent.'],
                    'not_sent' => [$not_sent.' not sent - no assigned guide.']
                ]);
            } else if($sent && $not_sent) {

                $error = \Illuminate\Validation\ValidationException::withMessages([
                    'title' => ['Tour manifest sent.'],
                    'sent' => [$sent.' sent.'],
                    'not_sent' => [$not_sent.' not sent - no assigned guide.']
                ]);
    
                throw $error;

            } else {

                $error = \Illuminate\Validation\ValidationException::withMessages([
                    'title' => ['Tour manifest not sent.'],
                    'sent' => [$sent.' sent.'],
                    'not_sent' => [$not_sent.' not sent - no assigned guide.']
                ]);
    
                throw $error;

            }
        }

        $request->validate([
            'id' => 'required|exists:tour_departures'
        ]);

        $departure = TourDeparture::find($request->id);

        // $coordinator = TourDepartureCoordinator::where(['tour_id' => $departure->tour_id, 'date' => $departure->date])->first();
        
        // if($coordinator && $coordinator->coordinator && $cEmail = $coordinator->coordinator->email) {

        //     Mail::to($cEmail)->send(new TourCoordinatorMail([$departure]));

        // } else {

        //     $errors['coordinator'] = ['No coordinator found.'];

        // }

        $email = $departure && $departure->schedule && $departure->schedule->user && $departure->schedule->user->email ? $departure->schedule->user->email : null;

        if(is_null($email)) {

            $errors['manifest'] = ['No assigned tour guide.'];

            $error = \Illuminate\Validation\ValidationException::withMessages($errors);

            throw $error;

        }

        Mail::to($email)->send(new TourList($departure));
        Mail::to($email)->send(new TourManifestMail($departure));

        return response()->json([
            'title' => 'Tour manifest sent!'
        ]);
    }
}
