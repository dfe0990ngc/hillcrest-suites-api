# Hillcrest Suites API

A comprehensive Laravel-based REST API for hotel management system featuring room booking, payment processing, user management, and administrative controls.

## Features

### 🏨 Core Functionality
- **Room Management**: Create, update, and manage hotel rooms with availability tracking
- **Booking System**: Complete reservation system with date validation and conflict detection
- **Payment Processing**: Multi-method payment handling with transaction tracking
- **User Management**: Role-based access control (Admin/Guest)
- **Real-time Availability**: Dynamic room availability checking with calendar integration
- **Activity Logging**: Comprehensive audit trail for all system activities

### 📊 Analytics & Reporting
- Revenue analytics and payment statistics
- Booking performance metrics
- Occupancy rate tracking
- Specific date range reporting

### 🔧 Administrative Features
- Hotel settings configuration
- Email notifications system
- Crrency setting
- Tax calculation and management

## Technology Stack

- **Framework**: Laravel 12
- **Authentication**: Laravel Sanctum
- **Database**: MySQL/PostgreSQL compatible
- **Notifications**: Laravel Mail with SMTP
- **API Documentation**: RESTful endpoints

## Installation

### Prerequisites
- PHP 8.1 or higher
- Composer
- MySQL/PostgreSQL
- Node.js & NPM (for frontend assets if needed)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/hillcrest-suites-api.git
   cd hillcrest-suites-api
   ```

2. **Install dependencies**
   ```bash
   composer install

   or

   composer install --ignore-platform-req=ext-sodium
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure your `.env` file**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hillcrest_suites
   DB_USERNAME=your_username
   DB_PASSWORD=your_password

   MAIL_MAILER=smtp
   MAIL_HOST=your_smtp_host
   MAIL_PORT=587
   MAIL_USERNAME=your_email
   MAIL_PASSWORD=your_password
   MAIL_ENCRYPTION=tls
   ```

5. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Storage setup**
   ```bash
   php artisan storage:link
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

## API Documentation

### Authentication
All protected routes require Bearer token authentication using Laravel Sanctum. I provided middleware to automate injection of bearer token

#### Authentication Endpoints
```http
POST /api/register          # User registration
POST /api/login             # User login
POST /api/logout            # User logout
POST /api/forgot-password-request  # Password reset request
POST /api/forgot-password   # Password reset
```

### Core Endpoints

#### Rooms Management
```http
GET    /api/admin/rooms                    # List all rooms
POST   /api/admin/rooms                    # Create new room
PUT    /api/admin/rooms/{id}               # Update room
DELETE /api/admin/rooms/{id}               # Delete room
PUT    /api/admin/rooms/{id}/update-image  # Update room image

# Guest endpoints
GET    /api/guest/rooms                    # Available rooms
GET    /api/guest/rooms/available          # Search available rooms
GET    /api/guest/rooms/check-availability # Check specific availability
```

#### Bookings Management
```http
GET /api/admin/bookings        # List all bookings
PUT /api/admin/bookings/{id}   # Update booking status

# Guest endpoints  
GET  /api/guest/bookings              # User's bookings
POST /api/guest/bookings              # Create booking
POST /api/guest/bookings/validate     # Validate booking
PUT  /api/guest/bookings/{id}/cancel  # Cancel booking
```

#### Payment Management
```http
GET  /api/admin/payments           # List payments
POST /api/admin/payments           # Record payment
PUT  /api/admin/payments/{id}      # Update payment
PUT  /api/admin/payments/{id}/void # Void payment
GET  /api/admin/payment-analytics  # Payment analytics
```

#### User Management
```http
GET /api/admin/users                     # List users
PUT /api/admin/users/{id}/update-role    # Update user role
GET /api/admin/users/{id}/profile        # User profile
PUT /api/admin/users/{id}/profile        # Update profile
PUT /api/admin/users/{id}/change-password # Change password
```

### Request/Response Examples

#### Room Availability Check
```http
GET /api/guest/rooms/available
Content-Type: application/json

{
  "check_in": "2024-12-01",
  "check_out": "2024-12-05", 
  "guests": 2,
  "room_type": "Deluxe"
}
```

```json
{
  "available_rooms": [
    {
      "id": 1,
      "number": "101",
      "type": "Deluxe",
      "price_per_night": 150.00,
      "capacity": 2,
      "amenities": ["WiFi", "AC", "TV"],
      "images": ["room1.jpg"]
    }
  ],
  "search_criteria": {
    "check_in": "2024-12-01",
    "check_out": "2024-12-05",
    "guests": 2,
    "nights": 4
  }
}
```

#### Create Booking
```http
POST /api/guest/bookings
Content-Type: application/json
Authorization: Bearer {token}

{
  "room_id": 1,
  "check_in": "2024-12-01",
  "check_out": "2024-12-05",
  "guests": 2,
  "total_amount": 600.00,
  "tax_amount": 60.00,
  "special_requests": "Late check-in required"
}
```

## Database Schema

### Key Models

#### Rooms
- `id`, `number`, `type`, `price_per_night`
- `capacity`, `amenities` (JSON), `images` (JSON)
- `status`, `floor`, `description`

#### Bookings  
- `id`, `code`, `user_id`, `room_id`
- `check_in`, `check_out`, `guests`
- `total_amount`, `tax_amount`
- `status`, `payment_status`
- `special_requests`

#### Payments
- `id`, `booking_id`, `user_id`, `amount`
- `payment_method`, `payment_reference`
- `payment_date`, `status`, `notes`
- `processed_by`, `is_void`

#### Users
- `id`, `name`, `email`, `role`
- `phone`, `profile_url`
- Includes computed attributes for booking statistics

## Business Logic

### Room Availability System
The API implements sophisticated availability checking:

- **Overlap Detection**: Prevents double-bookings using date range overlap logic
- **Status Management**: Tracks room status (Available, Occupied, Maintenance)
- **Calendar Integration**: Monthly availability calendar generation
- **Conflict Resolution**: Identifies and reports booking conflicts

### Payment Processing
- **Multi-Method Support**: Cash, credit card, bank transfer, mobile payments
- **Partial Payments**: Supports installment payments
- **Payment Tracking**: Complete audit trail with void capabilities
- **Automatic Status Updates**: Booking status updates based on payment completion

### Notification System
- **Email Notifications**: Booking confirmations, cancellations, payment confirmations
- **Configurable Settings**: Admin control over notification preferences
- **Multi-template Support**: Different templates for different notification types

## Configuration

### Hotel Settings
The system supports configurable hotel settings:

```php
// Currency and localization
'currency' => 'USD|PHP|JPY'
'tax_rate' => 10.0

// Operating hours  
'check_in' => '14:00'
'check_out' => '11:00'

// Notifications
'notify_new_booking' => true
'notify_booking_cancellation' => true
'notify_booking_payment_confirmation' => true
```

## Security Features

- **Role-based Access Control**: Admin and Guest roles with appropriate permissions
- **API Rate Limiting**: Built-in Laravel rate limiting
- **Input Validation**: Comprehensive request validation
- **SQL Injection Protection**: Eloquent ORM usage
- **Authentication**: Sanctum token-based authentication
- **Password Security**: Configurable password policies

## Testing

Run the test suite:
```bash
php artisan test
```

## Deployment

### Production Setup
1. Configure web server (Apache/Nginx)
2. Set up SSL certificates
3. Configure production database
4. Set up job queues for email processing
5. Configure file storage (S3/local)
6. Set up monitoring and logging

### Environment Variables
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-db-name

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password

# Storage
FILESYSTEM_DISK=s3  # or local
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Write comprehensive tests for new features
- Update documentation for API changes
- Use meaningful commit messages

## Support

For support and questions:
- Create an issue on GitHub
- Review the API documentation
- Check existing issues for solutions

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Built with Laravel framework
- Uses Laravel Sanctum for API authentication
- Implements Laravel notification system
- Follows RESTful API design principles

## Author

Nelson Gabriel Cañete <dfe0990ngc@gmail.com>