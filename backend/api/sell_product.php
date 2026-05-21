<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = 'localhost';
$dbname = 'inventory_sales_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $e->getMessage()]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? 0;
$quantity = $data['quantity'] ?? 1;

try {
    $stmt = $pdo->prepare("UPDATE products SET current_stock = current_stock - ? WHERE product_id = ? AND current_stock >= ?");
    $stmt->execute([$quantity, $id, $quantity]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Product sold"]);
    } else {
        echo json_encode(["success" => false, "message" => "Insufficient stock or product not found"]);
    }
} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>