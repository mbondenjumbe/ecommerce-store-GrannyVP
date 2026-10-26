<?php
require_once 'config/db.php';

// Handle Actions (Create / Update / Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. CREATE ACTION: Add a brand new part visually
    if ($action === 'create') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $category = trim($_POST['category']);
        $oem_number = strtoupper(trim($_POST['oem_number']));
        $compatibility = trim($_POST['compatibility']);
        $stock = (int)$_POST['stock'];
        $item_condition = $_POST['item_condition'];
        
        // Use a reliable default local or web fallback image if left blank
        $image_url = trim($_POST['image_url']);
        if (empty($image_url)) {
            $image_url = 'public/part.jpg';
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image_url, category, oem_number, compatibility, stock, item_condition) 
                                    VALUES (:name, :description, :price, :image_url, :category, :oem_number, :compatibility, :stock, :item_condition)");
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':image_url' => $image_url,
                ':category' => $category,
                ':oem_number' => $oem_number,
                ':compatibility' => $compatibility,
                ':stock' => $stock,
                ':item_condition' => $item_condition
            ]);
            header('Location: admin.php?status=created');
            exit;
        } catch (PDOException $e) {
            header('Location: admin.php?status=error&msg=' . urlencode($e->getMessage()));
            exit;
        }
    }

    // 2. UPDATE ACTION: Modify prices and quantities
    if ($action === 'update') {
        $product_id = (int)$_POST['product_id'];
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];

        $stmt = $pdo->prepare("UPDATE products SET price = :price, stock = :stock WHERE id = :id");
        $stmt->execute([':price' => $price, ':stock' => $stock, ':id' => $product_id]);
        header('Location: admin.php?status=updated');
        exit;
    }

    // 3. DELETE ACTION: Remove row completely
    if ($action === 'delete') {
        $product_id = (int)$_POST['product_id'];
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute([':id' => $product_id]);
        header('Location: admin.php?status=deleted');
        exit;
    }
}

// Fetch total records for admin viewport table rendering
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'components/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrannyVP - Inventory Control Dashboard</title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .admin-layout { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .form-panel { background-color: #0f172a; border: 1px solid #1e293b; border-radius: 8px; padding: 24px; margin-bottom: 40px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 16px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-size: 0.8rem; color: #94a3b8; margin-bottom: 6px; font-weight: 600; text-transform: uppercase; }
        .form-group input, .form-group textarea, .form-group select { background-color: #020617; border: 1px solid #334155; color: white; padding: 10px; border-radius: 6px; font-size: 0.85rem; }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: #f97316; }
        .admin-table { width: 100%; border-collapse: collapse; background-color: #0f172a; border: 1px solid #1e293b; border-radius: 8px; overflow: hidden; }
        .admin-table th, .admin-table td { padding: 12px 14px; text-align: left; border-bottom: 1px solid #1e293b; font-size: 0.85rem; }
        .admin-table th { background-color: #1e293b; color: #f97316; font-weight: bold; text-transform: uppercase; font-size: 0.8rem; }
        .input-admin { background-color: #020617; border: 1px solid #334155; color: white; padding: 6px; border-radius: 4px; font-size: 0.85rem; font-family: monospace; }
        .btn-save { background-color: #10b981; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .btn-trash { background-color: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .status-banner { padding: 12px; border-radius: 6px; font-weight: bold; text-align: center; margin-bottom: 20px; font-size: 0.9rem; }
        .success { background-color: #10b981; color: white; }
        .error { background-color: #ef4444; color: white; }
    </style>
</head>
<body>

<div class="admin-layout">
    
    <!-- Status Action Notifications Banner -->
    <?php if (isset($_GET['status'])): ?>
        <div class="status-banner <?php echo ($_GET['status'] === 'error') ? 'error' : 'success'; ?>">
            <?php 
                if ($_GET['status'] === 'created') echo '✅ Fresh component registry created successfully.';
                elseif ($_GET['status'] === 'updated') echo '⚙️ Record parameters updated cleanly.';
                elseif ($_GET['status'] === 'deleted') echo '🗑️ Component dropped from active tracking.';
                elseif ($_GET['status'] === 'error') echo '❌ Operation Failed: ' . htmlspecialchars($_GET['msg']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Interactive Creation Form Section Panel -->
    <div class="form-panel">
        <h2 style="font-size: 1.25rem; font-weight: 800; color: #f97316; margin-bottom: 16px; border-bottom: 1px solid #1e293b; padding-bottom: 8px;">
            📥 Add New Component to Catalog
        </h2>
        <form action="admin.php" method="POST">
            <input type="hidden" name="action" value="create">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Component Name</label>
                    <input type="text" name="name" required placeholder="e.g. Carbon Brake Pads Pack">
                </div>
                <div class="form-group">
                    <label>OEM Serial Reference</label>
                    <input type="text" name="oem_number" required placeholder="e.g. BRK-990812">
                </div>
                <div class="form-group">
                    <label>Category Variety</label>
                    <select name="category">
                        <option value="brakes">Brakes & Suspension</option>
                        <option value="engine">Engine & Drivetrain</option>
                        <option value="electrical">Electrical & Lighting</option>
                        <option value="filters">Filters & Fluids</option>
                        <option value="body">Body Parts</option>
                    </select>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Price ($)</label>
                    <input type="number" name="price" step="0.01" required placeholder="49.99">
                </div>
                <div class="form-group">
                    <label>Warehouse Stock Count</label>
                    <input type="number" name="stock" min="0" required placeholder="15">
                </div>
                <div class="form-group">
                    <label>Condition Status</label>
                    <select name="item_condition">
                        <option value="New">New</option>
                        <option value="Refurbished">Refurbished</option>
                        <option value="Used">Used</option>
                    </select>
                </div>
            </div>

            <div class="form-grid" style="grid-template-columns: 2fr 1fr;">
                <div class="form-group">
                    <label>Fitment / Vehicle Compatibility</label>
                    <input type="text" name="compatibility" required placeholder="e.g. Toyota Corolla 2018-2022 / Universal">
                </div>
                <div class="form-group">
                    <label>Image Resource Reference (Optional)</label>
                    <input type="text" name="image_url" placeholder="public/part.jpg">
                </div>
            </div>

            <div class="form-group">
                <label>Technical Description</label>
                <textarea name="description" rows="3" required placeholder="Enter primary specifications, warranty info or manufacturing details..."></textarea>
            </div>

            <button type="submit" class="action-btn" style="margin-top: 16px; padding: 12px; font-weight: bold; width: auto;">
                + Save Item Registry
            </button>
        </form>
    </div>

    <!-- Active Inventory Listing Table -->
    <h2 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 16px;">📦 Active Inventory Catalog</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>OEM Ref</th>
                <th>Component Name</th>
                <th>Variety Group</th>
                <th>Price ($)</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inventory as $part): ?>
                <tr>
