<?php

namespace App\Helpers;

use stdClass;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Cookie;

class Util{
    public static function persistLogin(User $user) {
        $token = $user->createToken('AuthToken')->plainTextToken;
        $isHttps = env('APP_ENV') !== 'local';
        $sameSite = $isHttps ? 'None' : 'Lax';

        return Cookie::make(
            'access_token',
            $token,
            720,
            '/',
            null,
            $isHttps,
            $isHttps,
            false,
            $sameSite
        );
    }

    public static function bccList(){
        
        $setting = Setting::first();
        $bccs = $setting->bcc_emails;
        $bccList = [];
        if(!empty($bccs)){
            $sp = explode(',',$bccs);
            foreach($sp as $v){
                $bccList[] = trim($v);
            }

            $bccList = array_unique($bccList);
        }

        return $bccList;
    }

    public static function passwordRules(){
        
        $passwordRules = config('services.security.password_policies');
        
        // Define custom error messages
        $passwordErrMsg = [
            'basic' => 'Password must have minimum of 8 characters.',
            'strong' => 'Password must have minimum of 10 characters and contain at least one uppercase, one lowercase, and one number.',
            'very_strong' => 'Password must have minimum of 12 characters and contain at least one uppercase, one lowercase, one number, and one special character (~, #, @, $, !, %, *, ?, &).',
        ];

        $setting = Setting::first();
        $passwordPolicy = $setting->password_policy ?? 'basic';

        $obj = new stdClass();

        $obj->rules = $passwordRules[$passwordPolicy];
        $obj->policy = $passwordPolicy;
        $obj->message = $passwordErrMsg[$passwordPolicy];
        
        return $obj;
    }
}