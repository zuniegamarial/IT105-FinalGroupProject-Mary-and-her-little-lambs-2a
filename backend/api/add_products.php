<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Connect to your database
$host = 'localhost';
$dbname = 'inventory_sales_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $e->getMessage()]);
    exit();
}

// Get the data from React
$data = json_decode(file_get_contents("php://input"), true);

$name = trim($data['name'] ?? '');
$price = floatval($data['price'] ?? 0);
$stock = intval($data['stock'] ?? 0);
$supplier = trim($data['supplier'] ?? '');
$category = trim($data['category'] ?? '');

// Validate
if (empty($name) || $price <= 0) {
    echo json_encode(["success" => false, "message" => "Product name and valid price are required"]);
    exit();
}

try {
    // Handle supplier
    $supplier_id = null;
    if (!empty($supplier)) {
        $stmt = $pdo->prepare("SELECT supplier_id FROM SUPPLIERS WHERE name = ?");
        $stmt->execute([$supplier]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $supplier_id = $row['supplier_id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO SUPPLIERS (name) VALUES (?)");
            $stmt->execute([$supplier]);
            $supplier_id = $pdo->lastInsertId();
        }
    }
    
    // Insert product - let MySQL auto-generate the product_id
    $sql = "INSERT INTO PRODUCTS (name, price, current_stock, size, supplier_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $price, $stock, $category, $supplier_id]);
    
    $newId = $pdo->lastInsertId();
    
    echo json_encode([
        "success" => true, 
        "message" => "Product added successfully",
        "id" => $newId
    ]);
    
} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>