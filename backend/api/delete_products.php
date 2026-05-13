<?php
require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? 0;

$sql = "DELETE FROM PRODUCTS WHERE product_id=?";
$stmt = $pdo->prepare($sql);
if ($stmt->execute([$id])) {
    echo json_encode(["success" => true, "message" => "Product deleted"]);
} else {
    echo json_encode(["success" => false, "message" => "Delete failed"]);
}
?>