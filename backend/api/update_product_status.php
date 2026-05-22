<?php
require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? 0;
$order_status = $data['order_status'] ?? 'available';

if (!$id) {
    echo json_encode(["success" => false, "message" => "Product ID required"]);
    exit();
}

try {
    // Add order_status column if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE products ADD COLUMN order_status VARCHAR(50) DEFAULT 'available'");
    } catch(PDOException $e) {
        // Column already exists, ignore error
    }
    
    $stmt = $pdo->prepare("UPDATE products SET order_status = ? WHERE product_id = ?");
    $stmt->execute([$order_status, $id]);
    
    echo json_encode(["success" => true, "message" => "Status updated successfully"]);
} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>