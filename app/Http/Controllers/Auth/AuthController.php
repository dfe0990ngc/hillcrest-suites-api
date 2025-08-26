<?php

namespace App\Http\Controllers\Auth;

use stdClass;
use App\Models\User;
use App\Helpers\Util;
use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Notifications\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request){
        

        $rules = Util::passwordRules();

        // Apply validation
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email|max:255',
            'password' => $rules->rules, // dynamic rules
            'phone' => 'required|string|max:255',
            'password_confirmation' => 'required|string|same:password',
        ], [
            'password.min' => $rules->message,
            'password.regex' => $rules->message,
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'password' => bcrypt($request->input('password')),
            'role' => 'guest',
        ]);
        
        try{
            $user->notify(new VerifyEmail());
        }catch(\Exception $ei){
            Log::debug('Sending Email Verification Error',[$ei->getMessage()]);
        }

        return response()->json(['message' => 'Successfully registered!']);
    }

    public function login(Request $request){

        // Apply validation
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if(!$user->hasVerifiedEmail()){
            return response()->json([
                'message' => 'Please verify your email!',
                'code' => 'EMAIL_NOT_VERIFIED',
            ],403);
        }

        $cookie = Util::persistLogin($user);

        return response()->json([
            'message' => 'Login Successful!',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'total_bookings' => $user->total_bookings,
                'total_spent' => $user->total_spent,
                'total_nights' => $user->total_nights,
                'profile_url' => $user->profile_url,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'token' => $cookie->getValue(),
        ])->cookie($cookie);
    }

    public function resendVerification(Request $request){
        $request->validate([
            'email' => 'required|email|string|exists:users,email',
        ],[
            'email.exists' => 'The email provided does not exists!',
        ]);

        $user = User::where('email',$request->input('email'))->first();
        
        try{
            $user->notify(new VerifyEmail());
        }catch(\Exception $ei){
            Log::debug('Sending Email Verification Error',[$ei->getMessage()]);
        }

        return response()->json(['message' => 'Email Verification has been sent successfully.']);
    }

    public function logout(Request $request){

        $user = $request->user();
    
        if ($user) {
            // Delete the current access token
            $user->currentAccessToken()->delete();
        }

        // Always clear the cookie regardless
        return response()->json(['message' => 'Logged out successfully'])
                        ->cookie('access_token', '', -1);
                        
    }

    public function passwordResetRequest(Request $request){
        $request->validate([
            'email' => 'required|email|string|exists:users,email',
        ],[
            'email.exists' => 'The email provided does not exists!',
        ]);

        $user = User::where('email',$request->input('email'))->first();

        // Generate Key
        $key = uniqid($user->id);
        $validAt = Carbon::now()->addMinutes(60);
        $url = config('app.frontend_url').'/reset-password?user='.$user->id.'&key='.$key;

        $user->reset_password_key = Hash::make($key);
        $user->reset_password_valid_until = $validAt;
        $user->save();
        
        try{
            $user->notify(new PasswordReset($url));
        }catch(\Exception $ei){
            Log::debug('Sending Password Reset Link Error',[$ei->getMessage()]);
        }

        return response()->json(['message' => 'Password Reset link has been sent successfully.']);
    }

    public function forgotPassword(Request $request){

        $rules = Util::passwordRules();

        $request->validate([
            'id' => 'required|numeric|exists:users,id',
            'key' => 'required|string',
            'password' => $rules->rules,
            'password_confirmation' => 'required|same:password',
        ],[
            'password.min' => $rules->message,
            'password.regex' => $rules->message,
        ]);

        $user = User::find($request->id);

        // Check Key
        if(!Hash::check($request->key,$user->reset_password_key)){

            $this->resetForgotPasswordRequest($user);

            return response()->json(['message' => 'Invalid Password Reset Key!'],400);
        }

        // Check Validity
        if(Carbon::parse($user->reset_password_valid_until)->lessThan(Carbon::now())){

            $this->resetForgotPasswordRequest($user);

            return response()->json(['message' => 'Password Reset Key Expired!'],400);
        }

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        $this->resetForgotPasswordRequest($user);

        return response()->json(['message' => 'Your Password has been reset successfully.']);
    }

    // Reset Values for Password Reset Request
    private function resetForgotPasswordRequest($user){
        $user->reset_password_key = null;
        $user->reset_password_valid_until = null;
        $user->save();
    }
}
