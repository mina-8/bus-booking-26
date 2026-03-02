# Discount System Usage Guide

## Overview
The discount system allows admins to create and manage discount codes that customers can use when booking bus tickets. Admins can activate/deactivate discounts and control various discount parameters.

## Features

### 1. Discount Types
- **Percentage**: Discount based on percentage (e.g., 10% off)
- **Fixed Amount**: Fixed discount amount (e.g., 50 EGP off)

### 2. Discount Controls
- **Active Status**: Enable or disable the discount
- **Usage Limit**: Control how many times a discount can be used
- **Date Range**: Set start and expiry dates
- **Minimum Amount**: Require minimum purchase amount
- **Maximum Discount**: Cap the discount amount (for percentage discounts)

## Admin Panel Usage

### Creating a Discount

1. Navigate to **Discounts** in the admin panel
2. Click **Create Discount**
3. Fill in the details:
   - **Code**: Unique discount code (e.g., "SUMMER2026")
   - **Description**: Brief description
   - **Type**: Select "Percentage" or "Fixed Amount"
   - **Value**: Enter discount value
   - **Minimum Amount**: (Optional) Minimum booking amount
   - **Maximum Discount**: (Optional, for percentage only) Cap the discount
   - **Usage Limit**: (Optional) Maximum number of uses
   - **Start Date**: (Optional) When discount becomes active
   - **Expiry Date**: (Optional) When discount expires
   - **Active**: Toggle on/off to activate/deactivate

### Managing Discounts

- **View All Discounts**: See all discounts with their status
- **Filter Discounts**: Filter by type, status, expired, or currently active
- **Edit Discount**: Click on a discount to edit its details
- **Activate/Deactivate**: Toggle the "Active" switch
- **Track Usage**: View how many times a discount has been used

## Code Examples

### 1. Apply Discount to Booking

```php
use App\Models\Discount;
use App\Models\Booking;

// Find the discount by code
$discount = Discount::where('code', 'SUMMER2026')->first();

// Find the booking
$booking = Booking::find(1);

// Apply discount
if ($discount && $discount->isValid()) {
    $success = $booking->applyDiscount($discount);
    
    if ($success) {
        echo "Discount applied successfully!";
        echo "Discount amount: {$booking->discount_amount} EGP";
        echo "Total after discount: {$booking->total_price} EGP";
    } else {
        echo "Failed to apply discount";
    }
} else {
    echo "Invalid or expired discount code";
}
```

### 2. Check Discount Validity

```php
$discount = Discount::where('code', 'SUMMER2026')->first();

if ($discount->isValid()) {
    echo "Discount is valid and can be used";
} else {
    echo "Discount cannot be used";
}

// Check specific reasons
if (!$discount->is_active) {
    echo "Discount is not active";
}

if ($discount->expires_at && now()->isAfter($discount->expires_at)) {
    echo "Discount has expired";
}

if ($discount->usage_limit && $discount->used_count >= $discount->usage_limit) {
    echo "Discount usage limit reached";
}
```

### 3. Calculate Discount Amount

```php
$discount = Discount::where('code', 'SUMMER2026')->first();
$bookingAmount = 500; // 500 EGP

$discountAmount = $discount->calculateDiscount($bookingAmount);

echo "Original amount: {$bookingAmount} EGP";
echo "Discount: {$discountAmount} EGP";
echo "Final amount: " . ($bookingAmount - $discountAmount) . " EGP";
```

### 4. Frontend Implementation Example

```php
// In your booking controller
public function applyDiscount(Request $request)
{
    $request->validate([
        'booking_id' => 'required|exists:bookings,id',
        'discount_code' => 'required|string',
    ]);

    $booking = Booking::findOrFail($request->booking_id);
    $discount = Discount::where('code', $request->discount_code)->first();

    if (!$discount) {
        return response()->json([
            'success' => false,
            'message' => 'Discount code not found'
        ], 404);
    }

    if (!$discount->isValid()) {
        return response()->json([
            'success' => false,
            'message' => 'This discount code is not valid or has expired'
        ], 400);
    }

    $discountAmount = $discount->calculateDiscount($booking->subtotal_price);

    if ($discountAmount <= 0) {
        return response()->json([
            'success' => false,
            'message' => 'This discount cannot be applied to your booking (minimum amount not met)'
        ], 400);
    }

    $booking->applyDiscount($discount);

    return response()->json([
        'success' => true,
        'message' => 'Discount applied successfully',
        'discount_amount' => $discountAmount,
        'new_total' => $booking->total_price
    ]);
}

public function removeDiscount(Request $request)
{
    $request->validate([
        'booking_id' => 'required|exists:bookings,id',
    ]);

    $booking = Booking::findOrFail($request->booking_id);
    $booking->removeDiscount();

    return response()->json([
        'success' => true,
        'message' => 'Discount removed',
        'new_total' => $booking->total_price
    ]);
}
```

### 5. Creating Booking with Discount

```php
use App\Models\Booking;
use App\Models\Discount;

// Create booking first
$booking = Booking::create([
    'user_id' => auth()->id(),
    'customer_name' => 'John Doe',
    'phone_number' => '01234567890',
    'subtotal_price' => 500,
    'discount_amount' => 0,
    'total_price' => 500,
    'status' => 'pending',
]);

// Add booking items...
// ...

// Apply discount if provided
if ($discountCode = request('discount_code')) {
    $discount = Discount::where('code', $discountCode)->first();
    
    if ($discount && $discount->isValid()) {
        $booking->applyDiscount($discount);
    }
}
```

## Database Structure

### Discounts Table
- `id`: Primary key
- `code`: Unique discount code
- `description`: Discount description
- `type`: 'percentage' or 'fixed'
- `value`: Discount value (percentage or amount)
- `min_amount`: Minimum booking amount required
- `max_discount`: Maximum discount amount (for percentage)
- `usage_limit`: Maximum number of uses allowed
- `used_count`: Current usage count
- `starts_at`: Start date/time
- `expires_at`: Expiry date/time
- `is_active`: Active status
- `created_at`, `updated_at`: Timestamps

### Updated Bookings Table
- `discount_id`: Foreign key to discounts table (nullable)
- `subtotal_price`: Price before discount
- `discount_amount`: Discount amount applied
- `total_price`: Final price after discount

## Example Discount Scenarios

### 1. Summer Sale - 20% Off
```
Code: SUMMER2026
Type: Percentage
Value: 20
Min Amount: 200 EGP
Max Discount: 100 EGP
Usage Limit: 1000
Active: Yes
Expires: 2026-08-31
```

### 2. New Customer Discount
```
Code: WELCOME50
Type: Fixed
Value: 50
Min Amount: 100 EGP
Usage Limit: (unlimited)
Active: Yes
```

### 3. Weekend Special
```
Code: WEEKEND15
Type: Percentage
Value: 15
Min Amount: (none)
Max Discount: 75 EGP
Start Date: Every Friday
Expiry Date: Every Sunday
Active: Yes
```

## Best Practices

1. **Use Clear Codes**: Make discount codes memorable and relevant (e.g., SUMMER2026, WELCOME50)
2. **Set Expiry Dates**: Always set expiry dates for promotional discounts
3. **Monitor Usage**: Regularly check usage statistics to prevent abuse
4. **Test Discounts**: Test discounts before making them public
5. **Deactivate When Done**: Deactivate expired or completed promotions instead of deleting them
6. **Minimum Amounts**: Use minimum amounts to ensure profitability
7. **Maximum Discounts**: Cap percentage discounts to control costs

## Admin Tips

- **View Currently Active**: Use the "Currently Active" filter to see which discounts are valid right now
- **Track Performance**: Sort by usage count to see which discounts are most popular
- **Expired Discounts**: Use the "Expired" filter to find and deactivate old discounts
- **Quick Activation**: Toggle the Active switch to quickly enable/disable discounts without editing

## Migration Commands

Run these commands to set up the discount system:

```bash
# Run migrations
php artisan migrate

# If you need to rollback and re-migrate
php artisan migrate:rollback
php artisan migrate
```

## Notes

- Discounts are automatically validated before being applied
- Usage count is automatically incremented when a discount is applied
- Bookings maintain the discount information even if the discount is later modified or deleted
- Removing a discount from a booking doesn't affect the original discount's usage count
