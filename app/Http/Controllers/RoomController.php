<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(){
        $rooms = Room::all();

        return response()->json($rooms);
    }

    public function store(Request $request){
        $request->validate([
            'number' => 'required|string|max:12|unique:rooms,number',
            'type' => 'required|string|in:Standard,Deluxe,Suite,Presidential',
            'price_per_night' => 'required|numeric|max:99999',
            'capacity' => 'required|numeric|max:12',
            'amenities' => 'required|array',
            'description' => 'required|string|max:255',
            'status' => 'required|in:Available,Occupied,Maintenance',
            'floor' => 'required|numeric|max:1000',
        ],[
            'number.unique' => 'Room number has already been used!',
        ]);

        // Set Default image
        $request->merge([
            'images' => ['https://images.pexels.com/photos/164595/pexels-photo-164595.jpeg'],
        ]);

        $room = Room::create([
            'number' => $request->number,
            'type' => $request->type,
            'price_per_night' => $request->price_per_night,
            'capacity' => $request->capacity,
            'amenities' => $request->amenities ?? [],
            'description' => $request->input('description'),
            'images' => $request->images ?? [],
            'status' => $request->status,
            'floor' => $request->floor,
        ]);

        return response()->json([
            'message' => 'Room created successfully!',
            'room' => $room,
        ],201);
    }

    public function update(Request $request, $id){
        
        $room = Room::find($id);

        if(!$room){
            return response()->json(['message' => 'Room does not exists!'],404);
        }

        $request->validate([
            'type' => 'nullable|string|in:Standard,Deluxe,Suite,Presidential',
            'price_per_night' => 'nullable|numeric|max:99999',
            'capacity' => 'nullable|numeric|max:12',
            'amenities' => 'nullable|array',
            'description' => 'nullable|string|max:255',
            'status' => 'nullable|in:Available,Occupied,Maintenance',
            'floor' => 'nullable|numeric|max:1000',
        ]);

        $room->update([
            'type' => $request->type,
            'price_per_night' => $request->price_per_night,
            'capacity' => $request->capacity,
            'amenities' => $request->amenities ?? [],
            'description' => $request->input('description'),
            'status' => $request->status,
            'floor' => $request->floor,
        ]);

        return response()->json([
            'message' => 'Room updated successfully!',
            'room' => $room,
        ],200);
    }

    public function updateImageUrl(Request $request, $id){
        $room = Room::find($id);

        if(!$room){
            return response()->json(['message' => 'Room does not exist!'], 404);
        }

        $request->validate([
            'url' => 'required|url|string|max:1024',
        ]);

        $room->update([
            'images' => [$request->url],
        ]);

        return response()->json([
            'message' => 'Image url has been updated successfully!',
            'room' => $room->fresh(), // Refresh to get updated data
        ], 200);
    }

    public function uploadImage(Request $request, $id){
    
        $room = Room::find($id);

        if(!$room){
            return response()->json(['message' => 'Room does not exist!'], 404);
        }

        $request->validate([
            'image_file' => 'required|image|max:4096',
        ]);

        // Handle the file upload
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            
            // Generate a unique filename
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Store the file (adjust the path as needed)
            $path = $file->storeAs('room_images', $filename, 'public');
            
            // Update the room with the image path
            $room->update([
                'images' => [asset('storage/' . $path)],
            ]);
        }

        return response()->json([
            'message' => 'Room image uploaded successfully!',
            'room' => $room->fresh(), // Refresh to get updated data
        ], 200);
    }

    // Destroy Room
    public function destroy(Request $request, $id){
        $room = Room::find($id);

        if(!$room){
            return response()->json(['message' => 'Room does not exist!'], 404);
        }

        if($request->user()->role !== 'admin'){
            return response()->json(['message' => 'You account is not authorized to delete room record!'], 403);
        }
        
        $room->delete();

        return response()->json([
            'message' => 'Room has been deleted successfully!',
        ], 200);
    }

    // ==================== GUEST Section =====================
    public function gIndex(Request $request){
        $rooms = Room::where('status','Available')->get();

        return response()->json($rooms);
    }
}
