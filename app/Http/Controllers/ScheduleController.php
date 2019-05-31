<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\User;
use Carbon\Carbon;
use Auth;

class ScheduleController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $validatedData = $request->validate([
            'id' => 'required|exists:users|numeric',
            'schedule_at' => 'required|date_format:Y-m-d',
            'shift' => 'required|in:Morning,Afternoon,Evening'
        ]);

        $schedule = Schedule::create([
            'user_id' => $validatedData['id'],
            'available_at' => $validatedData['schedule_at'],
            'shift' => $validatedData['shift'],
        ]);

        return response()->json($schedule);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $date
     * @return \Illuminate\Http\Response
     */
    public function show($date = null)
    {
        $date = $date ? $date : Carbon::now()->format('Y-m-d');

        $user = Auth::user();

        $isAdmin = Auth::user()->access_levels()->whereHas('info', function($q) {
            $q->where('code', 'admin');
            })->first();

        $morning = $this->getSchedules($date, 'Morning', $isAdmin);

        $afternoon = $this->getSchedules($date, 'Afternoon', $isAdmin);

        $evening = $this->getSchedules($date, 'Evening', $isAdmin);

        $pending = $isAdmin ? Schedule::where('available_at', $date)->where('flag', 0)->count() : $user->schedules()->where('available_at', $date)->where('flag', 0)->count();

        $scheduled = $isAdmin ? Schedule::where('available_at', $date)->where('flag', 1)->count() : $user->schedules()->where('available_at', $date)->where('flag', 1)->count();

        $data = array(
            'schedules' => array(
                'pending' => $pending, 
                'scheduled' => $scheduled 
            ), 
            'tour_guides' => array(
                'morning' => $morning ? $morning : array($user), 
                'afternoon' => $afternoon ? $afternoon : array($user), 
                'evening' => $evening ? $evening : array($user)
            ),
            'date' => $date ? $date : $now,
            'isAdmin' => $isAdmin ? true : false
        );
        
        return response()->json($data);
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
        $request->validate([
            'flag' => 'required|numeric|in:0,1'
        ]);

        $schedule = Schedule::find($id);

        $schedule->flag = $request->flag;
        
        $schedule->save();

        return response()->json($schedule);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $schedule = Schedule::where(['id' => $id, 'flag' => 0])->first();

        return $schedule ? response()->json(Schedule::destroy($id)) : response()->json(['error' => "You can't un-flagged this schedule. It is already confirmed."], 401);
    }

    public function getSchedules($date, $shift, $isAdmin = false) {
        $user = User::with(['schedules' => function($q) use ($date, $shift){
            $q->where('available_at', $date)->where('shift', $shift);
        }])->withCount(['schedules' => function($q) use ($date, $shift){
            $q->where('available_at', $date)->where('shift', $shift);
        }]);

        $user = $isAdmin ? $user->whereHas('schedules', function($q) use ($date, $shift){
            $q->where('available_at', $date)->where('shift', $shift);
        })->get() : array($user->where('id', Auth::id())->first()->setAttribute('flag', 0)->setAttribute('schedule_at', $date));

        return $user;
    }
}
