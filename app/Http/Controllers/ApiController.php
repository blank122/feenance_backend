<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Log;
use Hash;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

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
            'token' => $token,
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
                    'usr_id' => $user->usr_id,

                ], 200);
            }

            // Update existing user if google_id was empty
            DB::table('users')->where('google_id', $request->google_id)->update([
                'google_email' => $request->google_email,
                'google_name' => $request->google_name,
                'google_avatar' => $request->google_avatar,
                'usr_date_modified' => now(),
            ]);

            // Generate a token for the updated user
            $token = $this->generateToken($user->usr_id);

            return response()->json([
                'message' => 'User details updated successfully',
                'user' => DB::table('users')->where('usr_id', $user->usr_id)->first(),
                'token' => $token,
                'usr_id' => $user->usr_id,
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
                'usr_id' => $userId,
                'temporary_password' => $temporaryPassword,
            ], 200);
        }
    }


    public function linkAccount(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'google_id' => 'string|unique:users,google_id',
            'google_email' => 'string|email|unique:users,google_email',
            'google_name' => 'string',
            'google_avatar' => 'string',
        ]);

        // check if the user is already linked his account
        $user = DB::table('users')->where('google_id', $request->google_id)->first();

        //trap the possible errors in the code
        try {
            if ($user) {
                return response()->json([
                    'message' => 'This account is already linked to a Google account',
                ], 400);
            } else {
                DB::table('users')->where('usr_id', $request->usr_id)->update([
                    'google_id' => $request->google_id,
                    'google_email' => $request->google_email,
                    'google_name' => $request->google_name,
                    'google_avatar' => $request->google_avatar,
                    'usr_date_modified' => now(),
                ]);

                return response()->json([
                    'message' => 'User account has been linked',
                    'user' => $user,
                    // 'token' => $token
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e,
            ], 500);
        }

        // Check if the user's Google account is already linked
        // if ($user->google_id || $user->google_email || $user->google_name) {
        //     return response()->json([
        //         'message' => 'This account is already linked to a Google account',
        //     ], 400);
        // }

    }


    public function UnlinkedAccount(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'google_id' => 'required|string',
            'google_email' => 'required|string|email',
            'google_name' => 'required|string',
            'google_avatar' => 'required|string',
            'usr_id' => 'required', // Ensure usr_id is validated as well
        ]);

        // Retrieve the user
        $user = DB::table('users')->where('google_id', $request->google_id)->first();
        $usrID = DB::table('users')->where('usr_id', $request->usr_id)->first();

        // Ensure user and usrID are retrieved correctly
        if ($user && $usrID) {
            // Check if the user's Google account is already linked
            if ($user->google_id !== null || $user->google_email !== null || $user->google_name !== null) {
                // Update the user's Google account information
                DB::table('users')
                    ->where('usr_id', '=', $usrID->usr_id)
                    ->update([
                        'google_id' => null,
                        'google_email' => null,
                        'google_name' => null,
                        'google_avatar' => null,
                        'usr_date_modified' => Carbon::now(),
                    ]);

                return response()->json([
                    'message' => 'User account has been unlinked',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Account is not linked to Google',
                ], 400);
            }
        } else {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }
    }



    public function createExpense(Request $request)
    {
        // Validate the request data
        $request->validate([
            'exp_name' => 'required|string',
            'exp_type' => 'required|string',
            'exp_price' => 'required|integer',
        ]);

        // Insert the validated data into the expenses table
        $expenseID = DB::table('expenses')->insertGetId([
            'exp_name' => $request->input('exp_name'),
            'exp_type' => $request->input('exp_type'),
            'exp_price' => $request->input('exp_price'),
            'exp_status' => '1',
            'created_at' => Carbon::now(), // Optionally add timestamps
            'updated_at' => Carbon::now(),
        ]);


        // $expense = DB::table('expenses')->where('id', $expenseID)->get();

        // Optionally, return a response or redirect
        return response()->json([
            'message' => 'Expense created successfully',
            'expense' => $expenseID
        ], 201);
    }

    public function expenses()
    {
        $expenses = DB::table('expenses')
            ->orderBy('exp_id', 'desc')
            ->get();

        return response()->json([
            'expense' => $expenses,
            'message' => 'Fetched Expenses Successfully',

        ], 200);
    }

    // public function weeklyExpenses()
    // {
    //     // Get the start and end dates of the current week
    //     $startDate = now()->startOfWeek();
    //     $endDate = now()->endOfWeek();

    //     // Query to get total expenses within the current week
    //     $totalExpenses = DB::table('expenses')
    //         ->whereBetween('created_at', [$startDate, $endDate])
    //         ->sum('exp_price');
    //     // $totalExpenses = Expense::whereBetween('created_at', [$startDate, $endDate])->sum('exp_price');

    //     return response()->json([
    //         'data' => $totalExpenses,
    //         'message' => 'Fetched Successfully',
    //     ], 200);
    // }

    public function updateExpenses(Request $request, $id)
    {
        $exp_id = $id;
        $exp_name = $request->exp_name;
        $exp_type = $request->exp_type;
        $exp_price = $request->exp_price;



        $expense = DB::table('expenses')
            ->where('exp_id', '=', $exp_id)
            ->first();

        if (!$expense) {
            return response()->json(['message' => 'Expense with that ID not found'], 400);
        } else {
            DB::table('expenses')
                ->where('exp_id', '=', $id)
                ->update(
                    array(
                        'exp_name' => $exp_name,
                        'exp_type' => $exp_type,
                        'exp_price' => $exp_price,
                        'updated_at' => Carbon::now(),
                        // 'usr_is_admin' => '1',
                    )
                );

            // Retrieve the updated expense
            $fetchExpense = DB::table('expenses')
                ->where('exp_id', '=', $exp_id)
                ->first();

            return response()->json([
                'response' => 200,
                'message' => 'Expenses has been updated successfully.',
                'expense' => $fetchExpense
            ]);
        }
    }

    public function generateQRCode()
    {
        $jsonData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '123-456-7890'
        ];

        $jsonString = json_encode($jsonData);

        $qrCode = new QrCode($jsonString);
        $qrCode->setSize(400);

        $writer = new PngWriter();
        $qrCodeImage = $writer->write($qrCode);

        $qrCodePath = $this->saveQRCode($qrCodeImage);

        if ($qrCodePath === false) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save QR code',
            ], 500);
        } else {
            return response($qrCodePath);
        }
    }

    private function saveQRCode($qrCodeImage)
    {
        $directory = public_path('qrcodesfiles');
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                Log::error("Failed to create directory: $directory");
                return false;
            }
        }

        $path = $directory . DIRECTORY_SEPARATOR . uniqid() . '.png';

        // Ensure the directory is writable
        if (!is_writable($directory)) {
            Log::error("Directory $directory is not writable");
            return false;
        }

        // Save QR code to file
        try {
            $qrCodeImage->saveToFile($path);
        } catch (\Exception $e) {
            Log::error("Failed to save QR code to: $path");
            return false;
        }

        Log::info("QR code saved successfully to: $path");
        return $path;
    }


}
