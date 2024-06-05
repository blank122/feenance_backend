<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Log;
use Hash;

class ApiController extends Controller
{
    //
    public function loginApi(Request $request)
    {
        $usr_email = $request->email;
        $usr_password = $request->password;

        $user = DB::table('users')
            ->join('accounts', 'accounts.acc_id', '=', 'users.acc_id')
            ->where('users.usr_email', '=', $usr_email)
            ->where('users.usr_password', '=', ($usr_password))
            ->where('users.usr_active', '=', '1')
            ->where('accounts.acc_active', '=', '1')
            ->first();

        if (!$user) {
            return response()->json(['message' => 'The provided credentials are incorrect.', 401]);
        }
        // $token = $user->generateToken('personal_access_token')->plainTextToken;
        $token = $this->generateToken($user->usr_id);

        return response()->json([
            'message' => 'Login successfully',
            'Personal Access Token' => $token,
            'status' => 200,
            'usr_id' => $user->usr_id,
            'email' => $usr_email,
            'user' => $user,
        ]);

    }



    public function logoutApi(Request $request)
    {
        $token = $request->bearerToken();

        if ($token && $this->deleteToken($token)) {
            return response()->json(['message' => 'Logged out successfully.'], 200);
        }

        return response()->json(['message' => 'Unauthenticated.'], 401);

    }

    private function generateToken($userId)
    {
        // Generate a token
        $plainTextToken = Str::random(60);
        $hashedToken = hash('sha256', $plainTextToken);

        // Store the token in the database
        DB::table('personal_access_token')->insert([
            'tokenable_type' => 'App\Models\User',
            'tokenable_id' => $userId,
            'name' => 'auth_token',
            'token' => $hashedToken,
            'abilities' => json_encode(['*']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $plainTextToken;
    }

    private function deleteToken($token)
    {
        $hashedToken = hash('sha256', $token);

        return DB::table('personal_access_token')
            ->where('token', $hashedToken)
            ->delete();
    }

    public function checkTable()
    {
        $results = DB::select('SELECT * FROM personal_access_token');
        return response()->json(['message' => 'Unauthenticated.', 'tokens' => $results], 401);
    }

    public function updatePasswordApi(Request $request)
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

            return response()->json(['message' => 'Password Updated Successfully.'], 200);

        }
    }


    public function updateUserApi(Request $request, $id)
    {
        $usr_id = $id;
        $usr_mobile = $request->usr_mobile;
        $usr_email = $request->usr_email;
        $usr_first_name = $request->usr_first_name;
        $usr_middle_name = $request->usr_middle_name;
        $usr_last_name = $request->usr_last_name;
        // $code = generateDigitCode();


        $user = DB::table('users')
            ->where('usr_id', '=', $usr_id)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 400);
        } else {
            $updatedUser = DB::table('users')
                ->where('usr_id', '=', $id)
                ->update(
                    array(
                        'usr_mobile' => $usr_mobile,
                        'usr_email' => $usr_email,
                        'usr_first_name' => $usr_first_name,
                        'usr_middle_name' => $usr_middle_name,
                        'usr_last_name' => $usr_last_name,
                        'usr_date_created' => Carbon::now(),
                        // 'usr_is_admin' => '1',
                    )
                );
            return response()->json([
                'response' => 200,
                'message' => 'User information has been updated.',
                'users' => $updatedUser
            ]);
        }

    }

    public function getUsers(Request $request)
    {
        $users = DB::table('users')->select([
            'users.*'
        ])->get();



        return response()->json(['message' => 'Users successfully', 'users' => $users, 200]);

    }

    public function findUser(Request $request, $id)
    {

        // Get the authenticated user from the request
        $user = DB::table('users')
            ->where('usr_id', '=', $id)
            ->get();

        if ($user) {
            return response()->json([
                'message' => 'User fetched successfully',
                'user' => $user,
            ], 200);
        } else {
            return response()->json([
                'message' => 'User not authenticated',
            ], 401);
        }
    }

    public function testApi(Request $request)
    {
        try {
            $usr_email = $request->input('email');

            if (!$usr_email) {
                throw new \Exception('Email not provided');
            }

            return response()->json(['message' => 'Users successfully retrieved', 'email' => $usr_email], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

    }

    public function storeGoogleInfoAPI(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'google_id' => 'required|string',
            'google_email' => 'required|string|email',
            'google_name' => 'required|string',
            'google_avatar' => 'required|string'
        ]);

        // Check if the user exists and google_id is not empty
        $user = DB::table('users')->where('google_id', $request->google_id)->first();

        if ($user) {
            // If google_id is already present, just generate a token
            if (!empty($user->google_id)) {
                $token = $this->generateToken($user->usr_id);

                return response()->json([
                    'message' => 'User already exists. Token generated.',
                    'user' => $user,
                    'token' => $token,
                ], 200);
            }

            // Update existing user if google_id was empty
            DB::table('users')->where('google_id', $request->google_id)->update([
                'google_email' => $request->google_email,
                'google_name' => $request->google_name,
                'google_avatar' => $request->google_avatar,
                'updated_at' => now(),
            ]);

            // Generate a token for the updated user
            $token = $this->generateToken($user->usr_id);

            return response()->json([
                'message' => 'User details updated successfully',
                'user' => DB::table('users')->where('usr_id', $user->usr_id)->first(),
                'token' => $token,
            ], 200);
        } else {
            // Generate a temporary password
            $temporaryPassword = Str::random(10);

            // Create new user
            $userId = DB::table('users')->insertGetId([
                'usr_uuid' => generateuuid(),
                'acc_id' => '1',
                'usr_mobile' => ' ',
                'usr_email' => $request->google_email,
                'usr_password' => md5($temporaryPassword),
                'usr_first_name' => $request->google_name,
                'usr_middle_name' => ' ',
                'usr_last_name' => ' ',
                'usr_date_created' => Carbon::now(),
                'google_id' => $request->google_id,
                'google_email' => $request->google_email,
                'google_name' => $request->google_name,
                'google_avatar' => $request->google_avatar,
                'role_id' => '4',
                'usr_active' => '1',
                'usr_date_modified' => Carbon::now()
            ]);

            // Generate a token for the new user
            $token = $this->generateToken($userId);

            // Return the response with the token and temporary password
            return response()->json([
                'message' => 'User details saved successfully',
                'user' => DB::table('users')->where('usr_id', $userId)->first(),
                'token' => $token,
                'temporary_password' => $temporaryPassword,
            ], 200);
        }
    }


    public function linkAccount(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'google_id' => 'required|string|unique:users,google_id',
            'google_email' => 'required|string|email|unique:users,google_email',
            'google_name' => 'required|string',
            'user_id' => 'required|integer|exists:users,id'
        ]);

        // Retrieve the user
        $user = DB::table('users')->where('id', $request->user_id)->first();

        // Check if the user's Google account is already linked
        if ($user->google_id || $user->google_email || $user->google_name) {
            return response()->json([
                'message' => 'This account is already linked to a Google account',
            ], 400);
        }

        // Update the user's Google account information
        DB::table('users')->where('id', $request->user_id)->update([
            'google_id' => $request->google_id,
            'google_email' => $request->google_email,
            'google_name' => $request->google_name,
            'updated_at' => now(),
        ]);

        // Generate a token for the user
        $token = $this->generateToken($request->user_id);

        // Return the response with the token
        return response()->json([
            'message' => 'User account has been linked',
            'user' => $user,
            'token' => $token
        ], 200);
    }


    public function UnlinkedAccount()
    {
        //update the users table
        //set null the users google_id, google_avatar, google_email,


    }


}
