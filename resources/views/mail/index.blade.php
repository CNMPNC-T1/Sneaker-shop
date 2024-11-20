<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
</head>
<body>
    <h2>Thank you for your order, {{ $user->firtsname }}!</h2>
    <p>Your order has been placed successfully. Below are the details:</p>

    <h3>Order Information:</h3>
    <p>Order ID: {{ $order->id }}</p>
    <p>Total Price: {{ formatCurrency($order->total) }}</p>
    <p>Delivery Date: {{ $order->delivery_date }}</p>
    <p>Payment Status: {{ $order->payment_status == 0 ? 'Pending' : 'Completed' }}</p>

    <h3>Order Items:</h3>
    <ul>
        @foreach ($order->details as $item)
            <li>{{ $item->product->name }} (x{{ $item->quantity }}) - {{ formatCurrency($item->price) }}</li>
        @endforeach
    </ul>

    <p>We will notify you once your order is shipped. Thank you for shopping with us!</p>
</body>
</html>
