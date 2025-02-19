<?php
session_start();
require '../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $_SESSION['user_id'];
$total_price = 0;

$conn->beginTransaction();
$stmt = $conn->prepare("INSERT INTO orders (user_id, total_price) VALUES (?, ?)");
$stmt->execute([$user_id, $total_price]);
$order_id = $conn->lastInsertId();

foreach ($data['cart'] as $item) {
    $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$item['id']]);
    $product = $stmt->fetch();
    
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt->execute([$order_id, $item['id'], $item['quantity'], $product['price']]);
    
    $total_price += $product['price'] * $item['quantity'];
}

$stmt = $conn->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
$stmt->execute([$total_price, $order_id]);

$conn->commit();
echo json_encode(["order_id" => $order_id]);
?>
