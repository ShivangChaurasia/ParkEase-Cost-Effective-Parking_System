# ParkEase – Smart Cost-Effective Parking Booking System

Welcome to **ParkEase**! This is a movie-ticket-style parking booking platform where users can easily find nearby registered parking areas and book parking slots in advance.

The goal of this system is to solve the problem of people wasting time searching for parking spaces, which causes traffic congestion and frustration. Instead of depending on expensive IoT sensors, this system works with affiliated parking owners where all parking data is managed directly inside the platform.

**Unique Selling Point (USP): "Parking = Movie Ticket Booking"**
- Theatre = Parking Lot
- Show Time = Time Slot
- Seat = Parking Slot
- Ticket = Booking Confirmation

## Technology Stack
- **Backend:** Laravel PHP
- **Database:** MongoDB (via `mongodb/laravel-mongodb`)
- **Frontend:** Blade Templates + Bootstrap + JavaScript (AJAX for dynamic slot selection)
- **Mapping & Location:** Google Maps API (for map visualization and navigation support)

---

## System Modules & Features

### 1. User Module (For Drivers)
- **Location-Based Search:** Users can allow GPS access to automatically show nearby parking, or manually enter their Pincode to search.
- **View Parking Details:** See a list of registered parking lots, available slots, pricing, and map location.
- **Movie-Style Booking Flow:**
  1. Select a Date.
  2. Select a Time Slot.
  3. Visual Grid Selection: Pick available parking slots (e.g., A1, A2, B1) visually on a grid.
- **Booking Confirmation:** Receive a unique booking ID (and optionally a QR code).
- **Navigation:** Use Google Maps integration to navigate to the booked parking location.
- **History:** View past and upcoming bookings.

### 2. Owner Module (For Parking Lot Owners)
- **Registration & Authentication:** Owners register and log in to manage their parking spaces.
- **Add Parking Area:** Enter details like parking name, address, pincode, city, pricing, and total parking capacity. Also, drop a pin on Google Maps to save Latitude/Longitude.
- **Design Layout (Grid Builder):** Owners enter the number of rows and columns (e.g., 5 rows, 4 columns) and the system automatically generates slots (A1-A4, B1-B4, etc.).
- *(Future Scope: Blueprint Upload - Manually mapping slots on an uploaded blueprint image).*
- **Booking Management:** View slot availability and track upcoming bookings for their parking areas.

### 3. Admin Module (System Administrator)
- **Owner Verification:** Approve or verify registered parking owners before they can list spaces.
- **System Monitoring:** Manage all parking areas, monitor active bookings, and handle user/owner issues.
- **Platform Management:** Handle reports and ensure smooth operation.

---

## Core Database Structure (MongoDB Collections)
- **`users`**: Manages all accounts (User, Owner, Admin).
- **`parking_lots`**: Stores parking lot details, location data (for geospatial queries), and layout parameters.
- **`slots`**: Individual parking spaces (e.g., Slot A1) linked to a specific parking lot.
- **`time_slots`**: Defines the booking time intervals.
- **`bookings`**: Stores the confirmed reservations linking a User, a Slot, a Time Slot, and a Date to prevent double-booking.

## Important Note for Team Members
Before confirming any booking, the backend implements strict validation to ensure that a selected slot is not double-booked for the exact same date and time slot!
