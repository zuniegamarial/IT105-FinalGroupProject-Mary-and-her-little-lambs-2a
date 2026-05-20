<?php
include_once 'config.php'; 

$data = json_decode(file_get_contents("php://input"), true);

header('Content-Type: application/json');

if (isset($data['id'])) {
    $product_id = intval($data['id']);

    // Scenario A: Mary used $pdo
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = :id");
            $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(["success" => true]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "PDO Error: " . $e->getMessage()]);
            exit;
        }
    } 

    // Scenario B: Mary used $conn as a PDO instance
    else if (isset($conn) && $conn instanceof PDO) {
        try {
            $stmt = $conn->prepare("DELETE FROM products WHERE product_id = :id");
            $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(["success" => true]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "PDO Error: " . $e->getMessage()]);
            exit;
        }
    }

    // Scenario C: Mary used MySQLi instead of PDO
    else {
        $db = isset($conn) ? $conn : (isset($pdo) ? $pdo : null);
        if ($db && method_exists($db, 'prepare')) {
            $stmt = $db->prepare("DELETE FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $product_id);
            if ($stmt->execute()) {
                echo json_encode(["success" => true]);
                exit;
            } else {
                echo json_encode(["success" => false, "message" => "MySQLi Execution Error: " . $stmt->error]);
                exit;
            }
        } else {
            echo json_encode(["success" => false, "message" => "Could not locate a valid database connection variable ($conn or $pdo)"]);
            exit;
        }
    }
} else {
    echo json_encode(["success" => false, "message" => "Missing Product ID"]);
}
?>