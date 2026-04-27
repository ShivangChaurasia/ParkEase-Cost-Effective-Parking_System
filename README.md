# 🚗 ParkEase – Smart Cost-Effective Parking Booking System

Welcome to **ParkEase**! This is a comprehensive, movie-ticket-style parking booking platform where users can easily find nearby registered parking areas and book parking slots in advance.

## 🎯 The Problem & Our Solution
Finding a parking spot in busy areas leads to traffic congestion, fuel wastage, and frustration. Existing "smart parking" solutions rely heavily on expensive IoT sensors and external APIs for live availability, making them costly to implement and maintain.

**Our Solution:** We eliminate the need for expensive hardware. ParkEase works strictly with **affiliated parking owners**. All parking data (slots, capacity, availability) is managed entirely through our database by the owners themselves. 

### 🌟 Unique Selling Point (USP)
**"Parking = Movie Ticket Booking"**
Our core philosophy makes the system highly intuitive:
- **Theatre** = Parking Lot
- **Show Time** = Time Slot
- **Seat** = Parking Slot (e.g., A1, B2)
- **Ticket** = Booking Confirmation

---

## 🛠 Technology Stack
- **Backend Framework:** Laravel PHP
- **Database:** MongoDB (using the official `mongodb/laravel-mongodb` package for Eloquent ORM support)
- **Frontend:** Blade Templates + Bootstrap + Custom Vanilla JavaScript / AJAX
- **Mapping & Location:** Google Maps API (for map visualization, geolocation, and navigation)
- **Payments:** UPI Integration & Custom In-App Wallet System

---

## 📱 Detailed User Flow

1. **Authentication:** The user registers and logs into the platform.
2. **Search:** 
   - **Auto:** Allows GPS access; the system runs a geospatial query to find nearby lots.
   - **Manual:** Enters a specific pincode.
3. **View Listings:** The user sees a list and a Google Map showing available parking lots, complete with pricing and distance.
4. **Lot Selection:** The user clicks a parking lot to see details.
5. **Time Selection:** The user chooses a **Date** and a **Time Slot**.
6. **Visual Slot Selection:** The user is presented with a visual grid (like a movie theatre). Available slots are green, booked slots are grayed out. They click to select (e.g., Slot B3).
7. **Payment Selection:** 
   - Pay using existing **Wallet Balance**.
   - Pay via **Direct UPI**.
8. **Confirmation:** The booking is confirmed. The user receives a unique Booking ID and a link to navigate to the lot via Google Maps.

---

## 🏗 Detailed Module Specifications

### 1. User Module (For Drivers)
- **Profile & Auth:** Registration, Login, Profile Management.
- **Wallet System:** 
  - View current balance.
  - Add money to the wallet via UPI.
- **Transaction History:** A dedicated page tracking all financial movements:
  - `Credits`: Money added to the wallet or refunds.
  - `Debits`: Money spent on bookings.
- **Search & Map:** Interactive map showing nearby parking locations.
- **Booking Engine:** The core movie-ticket style interface preventing double-booking via database-level validation.
- **My Bookings:** View upcoming and past parking reservations.

### 2. Owner Module (For Parking Lot Owners)
- **Registration & Verification:** Owners must register and await Admin approval.
- **Property Management:** Add new parking lots. Enter Name, Address, Pincode, Hourly Price. Drop a pin on a Google Map to capture exact Latitude and Longitude.
- **Visual Layout Builder (Grid):** 
  - The owner inputs `Total Rows` (e.g., 5) and `Slots per Row` (e.g., 4). 
  - The system automatically generates 20 slot records named A1, A2, A3, A4, B1, B2... up to E4.
- **Dashboard & Earnings:** Monitor active bookings for the day and track revenue generated from paid slots.

### 3. Admin Module (System Administrator)
- **User Management:** View all users and owners. Suspend/Ban accounts if necessary.
- **Owner Verification:** Review and approve pending owner requests before their lots go live on the map.
- **System Monitoring:** View platform-wide statistics (total bookings, total revenue, active lots).
- **Financial Oversight:** Monitor the global transaction log for security and auditing.

---

## 🗄 Database Architecture (MongoDB Collections)

Since we are using MongoDB, data is stored in document collections. We use Laravel's Eloquent to interact with them relationally.

### `users`
- `_id` (ObjectId)
- `name` (String)
- `email` (String)
- `password` (String)
- `role` (Enum: 'user', 'owner', 'admin')
- `wallet_balance` (Decimal/Double, Default: 0.00)
- `timestamps`

### `parking_lots`
- `_id` (ObjectId)
- `owner_id` (ObjectId -> users)
- `name` (String)
- `address` (String)
- `pincode` (String)
- `location` (GeoJSON Point: coordinates [longitude, latitude] for `$near` queries)
- `price_per_slot` (Decimal)
- `layout_type` (Enum: 'grid', 'blueprint')
- `total_rows` (Integer)
- `slots_per_row` (Integer)
- `status` (Enum: 'pending', 'approved', 'rejected')
- `timestamps`

### `slots`
- `_id` (ObjectId)
- `parking_lot_id` (ObjectId -> parking_lots)
- `slot_number` (String, e.g., 'A1')
- `row` (String, e.g., 'A')
- `column` (Integer, e.g., 1)
- `status` (Enum: 'active', 'inactive' - allows owners to block specific broken slots)
- `timestamps`

### `time_slots`
- `_id` (ObjectId)
- `start_time` (Time)
- `end_time` (Time)
*(Note: Can be standardized global hourly slots or custom slots per parking lot)*

### `bookings`
- `_id` (ObjectId)
- `booking_id` (String, Unique Alphanumeric)
- `user_id` (ObjectId -> users)
- `parking_lot_id` (ObjectId -> parking_lots)
- `slot_id` (ObjectId -> slots)
- `time_slot_id` (ObjectId -> time_slots)
- `date` (Date)
- `price` (Decimal)
- `payment_method` (Enum: 'wallet', 'upi')
- `status` (Enum: 'confirmed', 'cancelled', 'completed')
- `timestamps`

### `transactions`
- `_id` (ObjectId)
- `user_id` (ObjectId -> users)
- `amount` (Decimal)
- `type` (Enum: 'credit', 'debit')
- `method` (Enum: 'upi', 'wallet', 'refund')
- `status` (Enum: 'success', 'failed', 'pending')
- `reference_id` (String - links to UPI Ref or Booking ID)
- `timestamps`

---

## 🚀 Future Scope
- **Blueprint Layout Mapping:** Allowing owners to upload an image of their parking lot and click to visually place slots instead of using the standard grid generator.
- **QR Code Check-in:** Generating a QR code on the booking ticket that the parking attendant scans upon entry.
- **AI Analytics:** Predicting parking demand based on historical data.

---
*This README serves as the master specification for the development team to ensure all modules align with the core business logic.*
