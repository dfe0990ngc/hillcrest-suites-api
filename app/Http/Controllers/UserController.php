<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(){
        $users = User::all();

        return response()->json($users);
    }

    public function updateRole(Request $request, $id){
        $user = User::find($id);

        if(!$user){
            return response()->json(['message' => 'User not found!'],404);
        }

        if($user->id === $request->user()->id){
            return response()->json(['message' => 'You cannot update your own role!'],422);
        }

        $request->validate([
            'role' => 'required|string|in:admin,guest',
        ]);

        $user->role = $request->role;
        $user->save();

        return response()->json(['message' => 'User role has been updated successfully!']);
    }

    public function showProfile(Request $request, $id){
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found!'], 404);
        }

        return response()->json($user->only(['id', 'name', 'email', 'phone','profile_url','created_at','total_bookings','total_spent','total_nights']));
    }

    public function update(Request $request, $id){
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found!'], 404);
        }

        if ($user->id !== $request->user()->id) {
            return response()->json(['message' => 'Operation not allowed!'], 403);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:255',
        ]);

        // Filter out null values to avoid overwriting with nulls
        $updateData = array_filter($validated, function($value) {
            return $value !== null;
        });

        if (empty($updateData)) {
            return response()->json(['message' => 'No data provided for update'], 400);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Your profile has been updated successfully!',
            'user' => $user->only(['id', 'name', 'email', 'role', 'phone','profile_url','created_at','total_bookings','total_spent','total_nights']) // Return updated data
        ]);
    }

    public function updateImage(Request $request, $id){
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found!'], 404);
        }

        if ($user->id !== $request->user()->id) {
            return response()->json(['message' => 'Operation not allowed!'], 403);
        }

        $validated = $request->validate([
            'profile_url' => 'nullable|string|max:1024',
        ]);

        // Filter out null values to avoid overwriting with nulls
        $updateData = array_filter($validated, function($value) {
            return $value !== null;
        });

        if (empty($updateData)) {
            return response()->json(['message' => 'No data provided for update'], 400);
        }

        $user->update(['profile_url' => $updateData['profile_url']]);

        return response()->json([
            'message' => 'Your profile image url has been updated successfully!',
            'user' => $user->only(['id', 'name', 'email','role', 'phone','profile_url','created_at','total_bookings','total_spent','total_nights']) // Return updated data
        ]);
    }

    public function changePassword(Request $request, $id){
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found!'], 404);
        }

        if ($user->id !== $request->user()->id) {
            return response()->json(['message' => 'Operation not allowed!'], 403);
        }

        $rules = Util::passwordRules();
        $validated = $request->validate([
            'password' => 'required|string|max:255',
            'new_password' => $rules->rules,
            'new_password_confirmation' => 'required|string|max:255|same:new_password',
        ],[
            'new_password.min' => $rules->message,
            'new_password.regex' => $rules->message,
        ]);

        // Filter out null values to avoid overwriting with nulls
        $updateData = array_filter($validated, function($value) {
            return $value !== null;
        });

        if (empty($updateData)) {
            return response()->json(['message' => 'No data provided for update'], 400);
        }

        // Check old Password
        if(!Hash::check($request->password,$user->password)){
            return response()->json(['message' => 'Invalid old password!'],422);
        }

        $user->update([
            'password' => bcrypt($request->new_password),
        ]);


        optional($user->tokens())->delete();

        return response()->json([
            'message' => 'Your password has been updated successfully! Please re-login.',
            'user' => $user->only(['id', 'name', 'email', 'role', 'phone','profile_url','created_at','total_bookings','total_spent','total_nights']) // Return updated data
        ])->cookie('access_token', '', -1);
    }

    // ============== GUEST SECTION =============
    public function gUpdate(Request $request, $id){
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found!'], 404);
        }

        if ($user->id !== $request->user()->id) {
            return response()->json(['message' => 'Operation not allowed!'], 403);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:255',
        ]);

        // Filter out null values to avoid overwriting with nulls
        $updateData = array_filter($validated, function($value) {
            return $value !== null;
        });

        if (empty($updateData)) {
            return response()->json(['message' => 'No data provided for update'], 400);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Your profile has been updated successfully!',
            'user' => $user->only(['id', 'name', 'email', 'role', 'phone','profile_url','created_at','total_bookings','total_spent','total_nights']) // Return updated data
        ]);
    }

    public function gUpdateImage(Request $request, $id){
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found!'], 404);
        }

        if ($user->id !== $request->user()->id) {
            return response()->json(['message' => 'Operation not allowed!'], 403);
        }

        $validated = $request->validate([
            'profile_url' => 'nullable|string|max:1024',
        ]);

        // Filter out null values to avoid overwriting with nulls
        $updateData = array_filter($validated, function($value) {
            return $value !== null;
        });

        if (empty($updateData)) {
            return response()->json(['message' => 'No data provided for update'], 400);
        }

        $user->update(['profile_url' => $updateData['profile_url']]);

        return response()->json([
            'message' => 'Your profile image url has been updated successfully!',
            'user' => $user->only(['id', 'name', 'email','role', 'phone','profile_url','created_at','total_bookings','total_spent','total_nights']) // Return updated data
        ]);
    }

    public function gChangePassword(Request $request, $id){
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found!'], 404);
        }

        if ($user->id !== $request->user()->id) {
            return response()->json(['message' => 'Operation not allowed!'], 403);
        }

        $rules = Util::passwordRules();
        $validated = $request->validate([
            'password' => 'required|string|max:255',
            'new_password' => $rules->rules,
            'new_password_confirmation' => 'required|string|max:255|same:new_password',
        ],[
            'new_password.min' => $rules->message,
            'new_password.regex' => $rules->message,
        ]);

        // Filter out null values to avoid overwriting with nulls
        $updateData = array_filter($validated, function($value) {
            return $value !== null;
        });

        if (empty($updateData)) {
            return response()->json(['message' => 'No data provided for update'], 400);
        }

        // Check old Password
        if(!Hash::check($request->password,$user->password)){
            return response()->json(['message' => 'Invalid old password!'],422);
        }

        $user->update([
            'password' => bcrypt($request->new_password),
        ]);

        optional($user->tokens())->delete();

        return response()->json([
            'message' => 'Your password has been updated successfully! Please re-login.',
            'user' => $user->only(['id', 'name', 'email', 'role', 'phone','profile_url','created_at','total_bookings','total_spent','total_nights']) // Return updated data
        ])->cookie('access_token', '', -1);
    }
}
