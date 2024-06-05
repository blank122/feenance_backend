<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class LoginController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function validateUser(Request $request)
    {
        $usr_email = $request->email;
        $usr_password = $request->password;

        $user = DB::table('users')
        ->join('accounts','accounts.acc_id','=','users.acc_id')
        ->where('users.usr_email', '=', $usr_email)
        ->where('users.usr_password', '=', md5($usr_password))
        ->where('users.usr_active', '=', '1')
        ->where('accounts.acc_active', '=', '1')
        ->first();

        if($user){
            setUserSessionVariables($user);
            return redirect()->action([MainController::class, 'home']);
        }else{
            alert()->error('Invalid Credentials','Invalid e-mail or password');
            return redirect()->action([LoginController::class, 'login']);
        }
    }

    public function logout()
    {
        session()->flush();
        return redirect()->action([LoginController::class, 'login']);
    }
}
