<?php
error_log(print_r($_POST, true));
error_log(print_r(json_decode(file_get_contents("php://input"), true), true));

require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'] ?? '';
$price = $data['price'] ?? 0;
$stock = $data['stock'] ?? 0;
$supplierName = $data['supplier'] ?? '';
$category = $data['category'] ?? ''; // ignored if your table has no category column, but stored as size?

// Find or insert supplier
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

// Insert into PRODUCTS (size column used for category if you wish)
$sql = "INSERT INTO PRODUCTS (name, price, current_stock, supplier_id, size) VALUES (?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
if ($stmt->execute([$name, $price, $stock, $supplier_id, $category])) {
    echo json_encode(["success" => true, "message" => "Product added", "id" => $pdo->lastInsertId()]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to add product"]);
}
?>