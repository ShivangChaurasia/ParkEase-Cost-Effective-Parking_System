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
- **Authentication:** Users must log in to book any slots.
- **Location-Based Search:** Users can allow GPS access to automatically show nearby parking, or manually enter their Pincode to search.
- **Movie-Style Booking Flow:**
  1. Select a Date.
  2. Select a Time Slot.
  3. Visual Grid Selection: Pick available parking slots (e.g., A1, A2, B1) visually on a grid.
- **Payments & Wallet System:**
  - **In-App Wallet:** Users have a digital wallet where they can add and store money.
  - **Payment Options:** Users can pay for bookings using their Wallet Balance or **directly via UPI**.
  - **Transaction History:** A dedicated section where users can track all their money added, money spent on bookings, and refunds.
- **Booking Confirmation:** Receive a unique booking ID.
- **Navigation:** Use Google Maps integration to navigate to the booked parking location.

### 2. Owner Module (For Parking Lot Owners)
- **Registration & Authentication:** Owners register and log in to manage their parking spaces.
- **Add Parking Area:** Enter details like parking name, address, pincode, pricing, and map location.
- **Design Layout (Grid Builder):** Owners enter the number of rows and columns (e.g., 5 rows, 4 columns) and the system automatically generates slots.
- **Booking & Revenue Management:** View slot availability, track upcoming bookings, and monitor earnings from their parking properties.

### 3. Admin Module (System Administrator)
- **Owner Verification:** Approve or verify registered parking owners before they can list spaces.
- **System Monitoring:** Manage all parking areas, monitor active bookings, and handle user/owner issues.
- **Financial Oversight:** Monitor system-wide transactions and payments.

---

## Core Database Structure (MongoDB Collections)
- **`users`**: Manages all accounts, including the `wallet_balance`.
- **`parking_lots`**: Stores parking lot details, location data, and layout parameters.
- **`slots`**: Individual parking spaces (e.g., Slot A1).
- **`time_slots`**: Defines the booking time intervals.
- **`bookings`**: Stores the confirmed reservations linking a User, Slot, and Time Slot.
- **`transactions`**: Logs every financial action (Wallet Top-up, UPI Payment, Refund).
