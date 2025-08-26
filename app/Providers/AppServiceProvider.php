<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        
        if(Schema::hasTable('settings') && Setting::count() > 0){
            $setting = Setting::first();
            $smtp = $setting->smtp;

            if(!empty($setting->hotel_name)){
                Config::set('app.name',$setting->hotel_name);
            }

            if(!empty($setting->hotel_name)){
                Config::set('hotel.tax_rate',$setting->tax_rate);
            }

            if(!empty($setting->password_policy)){
                $rule = config('services.security.password_policies.'.$setting->password_policy);
                Config::set('auth.password_rule',$rule);
            }

            if(!empty($setting->session_timeout)){
                Config::set('sanctum.expiration',$setting->session_timeout);
            }

            if(!empty($smtp) && is_array($smtp)){
                Config::set('mail.mailers.smtp.transport','smtp');

                if(isset($smtp['MAIL_HOST'])){
                    Config::set('mail.mailers.smtp.host',$smtp['MAIL_HOST']);
                }
                
                if(isset($smtp['MAIL_PORT'])){
                    Config::set('mail.mailers.smtp.port',$smtp['MAIL_PORT']);
                }
                
                if(isset($smtp['MAIL_USERNAME'])){
                    Config::set('mail.mailers.smtp.username',$smtp['MAIL_USERNAME']);
                }
                
                if(isset($smtp['MAIL_PASSWORD'])){
                    Config::set('mail.mailers.smtp.password',$smtp['MAIL_PASSWORD']);
                }
                
                if(isset($smtp['MAIL_FROM_ADDRESS'])){
                    Config::set('mail.from.address',$smtp['MAIL_FROM_ADDRESS']);
                }
                
                if(!empty($setting->hotel_name)){
                    Config::set('mail.from.name',$setting->hotel_name);
                }
            }
        }
    }
}
