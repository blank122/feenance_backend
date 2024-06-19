<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $usr_mobile = $request->usr_mobile;
        $usr_email = $request->usr_email;
        $usr_first_name = $request->usr_first_name;
        $usr_middle_name = $request->usr_middle_name;
        $usr_last_name = $request->usr_last_name;
        $code = generateDigitCode();

        $user = DB::table('users')
            ->where('usr_email', '=', $usr_email)
            ->where('acc_id', '=', session('acc_id'))
            ->first();

        if ($user) {
            alert()->error('A user with the same email ' . $usr_email . ' already exists.');
        } else {
            DB::table('users')
                ->insert([
                    'usr_uuid' => generateuuid(),
                    'acc_id' => session('acc_id'),
                    'usr_mobile' => $usr_mobile,
                    'usr_email' => $usr_email,
                    'usr_password' => md5($code),
                    'usr_first_name' => $usr_first_name,
                    'usr_middle_name' => $usr_middle_name,
                    'usr_last_name' => $usr_last_name,
                    'usr_date_created' => Carbon::now(),
                    'usr_is_admin' => '1',
                    'usr_code' => $code
                ]);

            $emailSubject = 'New System User';
            $emailContent = 'Welcome to Equipment Borrowing System. To login, use your email address and the following temporary password ' . $code . '.';
            $emailTo = $usr_email;

            sendEmail($emailSubject, $emailContent, $emailTo);

            alert()->info('New user has been added. A temporary password has been sent to ' . $usr_email . '.');
        }

        return redirect()->action([UserController::class, 'active']);
    }

    public function verify($code)
    {
        $user = DB::table('users')
            ->where('usr_email_activation_code', '=', $code)
            ->first();

        if ($user) {
            if ($user->usr_is_verified == '0') {
                DB::table('users')
                    ->where('usr_email_activation_code', '=', $code)
                    ->update([
                        'usr_date_verified' => Carbon::now(),
                        'usr_is_verified' => '1'
                    ]);

                $user = DB::table('users')
                    ->where('usr_email_activation_code', '=', $code)
                    ->first();

                resetUserLoginCounter($user->usr_email);
                setUserSessionVariables($user);
                return redirect()->action([MainController::class, 'welcome']);
            } else {
                alert()->error('User is already verified', 'This user has already been verified. Please login to continue.');
                return redirect()->action([MainController::class, 'main']);
            }
        } else {
            alert()->error('Invalid Verification Code', 'Invalid user verification code.');
            return redirect()->action([MainController::class, 'main']);
        }
    }

    public function updatePassword(Request $request)
    {
        $usr_password1 = $request->usr_password1;
        $usr_password2 = $request->usr_password2;
        $usr_uuid = session('usr_uuid');

        if ($usr_password1 == $usr_password2) {
            DB::table('users')
                ->where('usr_uuid', '=', $usr_uuid)
                ->update([
                    'usr_password' => md5($usr_password1)
                ]);

            alert()->info('Success', 'Your password was successfully changed.');
            return redirect()->action([MainController::class, 'home']);
        } else {
            DB::table('users')
                ->where('usr_uuid', '=', $usr_uuid)
                ->update([
                    'usr_password' => md5($usr_password1)
                ]);

            alert()->error('Password Not Changed', 'Password did not match!');
            return redirect()->back();
        }
    }

    public function forgotPassword(Request $request)
    {
        $usr_email = $request->usr_email;
        $code = generateDigitCode();

        DB::table('users')
            ->where('usr_email', '=', $usr_email)
            ->update([
                'usr_password_reset_code' => $code,
                'usr_password_reset_allowed' => '1'
            ]);

        $reset_url = env('APP_URL') . '/user/reset/' . $code;
        $emailSubject = 'GRIND password reset';
        $emailContent = 'To reset you password, please go to the following link: ' . $reset_url . '.';
        $emailTo = $usr_email;

        sendEmail($emailSubject, $emailContent, $emailTo);

        alert()->info('Check Your E-mail', 'An e-mail password reset link has been sent to ' . $usr_email . '. Please login to your e-mail to complete the password reset process.');
        return redirect()->action([MainController::class, 'main']);
    }

    public function reset($usr_uuid)
    {
        $code = generateDigitCode();

        $user = DB::table('users')
            ->where('usr_uuid', '=', $usr_uuid)
            ->first();

        $usr_email = $user->usr_email;

        DB::table('users')
            ->where('usr_uuid', '=', $usr_uuid)
            ->update([
                'usr_password' => md5($code),
                'usr_code' => $code
            ]);

        $emailSubject = 'Infinit SMS password reset';
        $emailContent = 'An administrator has reset your password. Your new password is ' . $code . '.';
        $emailTo = $usr_email;

        sendEmail($emailSubject, $emailContent, $emailTo);

        alert()->info('Password has been reset', 'A new password has been generated and sent to ' . $usr_email . '.');
        return redirect()->action([UserController::class, 'active']);
    }

    public function update(Request $request)
    {
        $usr_uuid = $request->usr_uuid;
        $usr_email = $request->usr_email;
        $usr_first_name = $request->usr_first_name;
        $usr_middle_name = $request->usr_middle_name;
        $usr_last_name = $request->usr_last_name;
        $usr_birth_date = $request->usr_birth_date;

        DB::table('users')
            ->where('usr_uuid', '=', $usr_uuid)
            ->update([
                'usr_email' => $usr_email,
                'usr_first_name' => $usr_first_name,
                'usr_middle_name' => $usr_middle_name,
                'usr_last_name' => $usr_last_name,
                'usr_birth_date' => $usr_birth_date,
                'usr_date_modified' => Carbon::now()
            ]);

        $user = DB::table('users')
            ->where('usr_uuid', '=', $usr_uuid)
            ->first();

        setUserSessionVariables($user);

        alert()->success('Success', 'User information has been updated.');
        return redirect()->action([MainController::class, 'home']);
    }

    public function updatePassword2(Request $request)
    {
        $current_password = $request->current_password;
        $new_password1 = $request->new_password1;
        $new_password2 = $request->new_password2;

        $user = DB::table('users')
            ->where('usr_id', '=', session('usr_id'))
            ->first();

        if (md5($current_password) == $user->usr_password) {
            if ($new_password1 == $new_password2) {

                DB::table('users')
                    ->where('usr_id', '=', session('usr_id'))
                    ->update([
                        'usr_password' => md5($new_password1)
                    ]);

                alert()->success('Success', 'User password has been changed.');
            } else {
                alert()->warning('Warning', 'Password did not matched.');
            }
        } else {
            alert()->warning('Warning', 'Incorrect user password.');
        }
        return redirect()->action([MainController::class, 'home']);
    }

    public function active()
    {
        $mode = 'active';

        $users = DB::table('users')
            ->where('usr_active', '=', '1')
            ->where('acc_id', '=', session('acc_id'))
            ->orderby('usr_last_name')
            ->orderby('usr_first_name')
            ->get();

        return view('users.current', compact('users', 'mode'));
    }

    public function inactive()
    {
        $mode = 'inactive';

        $users = DB::table('users')
            ->where('usr_active', '=', '0')
            ->where('acc_id', '=', session('acc_id'))
            ->orderby('usr_last_name')
            ->orderby('usr_first_name')
            ->get();

        return view('users.current', compact('users', 'mode'));
    }

    public function activate($usr_uuid)
    {
        DB::table('users')
            ->where('usr_uuid', '=', $usr_uuid)
            ->update([
                'usr_active' => '1'
            ]);

        alert()->success('Success', 'User has been activated.');
        return redirect()->back();
    }

    public function deactivate($usr_uuid)
    {
        DB::table('users')
            ->where('usr_uuid', '=', $usr_uuid)
            ->update([
                'usr_active' => '0'
            ]);

        alert()->success('Success', 'User has been deactivated.');
        return redirect()->back();
    }

    public function addAdmin($usr_uuid)
    {
        DB::table('users')
            ->where('usr_uuid', '=', $usr_uuid)
            ->update([
                'usr_is_admin' => '1'
            ]);

        alert()->success('Success', 'User has been set as admin.');
        return redirect()->back();
    }

    public function removeAdmin($usr_uuid)
    {
        DB::table('users')
            ->where('usr_uuid', '=', $usr_uuid)
            ->update([
                'usr_is_admin' => '0'
            ]);

        alert()->success('Success', 'User has been set as regular user.');
        return redirect()->back();
    }
}
