<?php
require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'] ?? '';
$category = $data['category'] ?? '';
$price = $data['price'] ?? 0;
$stock = $data['stock'] ?? 0;
$supplier = $data['supplier'] ?? '';

$sql = "INSERT INTO products (name, category, price, stock, supplier) VALUES (?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);

if ($stmt->execute([$name, $category, $price, $stock, $supplier])) {
    echo json_encode(["success" => true, "message" => "Product added successfully", "id" => $pdo->lastInsertId()]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to add product"]);
}
?>