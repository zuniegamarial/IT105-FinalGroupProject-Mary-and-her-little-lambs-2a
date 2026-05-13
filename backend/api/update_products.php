<?php
require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? 0;

$name = $data['name'] ?? '';
$category = $data['category'] ?? '';
$price = $data['price'] ?? 0;
$stock = $data['stock'] ?? 0;
$supplier = $data['supplier'] ?? '';

$sql = "UPDATE products SET name=?, category=?, price=?, stock=?, supplier=? WHERE id=?";
$stmt = $pdo->prepare($sql);

if ($stmt->execute([$name, $category, $price, $stock, $supplier, $id])) {
    echo json_encode(["success" => true, "message" => "Product updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update product"]);
}
?>