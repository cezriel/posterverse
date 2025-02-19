<?php
require '../config/db.php';
require '../vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_your_stripe_key');

$order_id = $_GET['order_id'];
$stmt = $conn->prepare("SELECT total_price FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'usd',
            'product_data' => ['name' => 'Order #' . $order_id],
            'unit_amount' => $order['total_price'] * 100,
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
    'success_url' => '/success.php?order_id=' . $order_id,
    'cancel_url' => '/cancel.php',
]);

header("Location: " . $session->url);
?>
