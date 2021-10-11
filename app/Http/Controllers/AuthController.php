<?php

namespace App\Http\Controllers;
use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use App\Mail\SendVerifyMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        //Validate data
        $data = $request->only('fname', 'name', 'email', 'password', 'password_confirmation');
        $validator = Validator::make($data, [
            'fname' => 'required|string',
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|string|min:6|max:50',
            'password_confirmation' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is valid, create new user
        $user = User::create([
        	'fname' => $request->fname,
        	'name' => $request->name,
        	'email' => $request->email,
        	'password' => bcrypt($request->password),
            'role' => 'Admin'
        ]);

        $verification_code = Str::random(30); //Generate verification code
        DB::table('user_verifications')->insert(['user_id' => $user->id, 'token' => $verification_code]);
        Mail::to($request->email)->send(new SendVerifyMail($verification_code));

        //User created, return success response
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'user' => $user
        ], Response::HTTP_OK);
    }

    public function verifyUser($verification_code)
    {
        $check = DB::table('user_verifications')->where('token', $verification_code)->first();
        if (!is_null($check)) {
            $user = User::find($check->user_id);

            if ($user->is_verified == 1) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account already verified..'
                ]);
            }else {
                $user->update(['is_verified' => true]);
                $user->is_verified = true;
                $user->save();
                DB::table('user_verifications')->where('token', $verification_code)->delete();
                Mail::to($user->email)->send(new WelcomeEmail($user->name));
    
                return response()->json([
                    'success' => true,
                    'message' => 'You have successfully verified your email address.'
                ]);
            }

            
        }

        return response()->json(['success' => false, 'error' => "Verification code is invalid or already verified."]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }


        //Request is validated
        //Crean token
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                	'success' => false,
                	'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
    	return $credentials;
            return response()->json([
                	'success' => false,
                	'message' => 'Could not create token.',
                ], 500);
        }
        $user = JWTAuth::user();
        if ($user->is_verified == false) {
            return response()->json(['message' => 'Your account is invalid.'], 302);
        }
 		//Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user,
        ]);
    }
 
    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

		//Request is validated, do logout        
        try {
            JWTAuth::invalidate($request->token);
 
            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
 
    public function get_user(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);
 
        $user = JWTAuth::authenticate($request->token);
 
        return response()->json(['user' => $user]);
    }

}
