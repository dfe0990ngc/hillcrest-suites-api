<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\MailTestController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\GuestRoomController;
use App\Http\Controllers\GuestBookingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register',[AuthController::class,'register'])->name('api.register');
Route::post('login',[AuthController::class,'login'])->name('api.login');
Route::post('resend-email-verification',[AuthController::class,'resendVerification'])->name('api.resendVerification');
Route::post('logout',[AuthController::class,'logout'])->middleware(['auth:sanctum'])->name('api.logout');
Route::post('forgot-password-request',[AuthController::class,'passwordResetRequest'])->name('api.passwordResetRequest');
Route::post('forgot-password',[AuthController::class,'forgotPassword'])->name('api.forgotPassword');

Route::middleware(['auth:sanctum','verified'])->group(function(){
    Route::prefix('admin')->group(function(){

        // Reports
        Route::get('reports',[ReportController::class,'index'])->name('api.admin.reports');

        // Rooms
        Route::get('rooms',[RoomController::class,'index'])->name('api.admin.rooms');
        Route::post('rooms',[RoomController::class,'store'])->name('api.admin.rooms.store');
        Route::put('rooms/{id}',[RoomController::class,'update'])->name('api.admin.rooms.update');
        Route::put('rooms/{id}/update-image',[RoomController::class,'updateImageUrl'])->name('api.admin.rooms.updateImageUrl');
        Route::delete('rooms/{id}',[RoomController::class,'destroy'])->name('api.admin.rooms.destroy');

        // Bookings
        Route::get('bookings',[BookingController::class,'index'])->name('api.admin.bookings');
        Route::put('bookings/{id}',[BookingController::class,'update'])->name('api.admin.bookings.update');

        // Users
        Route::get('users',[UserController::class,'index'])->name('api.admin.users');
        Route::put('users/{id}/update-role',[UserController::class,'updateRole'])->name('api.admin.users.updateRole');
        Route::get('users/{id}/profile',[UserController::class,'showProfile'])->name('api.admin.users.showProfile');
        Route::put('users/{id}/profile',[UserController::class,'update'])->name('api.admin.users.update');
        Route::put('users/{id}/profile-url',[UserController::class,'updateImage'])->name('api.admin.users.updateImage');
        Route::put('users/{id}/change-password',[UserController::class,'changePassword'])->name('api.admin.users.changePassword');

        // Settings
        Route::get('settings',[SettingController::class,'index'])->name('api.admin.settings');
        Route::put('settings/{id}',[SettingController::class,'update'])->name('api.admin.settings.update');

        // Payment CRUD operations
        Route::get('/payments', [PaymentController::class, 'index'])->name('api.admin.payments');
        Route::post('/payments', [PaymentController::class, 'store'])->name('api.admin.payments.store');
        Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('api.admin.payments.update');
        Route::put('/payments/{payment}/void-transaction', [PaymentController::class, 'destroy'])->name('api.admin.payments.destroy');
        Route::get('/payment-analytics', [PaymentController::class, 'analytics'])->name('api.admin.payments.analytics');
    });

    Route::prefix('guest')->group(function(){
        Route::get('bookings',[BookingController::class,'gIndex'])->name('api.guest.bookings');

        Route::get('rooms', [RoomController::class, 'gIndex']);
        Route::get('rooms/available', [GuestRoomController::class, 'getAvailableRooms']);
        Route::get('rooms/check-availability', [GuestRoomController::class, 'checkAvailability']);
        Route::get('rooms/{room}/availability-calendar', [GuestRoomController::class, 'getAvailabilityCalendar']);
        Route::get('rooms/availability-summary', [GuestRoomController::class, 'getAvailabilitySummary']);
        Route::get('rooms/similar', [GuestRoomController::class, 'getSimilarRooms']);
        
        Route::post('bookings', [GuestBookingController::class, 'store']);
        Route::post('bookings/validate', [GuestBookingController::class, 'validateBooking']);

        Route::put('bookings/{id}/cancel-booking',[BookingController::class,'gCancelBooking'])->name('api.guest.bookings.gCancelBooking');
        Route::put('users/{id}/profile',[UserController::class,'gUpdate'])->name('api.guest.users.gUpdate');
        Route::put('users/{id}/profile-url',[UserController::class,'gUpdateImage'])->name('api.guest.users.gUpdateImage');
        Route::put('users/{id}/change-password',[UserController::class,'gChangePassword'])->name('api.guest.users.gChangePassword');
    });
});

Route::post('send-email',[MailTestController::class,'send'])->name('api.mail.send');
Route::get('hotel-info',[SettingController::class,'basicInfo'])->name('api.settings.basicInfo');