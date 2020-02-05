<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterTourGuide; 
use App\Mail\ApproveSubscription; 
use App\Models\UserAccessLevel;
use App\Models\AccessLevel;
use App\Models\UserInfo;
use App\Models\PaymentType;
use App\Models\UserLanguage;
use App\Models\Language;
use App\User;
use Carbon\Carbon;
use Validator;
use Auth;

class TourGuideController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $payments = PaymentType::all();

        return view('profile.tour_guides', compact('payments'));
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
    public function show($id = null, Request $request)
    {
        if($request->languages) {
            $request->validate([
                'languages' => 'required|array',
                'languages.*' => 'exists:languages,id'
            ]);
        }

        $tour_guides = User::with('info')
                    ->whereHas('access_levels', function($q) {
                        $q->where('access_level_id', 2);
                    })
                    ->when($request->languages, function($q) use ($request) {
                        $q->whereHas('languages', function($q) use ($request) {
                            $q->whereHas('language', function($q) use ($request) {
                                $q->whereIn('id', $request->languages);
                            });
                        });
                    })
                    ->get();

        return response()->json($tour_guides);
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
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $user = User::find($id);
        
        if ($user->accepted_at) {
            $user->accepted_at = null;
            $user->save();
        } else {
            $user->accepted_at = Carbon::now();

            if($user->save())
            Mail::send(new ApproveSubscription($user));
        }

        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id)->info();

        return $user->delete();
    }
    
    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        return view('profile.register');
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users']
        ])->validate();

        event(new Registered($user = $this->createGuide($request->all())));

        $request->session()->flash('status', 'Tour guide was added successfully!');

        return back();
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    public function createGuide(array $data)
    {
        $info = UserInfo::create([
            'first_name' => $data['first_name'],
            'middle_ename' => $data['middle_name'],
            'last_name' => $data['last_name']
        ]);

        $data['password'] = Str::random(8);

        $user = User::create([
            'user_info_id' => $info->id,
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'accepted_at' => Carbon::now()
        ]);

        $access_level = AccessLevel::where('code', 'tg')->first();

        $access = UserAccessLevel::create([
            'user_id' => $user->id,
            'access_level_id' => $access_level->id
        ]); 

        $default_language = Language::where('alpha2', 'en')->first();

        if($default_language) {
            UserLanguage::create([
                'user_id' => $user->id,
                'language_id' => $default_language->id
            ]);
        }

        $user['readable_password'] = $data['password'];

        Mail::send(new RegisterTourGuide($user));

        return $user;
    }

    public function profile($id) {
        $isAdmin = Auth::user()->access_levels()->whereHas('info', function($q) {
            $q->where('code', 'admin');
            })->first() ? true : false;
        $guide = User::with('info', 'languages')->find($id);

        return view('guide.profile')->with(['guide' => $guide, 'isAdmin' => $isAdmin]);
    }

    public function rating(Request $request, $id) {
        $request->validate([
            'rating' => 'required|numeric|min:0'
        ]);

        $user = User::find($id);
        $userInfo = $user->info()->first();
        $userInfo->rating = $request->rating;
        $userInfo->save();

        return response()->json($userInfo);
    }

    public function updateLanguage(Request $request, $id) {
        $request->validate([
            'languages' => 'required|array'
        ]);

        $languages = $request->languages;

        $user = User::find($id);

        if(!$user) {
            return response()->json('User does not exists', 404);
        }

        $userLang = $user->languages();

        $userLang->delete();

        foreach ($languages as $key => $value) {
            if($value) {
                $userLang->create([
                    'language' => $value
                ]);
            }
        }

        return response()->json($userLang->get());
    }

    public function updateContact(Request $request, $id) {
        $request->validate([
            'contact' => 'required'
        ]);

        $contact = $request->contact;

        $user = User::find($id);

        if(!$user) {
            return response()->json('User does not exists', 404);
        }
        
        $userInfo = $user->info()->first();
        $userInfo->contact_number = $contact;
        $userInfo->save();

        return response()->json($userInfo);
    }

    public function updateEmail(Request $request, $id) {
        $request->validate([
            'email' => 'required|email|unique:users'
        ]);

        $email = $request->email;

        $user = User::find($id);

        if(!$user) {
            return response()->json('User does not exists', 404);
        }
        
        $user->email = $email;
        $user->save();

        return response()->json($user);
    }

    public function updatePicture(Request $request, $id) {
        $request->validate([
            'image' => 'required|image|max:10240'
        ]);

        $contact = $request->contact;

        $user = User::find($id);

        if(!$user) {
            return response()->json('User does not exists', 404);
        }

        $path = $request->file('image')->store('/');
        $url = Storage::url($path);
        
        $userInfo = $user->info()->first();
        $userInfo->picture = $url;
        $userInfo->save();

        return response()->json($userInfo);
    }

    public function note(Request $request, $id) {
        $user = User::find($id);
        $userInfo = $user->info()->first();
        $userInfo->note = $request->note;
        $userInfo->save();

        return response()->json($userInfo);
    }

    public function payment(Request $request, $id) {
        $request->validate([
            'id' => 'required|exists:user_infos,id',
            'payment.id' => 'required|exists:payment_types,id'
        ]);

        $info = UserInfo::find($request->id);
        $info->payment_type_id = $request->payment['id'];
        $info->save();

        return response()->json($info);
    }
}
