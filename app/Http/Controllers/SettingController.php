<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(){
        $setting = Setting::first();

        return response()->json($setting);
    }

    public function update(Request $request, $id){
        $setting = Setting::find($id);

        if(!$setting){
            return response()->json(['message' => 'Settings not found'],404);
        }
        
        // Filter out null values to avoid overwriting with nulls
        $updateData = array_filter($request->all(), function($value) {
            return $value !== null;
        });

        if (empty($updateData)) {
            return response()->json(['message' => 'No data provided for update'], 400);
        }

        $setting->update($request->all());

        return response()->json([
            'message' => 'Settings has been updated successfully!',
            'settings' => $setting->fresh(),
        ]);
    }

    public function basicInfo(){
        $setting = Setting::first();

        $currency_symbols = [
            'USD' => '$',
            'PHP' => '₱',
            'JPY' => '¥',
        ];

        return response()->json([
            'hotel_name' => $setting->hotel_name,
            'currency' => $setting->currency,
            'currency_symbol' => $currency_symbols[$setting->currency],
            'hotel_address' => $setting->hotel_address,
            'phone' => $setting->phone,
            'email' => $setting->email,
            'check_in' => $setting->check_in,
            'check_out' => $setting->check_out,
            'tax_rate' => $setting->tax_rate,
        ]);
    }
}
