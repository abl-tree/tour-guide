<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gender;
use App\User;
use Validator;
use Auth;
use Hash;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $gender = Gender::all();

        return view('profile.change_password', compact('gender'));
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
        return redirect()->back();
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
        $this->validator($request->all())->validate();

        $user = User::find($id);

        $user->username = $request->username;

        $user->email = $request->email;

        $request->password ? $user->password = $request->password : null;

        $user->save();

        $info = $user->info()->first();

        $info->first_name = $request->first_name;

        $info->middle_name = $request->middle_name;

        $info->last_name = $request->last_name;

        $info->birthdate = $request->birthdate;

        $info->gender_id = $request->gender;

        $info->save();

        return redirect()->back()->with('success', 'Profile has been updated.');
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

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $validator = Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'numeric', 'max:255'],
            'birthdate' => ['required', 'date', 'max:255', 'date_format:Y-m-d'],
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'current_password' => ['required', 'string', 'min:8'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        
        $validator->after(function ($validator) use ($data) {
            if (Auth::user()->username != $data['username'] && User::where('username', $data['username'])->count() > 0) {
                $validator->errors()->add('username', 'The username has already been taken.');
            }

            if (Auth::user()->email != $data['email'] && User::where('email', $data['email'])->count() > 0) {
                $validator->errors()->add('email', 'The username has already been taken.');
            }

            if (!Hash::check($data['current_password'], Auth::user()->password)) {
                $validator->errors()->add('current_password', 'The current password is incorrect.');
            }
        });

        return $validator;
    }
}
