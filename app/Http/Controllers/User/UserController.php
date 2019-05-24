<?php

namespace App\Http\Controllers\User;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{

    public function index()
    {
        $user = User::all();
        return response()->json(['data' => $user], 200);
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);

        $data = $request->all();
        $data['password'] = bcrypt('password');
        $data['verified'] = User::UNVERIFIED_USER;
        $data['verification_token'] = User::generateVerificationToken();
        $data['admin'] = User::REGULAR_USER;

        $user = User::create($data);
        return response()->json(['data' => $user], 201);


    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $user = User::findOrfail($id);
        return response()->json(['data' => $user], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrfail($id);
        $this->validate($request, [
            'email' => 'email|unique:users,email' . $user->id,
            'password' => 'min:5|confirmed',
            'admin' => 'in:' . User::REGULAR_USER . ',' . User::ADMIN_USER,
        ]);
        if ($request->has('name')) {
            $user->name = \request('name');
        }
        if ($request->has('password')) {
            $user->password = bcrypt(\request('password'));
        }
        if ($request->has('email') && $user->email != \request('email')) {
            $user->verified = User::UNVERIFIED_USER;
            $user->verification_token = User::generateVerificationToken();
            $user->email = \request('email');
        }
        if ($request->has('admin')) {
            if (!$user->isverified()) {
                return response()->json(['error' => 'un verified'], 409);
            }
            $user->admin = \request('admin');
        }
        if ($user->isClean()) {
            return response()->json(['error'=>'update field'],422);
        }
        $user->save();
        return response()->json(['data'=>$user]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
     $user = User::findOrfail($id);
     $user->delete();
     return response()->json(['data'=>$user],200);
    }
    public function verify($token){
       $user =  User::where('verification_token',$token)->firstOrFail();
       $user->verified = User::VERIFIED_USER;
       $user->verification_token = null;
       $user->save();

        return response()->json(['data'=>'done'],200);
    }

}
