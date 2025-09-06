<?php
session_start();

// Sample order data
$_SESSION['order'] = [
    'id' => 'TEST12345',
    'email' => 'customer@example.com',
    'paypal_id' => 'PAYPALTEST67890'
];

// Sample cart data
$_SESSION['cart'] = [
    [
        'slug' => 'the-keeper-gauge',
        'name' => 'The Keeper Gauge',
        'qty' => 2,
        'price' => 4.99
    ],
    [
        'slug' => 'the-bucket-station',
        'name' => 'The Bucket Station',
        'qty' => 1,
        'price' => 49.99
    ]
];

// Redirect to your actual thank you page
header("Location: /thank-you");
exit;
