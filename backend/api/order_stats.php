<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Content-Type: application/json; charset=UTF-8");

$host = 'localhost';
$dbname = 'inventory_sales_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
    exit();
}

try {
    $order_items_count = $pdo->query("SELECT COUNT(*) FROM order_items")->fetchColumn();
    $orders_count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $products_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    
    echo json_encode([
        "order_items" => (int)$order_items_count,
        "orders" => (int)$orders_count,
        "products" => (int)$products_count
    ]);
} catch(PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>