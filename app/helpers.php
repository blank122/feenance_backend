<?php

function setUserSessionVariables($user)
    {
        $account = DB::table('accounts')
        ->where('acc_id', '=', $user->acc_id)
        ->first();

        //Set account sessions
        Session::put('acc_id', $account->acc_id);
        Session::put('acc_uuid', $account->acc_uuid);
        Session::put('acc_name', $account->acc_name);
        Session::put('acc_short_name', $account->acc_short_name);
        Session::put('acc_image', $account->acc_image);
        Session::put('acc_background', $account->acc_background);
        Session::put('acc_website', $account->acc_website);
        Session::put('acc_link', $account->acc_link);

        //Set user sessions
        Session::put('usr_id', $user->usr_id);
        Session::put('usr_uuid', $user->usr_uuid);
        Session::put('usr_last_name', $user->usr_last_name);
        Session::put('usr_first_name', $user->usr_first_name);
        Session::put('usr_middle_name', $user->usr_middle_name);
        Session::put('usr_email', $user->usr_email);
        Session::put('usr_image_path', $user->usr_image_path);
        Session::put('usr_full_name', $user->usr_first_name . ' ' . $user->usr_middle_name . ' ' . $user->usr_last_name);
        recordLogin($user->usr_id);
    }

    function recordLogin($usr_id)
    {
        DB::table('logins')
        ->insert([
            'usr_id' => $usr_id,
            'log_date' => \Carbon\Carbon::now(),
            'log_ip' => \Request::ip(),
        ]);
    }

    function unauthorize()
    {
        echo redirect('/logout');
        exit();
    }

    function sendEmail($emailSubject,$emailContent,$emailTo)
    {
        session()->put('emailTo', $emailTo);
        session()->put('emailSubject', $emailSubject);

        Mail::raw($emailContent, function($message) {
            $message
                ->to(session()->get('emailTo'), 'Infinit SMS User')
                ->subject(session()->get('emailSubject'));
            $message->from('mailer@infinitsms.com','Infinit SMS');
        });
    }

    function generateuuid()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $string = '';

        for ($i = 0; $i < 32; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;
    }

    function generateCode()
    {
        $characters = '0123456789';
        $string = '';

        for ($i = 0; $i < 5; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;
    }

    function generateDigitCode(){
        return rand(100000, 999999);
    }

    

    function getUserName($usr_id)
    {
        $user = DB::table('users')
        ->where('usr_id','=',$usr_id)
        ->first();

        if($user){
            $last_name = $user->usr_last_name;
            $first_name = $user->usr_first_name;
            $display_name = $first_name .' ' . $last_name;
            return $display_name;
        }else{
            return '';
        }
    }

   

    function getAvatar($usr_id)
    {
        try{
            $user = DB::table('users')
            ->where('usr_id','=',$usr_id)
            ->first();

            if($user->usr_image_path <> ''){
                return 'images/avatars/' . $user->usr_image_path;
            }else{
                return 'images/avatar.png';
            }
        }catch (Exception $e){
            return 'images/avatar.png';
        }
    }

    function getLastLogin($usr_id)
    {
        $login = DB::table('logins')
        ->where('usr_id','=',$usr_id)
        ->orderBy('log_date','desc')
        ->first();

        if(isset($login)){
            return $login->log_date;
        }else{
            return '-never-';
        }
    }
?>
