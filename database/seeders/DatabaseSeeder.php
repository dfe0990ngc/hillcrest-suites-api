<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Room;
use App\Models\Setting;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        User::updateOrInsert([
            'email' => 'hillcrest-suites@pcds.edu.ph',
        ],[
            'name' => 'HillCrest Admin',
            'password' => bcrypt('#passwordN1234'),
            'email_verified_at' => Carbon::now(),
            'role' => 'admin',
            'phone' => '(082)553 1662',
        ]);

        // User::updateOrInsert([
        //     'email' => 'neslie@gmail.com',
        // ],[
        //     'name' => 'Neslie Cañete',
        //     'password' => bcrypt('#passwordN1234'),
        //     'email_verified_at' => Carbon::now(),
        //     'role' => 'guest',
        //     'phone' => '+639858147644',
        // ]);

        // // Other guest users
        // User::whereNotIn('id',[1,2])->delete();
        // for($i = 0; $i < 20; $i++){
        //     User::updateOrInsert([
        //         'email' => fake()->email(),
        //     ],[
        //         'name' => fake()->name(['male','female'][rand(0,1)]),
        //         'password' => bcrypt('#passwordN1234'),
        //         'email_verified_at' => Carbon::now(),
        //         'role' => 'guest',
        //         'phone' => fake()->phoneNumber(),
        //     ]);
        // }

        // // Default Settings
        Setting::updateOrInsert([
            'hotel_name' => 'HILLCREST SUITES',
            'email' => 'admin@pcds.edu.ph',
        ],[
            'currency' => 'PHP',
            'hotel_address' => 'BY PASS ROAD, BARANGAY KIAGOT,DIGOS CITY',
            'phone' => '(082)553 1662',
            'check_in' => '15:00',
            'check_out' => '11:00',
            'tax_rate' => 10,
            'notify_new_booking' => true,
            'notify_booking_cancellation' => true,
            'notify_booking_payment_confirmation' => true,
            'enable_push_notification' => false,
            'session_timeout' => 120,
            'password_policy' => 'basic',
            'bcc_emails' => 'digospcds@pcds.edu.ph',
            'smtp' => json_encode([
                'MAIL_HOST' => 'mail.pcds.edu.ph',
                'MAIL_PORT' => 465,
                'MAIL_USERNAME' => 'info@pcds.edu.ph',
                'MAIL_PASSWORD' => '.vIK89XDU=PA#]4x',
                'MAIL_FROM_ADDRESS' => 'no-reply@pcds.edu.ph',
                'MAIL_FROM_NAME' => 'HILLCREST SUITES',
            ]),
        ]);

        // Rooms
        Room::updateOrInsert([
            'number' => '101',
        ],[
            'type' => 'standard',
            'price_per_night' => 120,
            'capacity' => 2,
            'amenities' => json_encode(['WiFi', 'TV', 'Air Conditioning', 'Bathroom']),
            'description' => 'Comfortable standard room with modern amenities',
            'images' => json_encode(['https://images.pexels.com/photos/164595/pexels-photo-164595.jpeg']),
            'status' => 'available',
            'floor' => 1,
        ]);

        Room::updateOrInsert([
            'number' => '102',
        ],[
            'type' => 'standard',
            'price_per_night' => 120,
            'capacity' => 2,
            'amenities' => json_encode(['WiFi', 'TV', 'Air Conditioning', 'Bathroom']),
            'description' => 'Comfortable standard room with modern amenities',
            'images' => json_encode(['https://images.pexels.com/photos/164595/pexels-photo-164595.jpeg']),
            'status' => 'occupied',
            'floor' => 1,
        ]);
        
        Room::updateOrInsert([
            'number' => '202',
        ],[
            'type' => 'deluxe',
            'price_per_night' => 180,
            'capacity' => 3,
            'amenities' => json_encode(['WiFi', 'TV', 'Air Conditioning', 'Bathroom', 'Mini Bar', 'Balcony']),
            'description' => 'Spacious deluxe room with premium amenities and city view',
            'images' => json_encode(['https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg']),
            'status' => 'available',
            'floor' => 2,
        ]);
        
        Room::updateOrInsert([
            'number' => '301',
        ],[
            'type' => 'suite',
            'price_per_night' => 350,
            'capacity' => 4,
            'amenities' => json_encode(['WiFi', 'TV', 'Air Conditioning', 'Bathroom', 'Mini Bar', 'Balcony', 'Living Room', 'Kitchen']),
            'description' => 'Luxury suite with separate living room and premium amenities',
            'images' => json_encode(['https://images.pexels.com/photos/237371/pexels-photo-237371.jpeg']),
            'status' => 'available',
            'floor' => 3,
        ]);
        
        Room::updateOrInsert([
            'number' => '401',
        ],[
            'type' => 'presidential',
            'price_per_night' => 800,
            'capacity' => 6,
            'amenities' => json_encode(['WiFi', 'TV', 'Air Conditioning', 'Bathroom', 'Mini Bar', 'Balcony', 'Living Room', 'Kitchen', 'Jacuzzi', 'Butler Service']),
            'description' => 'Presidential suite with luxury amenities and panoramic view',
            'images' => json_encode(['https://images.pexels.com/photos/1743227/pexels-photo-1743227.jpeg']),
            'status' => 'available',
            'floor' => 4,
        ]);


        // // Bookings
        // Booking::updateOrCreate([
        //     'id' => 1,
        // ],[
        //     'user_id' => 2,
        //     'room_id' => 2,
        //     'check_in' => '2025-03-01',
        //     'check_out' => '2025-03-05',
        //     'guests' => 2,
        //     'total_amount' => 480,
        //     'status' => 'checked_in',
        //     'payment_status' => 'paid',
        //     'special_requests' => 'Late checkout if possible',
        //     'created_at' => '2025-03-01',
        // ]);

        // Booking::updateOrCreate([
        //     'id' => 2,
        // ],[
        //     'user_id' => 3,
        //     'room_id' => 3,
        //     'check_in' => '2025-03-10',
        //     'check_out' => '2025-03-12',
        //     'guests' => 2,
        //     'total_amount' => 360,
        //     'status' => 'confirmed',
        //     'payment_status' => 'paid',
        //     'created_at' => '2025-03-10',
        // ]);
        
        // Booking::updateOrCreate([
        //     'id' => 3,
        // ],[
        //     'user_id' => 4,
        //     'room_id' => 4,
        //     'check_in' => '2025-03-15',
        //     'check_out' => '2025-03-18',
        //     'guests' => 3,
        //     'total_amount' => 1050,
        //     'status' => 'pending',
        //     'payment_status' => 'pending',
        //     'special_requests' => 'Airport transfer required',
        //     'created_at' => '2025-03-15',
        // ]);
        
        // Booking::updateOrCreate([
        //     'id' => 4,
        // ],[
        //     'user_id' => 2,
        //     'room_id' => 1,
        //     'check_in' => '2025-02-20',
        //     'check_out' => '2025-02-23',
        //     'guests' => 1,
        //     'total_amount' => 360,
        //     'status' => 'checked_out',
        //     'payment_status' => 'paid',
        //     'created_at' => '2025-02-20',
        // ]);
    }
}
