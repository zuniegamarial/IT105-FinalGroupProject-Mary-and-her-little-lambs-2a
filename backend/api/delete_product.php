<?php
require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? 0;

if (!$id) {
    echo json_encode(["success" => false, "message" => "Product ID required"]);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->execute([$id]);
    echo json_encode(["success" => true, "message" => "Product deleted"]);
} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>