<?php
require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? 0;
$quantity = $data['quantity'] ?? 1;

if (!$id) {
    echo json_encode(["success" => false, "message" => "Product ID required"]);
    exit();
}

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