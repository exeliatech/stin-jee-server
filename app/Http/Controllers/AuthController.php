<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Auth\Authenticatable;
use App\User;
use Illuminate\Http\Request;
use Log;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller{

    public function register(){

    }

    public function login(Request $request){
        $email = $request->input('email');
        $password = $request->input('password');
        //Auth::loginUsingId(2)

        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            return redirect('/private');
        }

        return view('login', ['error' => 'Wrong email or password']);
    }
}