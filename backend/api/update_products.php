<?php
require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? 0;
$name = $data['name'] ?? '';
$price = $data['price'] ?? 0;
$stock = $data['stock'] ?? 0;
$supplierName = $data['supplier'] ?? '';

$supplier_id = null;
if ($supplierName) {
    $stmt = $pdo->prepare("SELECT supplier_id FROM SUPPLIERS WHERE name = ?");
    $stmt->execute([$supplierName]);
    $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($supplier) {
        $supplier_id = $supplier['supplier_id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO SUPPLIERS (name) VALUES (?)");
        $stmt->execute([$supplierName]);
        $supplier_id = $pdo->lastInsertId();
    }
}

$sql = "UPDATE PRODUCTS SET name=?, price=?, current_stock=?, supplier_id=? WHERE product_id=?";
$stmt = $pdo->prepare($sql);
if ($stmt->execute([$name, $price, $stock, $supplier_id, $id])) {
    echo json_encode(["success" => true, "message" => "Product updated"]);
} else {
    echo json_encode(["success" => false, "message" => "Update failed"]);
}
?>