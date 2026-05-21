<?php
<<<<<<< HEAD
error_log(print_r($_POST, true));
error_log(print_r(json_decode(file_get_contents("php://input"), true), true));

require_once 'config.php';
=======
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
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
    echo json_encode(["success" => false, "error" => "Connection failed: " . $e->getMessage()]);
    exit();
}
>>>>>>> 59ac3deb9cb36cce45bd3dafa9d37d469d669dce

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? 0;
$name = $data['name'] ?? '';
$price = $data['price'] ?? 0;
$stock = $data['stock'] ?? 0;
$supplier = $data['supplier'] ?? '';
$category = $data['category'] ?? 'General';

if (!$id || empty($name)) {
    echo json_encode(["success" => false, "message" => "Invalid data"]);
    exit();
}

try {
    // Get or create supplier
    $supplier_id = null;
    if (!empty($supplier)) {
        $stmt = $pdo->prepare("SELECT supplier_id FROM SUPPLIERS WHERE name = ?");
        $stmt->execute([$supplier]);
        $supplierRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($supplierRow) {
            $supplier_id = $supplierRow['supplier_id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO SUPPLIERS (name) VALUES (?)");
            $stmt->execute([$supplier]);
            $supplier_id = $pdo->lastInsertId();
        }
    }
    
    $sql = "UPDATE PRODUCTS SET name=?, price=?, current_stock=?, size=?, supplier_id=? WHERE product_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $price, $stock, $category, $supplier_id, $id]);
    
    echo json_encode(["success" => true, "message" => "Product updated"]);
} catch(PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>