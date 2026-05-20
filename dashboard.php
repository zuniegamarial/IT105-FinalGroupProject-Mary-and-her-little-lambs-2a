<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'inventory_sales_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle Login
$error = '';
$userRole = null;
$userName = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'login') {
        $loginUser = $_POST['username'] ?? '';
        $loginPass = $_POST['password'] ?? '';
        
        if ($loginUser === 'admin' && $loginPass === 'admin') {
            $_SESSION['user'] = 'admin';
            $_SESSION['role'] = 'admin';
            $_SESSION['name'] = 'ADMIN001';
            header('Location: dashboard.php');
            exit();
        } elseif ($loginUser === 'demo' && $loginPass === 'demo') {
            $_SESSION['user'] = 'demo';
            $_SESSION['role'] = 'user';
            $_SESSION['name'] = 'DEMO_USER';
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    } elseif (isset($_POST['logout'])) {
        session_destroy();
        header('Location: dashboard.php');
        exit();
    }
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user']);
$userRole = $_SESSION['role'] ?? null;
$userName = $_SESSION['name'] ?? '';

// Handle Add Product (Admin only)
if ($isLoggedIn && $userRole === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $supplier = $_POST['supplier'];
    $size = $_POST['category'];
    
    // Get or create supplier
    $stmt = $pdo->prepare("SELECT supplier_id FROM SUPPLIERS WHERE name = ?");
    $stmt->execute([$supplier]);
    $supplierRow = $stmt->fetch();
    if ($supplierRow) {
        $supplier_id = $supplierRow['supplier_id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO SUPPLIERS (name) VALUES (?)");
        $stmt->execute([$supplier]);
        $supplier_id = $pdo->lastInsertId();
    }
    
    $stmt = $pdo->prepare("INSERT INTO PRODUCTS (name, price, current_stock, supplier_id, size) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $price, $stock, $supplier_id, $size]);
    header('Location: dashboard.php');
    exit();
}

// Handle Delete (Admin only)
if ($isLoggedIn && $userRole === 'admin' && isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM PRODUCTS WHERE product_id = ?");
    $stmt->execute([$id]);
    header('Location: dashboard.php');
    exit();
}

// Handle Sell (both Admin and User)
if ($isLoggedIn && isset($_GET['sell'])) {
    $id = $_GET['sell'];
    $stmt = $pdo->prepare("UPDATE PRODUCTS SET current_stock = current_stock - 1 WHERE product_id = ? AND current_stock > 0");
    $stmt->execute([$id]);
    header('Location: dashboard.php');
    exit();
}

// Fetch all products
$stmt = $pdo->query("
    SELECT p.*, COALESCE(s.name, 'Unknown') as supplier_name 
    FROM PRODUCTS p 
    LEFT JOIN SUPPLIERS s ON p.supplier_id = s.supplier_id 
    ORDER BY p.product_id DESC
");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stockly Inventory System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; }
        .header { background: white; border-bottom: 1px solid #e5e7eb; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .header h1 { font-size: 24px; font-weight: bold; color: #1f2937; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .role-badge { background: #e0e7ff; color: #3730a3; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .logout-btn { background: #fee2e2; color: #991b1b; padding: 8px 16px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; }
        .container { max-width: 1280px; margin: 0 auto; padding: 24px; }
        .grid { display: grid; grid-template-columns: 1fr 2fr; gap: 24px; }
        @media (max-width: 768px) { .grid { grid-template-columns: 1fr; } }
        .card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px; }
        .card h2 { font-size: 18px; font-weight: 600; margin-bottom: 16px; color: #1f2937; border-left: 4px solid #3b82f6; padding-left: 12px; }
        input, select { width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px 12px; margin-bottom: 12px; }
        .btn-primary { background: #3b82f6; color: white; border: none; font-weight: 600; cursor: pointer; padding: 10px; border-radius: 8px; width: 100%; }
        .btn-primary:hover { background: #2563eb; }
        .btn-secondary { background: #10b981; color: white; border: none; font-weight: 600; cursor: pointer; padding: 10px; border-radius: 8px; width: 100%; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: 600; }
        .action-btn { padding: 4px 10px; border-radius: 6px; font-size: 12px; margin: 0 3px; border: none; cursor: pointer; }
        .sell { background: #dcfce7; color: #166534; }
        .delete { background: #fee2e2; color: #991b1b; }
        .search { width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #d1d5db; border-radius: 8px; }
        .login-container { max-width: 400px; margin: 100px auto; background: white; border-radius: 16px; padding: 32px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .login-container h2 { text-align: center; margin-bottom: 24px; }
        .error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 16px; text-align: center; }
        .demo-users { margin-top: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 16px; }
        .demo-users p { margin: 5px 0; }
    </style>
</head>
<body>

<?php if (!$isLoggedIn): ?>
    <!-- Login Screen -->
    <div class="login-container">
        <h2>🔐 Stockly Login</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="action" value="login">
            <input type="text" name="username" placeholder="Username" required style="margin-bottom: 12px;">
            <input type="password" name="password" placeholder="Password" required style="margin-bottom: 12px;">
            <button type="submit" class="btn-primary">Login</button>
        </form>
        <div class="demo-users">
            <p><strong>Demo Accounts:</strong></p>
            <p>👑 Admin: <code>admin / admin</code> (Full access)</p>
            <p>👤 Demo User: <code>demo / demo</code> (View & Buy only)</p>
        </div>
    </div>
<?php else: ?>
    <!-- Dashboard -->
    <div class="header">
        <h1>📦 Stockly Inventory System</h1>
        <div class="user-info">
            <span class="role-badge"><?php echo $userRole === 'admin' ? '👑 Admin' : '👤 Demo User'; ?></span>
            <span>Welcome, <?php echo htmlspecialchars($userName); ?></span>
            <form method="POST" style="margin:0">
                <button type="submit" name="logout" value="1" class="logout-btn">Logout</button>
            </form>
        </div>
    </div>

    <div class="container">
        <div class="grid">
            <!-- Left Column -->
            <div>
                <?php if ($userRole === 'admin'): ?>
                    <!-- Add Product Form (Admin only) -->
                    <div class="card">
                        <h2>➕ Add New Product</h2>
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            <input type="text" name="name" placeholder="Product Name" required>
                            <input type="text" name="category" placeholder="Category / Size">
                            <input type="number" step="0.01" name="price" placeholder="Price" required>
                            <input type="number" name="stock" placeholder="Initial Stock" required>
                            <input type="text" name="supplier" placeholder="Supplier Name">
                            <button type="submit" class="btn-primary">➕ Add Product</button>
                        </form>
                    </div>
                <?php endif; ?>
                
                <!-- Audit Log / Info Card -->
                <div class="card">
                    <h2>📋 System Info</h2>
                    <p><strong>Logged in as:</strong> <?php echo $userRole === 'admin' ? 'Administrator' : 'Demo User'; ?></p>
                    <p><strong>Total Products:</strong> <?php echo count($products); ?></p>
                    <p><strong>Current Time:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
                    <p><strong>Last Action:</strong> 
                        <?php 
                        if ($userRole === 'admin') echo 'Full CRUD access enabled';
                        else echo 'View and Buy only (read-only mode)';
                        ?>
                    </p>
                </div>
            </div>

            <!-- Right Column - Products Table -->
            <div>
                <div class="card">
                    <h2>🔍 Search Products</h2>
                    <input type="text" id="searchInput" class="search" placeholder="Search by name, category or supplier...">
                </div>

                <div class="card">
                    <h2>📋 Product List</h2>
                    <div style="overflow-x: auto;">
                        <table id="productTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Supplier</th>
                                    <th><?php echo $userRole === 'admin' ? 'Actions' : 'Action'; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['product_id']; ?></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['size'] ?? 'General'); ?></td>
                                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo $product['current_stock']; ?></td>
                                    <td><?php echo htmlspecialchars($product['supplier_name']); ?></td>
                                    <td>
                                        <a href="?sell=<?php echo $product['product_id']; ?>" class="action-btn sell" onclick="return confirm('Sell 1 unit of <?php echo addslashes($product['name']); ?>?')">🛒 Buy</a>
                                        <?php if ($userRole === 'admin'): ?>
                                            <a href="?delete=<?php echo $product['product_id']; ?>" class="action-btn delete" onclick="return confirm('Delete <?php echo addslashes($product['name']); ?>?')">🗑️ Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (count($products) == 0): ?>
                                <tr><td colspan="7" style="text-align: center; padding: 40px;">No products found. <?php echo $userRole === 'admin' ? 'Use the form to add products.' : 'Please check back later.'; ?></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Live search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#productTable tbody tr');
            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>
<?php endif; ?>
</body>
</html>