<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class MainController extends Controller
{
    public function home()
    {
        $logins = DB::table('logins')
        ->select('users.usr_last_name','users.usr_first_name','users.usr_image_path','logins.*',DB::raw('max(logins.log_date) as log_date_max'))
        ->join('users','users.usr_id','logins.usr_id')
        ->orderBy('log_date_max','desc')
        ->groupby('logins.usr_id')
        ->limit(10)
        ->get();

        $announcements = DB::table('announcements')
        ->join('users','users.usr_id','announcements.ann_created_by')
        ->where('ann_active','=','1')
        ->orderBy('ann_date_created','DESC')
        ->get();

        return view('home',compact('logins','announcements'));
    }
}
