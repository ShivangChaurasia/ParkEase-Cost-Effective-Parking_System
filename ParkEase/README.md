# ParkEase - Cost-Effective Parking System

ParkEase is a modern, cost-effective parking management system built with Laravel, MongoDB, and Vanilla JavaScript. It features a premium Glassmorphism UI, secure Clerk Authentication, and a robust dual-payment integration (Razorpay & Manual UPI QR).

## 🚀 Tech Stack
- **Backend**: Laravel 11
- **Database**: MongoDB (via `mongodb/laravel-mongodb`)
- **Frontend**: Blade Templates, Bootstrap 5, Vanilla JS
- **Authentication**: Clerk Vanilla JS SDK
- **Payment Gateway**: Razorpay PHP SDK

---

## 💳 Payment Integrations

ParkEase comes with a production-ready, highly secure checkout flow supporting two main payment methods:

### 1. Razorpay (Online Payment)
The checkout uses a **UPI-First** Razorpay integration, optimized for mobile and desktop. 
- Automatically pre-fills user details (Name, Email, Phone).
- Prioritizes Google Pay, PhonePe, and Paytm intent flows.
- Validates payments securely via cryptographic signatures (`razorpay_signature`) on the Laravel backend.

### 2. Manual UPI QR (Fallback / Demo Mode)
For hackathons or manual operations, users can choose "Scan & Pay via UPI".
- Displays a static PhonePe/GPay QR code.
- Captures confirmation via a sleek modal.
- Marks the booking status as `pending_verification` in MongoDB for manual admin approval.

---

## 🛠️ Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/ShivangChaurasia/ParkEase-Cost-Effective-Parking_System.git
   cd ParkEase
   ```

2. **Install PHP Dependencies:**
   Ensure you have PHP 8.3+ installed, then run:
   ```bash
   composer install --ignore-platform-reqs
   ```

3. **Configure Environment Variables:**
   Copy the example file:
   ```bash
   cp .env.example .env
   ```
   Add your MongoDB, Clerk, and Razorpay credentials to the `.env` file:
   ```env
   # Clerk Authentication
   VITE_CLERK_PUBLISHABLE_KEY=pk_test_...
   CLERK_JS_URL=...

   # Razorpay Payment Gateway (Test Mode)
   RAZORPAY_KEY=rzp_test_...
   RAZORPAY_SECRET=your_razorpay_secret_here
   ```

4. **Start the Development Server:**
   ```bash
   php artisan serve
   ```

---

## 🧪 Testing the Payment Flow (Razorpay Test Mode)

To test the payment gateway without using real money:
1. Ensure your `.env` contains **Test Mode** Razorpay keys (`rzp_test_...`).
2. Go to the checkout page and click **Pay Online**.
3. In the Razorpay popup, select **Netbanking**.
4. Choose the bank named **"Success"**.
5. Click **Pay**. 
6. The backend will verify the test signature and securely save the booking to MongoDB.

For the **Manual QR Flow**:
1. Select "Scan & Pay via UPI".
2. Click "I Have Completed Payment".
3. Confirm the modal prompt to save the pending booking.
