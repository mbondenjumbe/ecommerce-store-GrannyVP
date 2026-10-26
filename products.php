<?php
require_once 'config/db.php';
require_once 'components/navbar.php';

$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$queryStr = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($category)) {
    $queryStr .= " AND category = :category";
    $params[':category'] = $category;
}

if (!empty($search)) {
    $queryStr .= " AND (name LIKE :search OR oem_number LIKE :search OR compatibility LIKE :search)";
    $params[':search'] = "%$search%";
}

$queryStr .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($queryStr);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrannyVP - Parts Catalog Directory</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<div class="directory-container">
    <h1 class="directory-title">
        <?php 
        if (!empty($category)) {
            echo ucfirst(htmlspecialchars($category)) . " Inventory";
        } elseif (!empty($search)) {
            echo "Search Results for '" . htmlspecialchars($search) . "'";
        } else {
            echo "All Spare Parts Pool";
        }
        ?>
    </h1>

    <?php if (count($products) === 0) { ?>
        <div style="background-color: #0f172a; padding: 40px; border-radius: 8px; border: 1px solid #1e293b; text-align: center;">
            <p style="color: #94a3b8;">No components match your parameters currently.</p>
            <a href="products.php" class="action-btn" style="display: inline-block; max-width: 200px; margin-top: 16px;">View Full Inventory</a>
        </div>
    <?php } else { ?>
        <!-- This container matches the horizontal CSS rules exactly -->
        <div class="horizontal-parts-row">
            <?php foreach ($products as $part) { ?>
                <div class="product-card">
                    <div class="image-window">
                        <img src="<?php echo htmlspecialchars($part['image_url']); ?>" alt="<?php echo htmlspecialchars($part['name']); ?>">
                        <span class="condition-tag <?php echo strtolower($part['item_condition']); ?>">
                            <?php echo htmlspecialchars($part['item_condition']); ?>
                        </span>
                    </div>

                    <div class="card-details">
                        <div>
                            <div class="part-oem">OEM: <?php echo htmlspecialchars($part['oem_number']); ?></div>
                            <a href="product-detail.php?id=<?php echo $part['id']; ?>">
                                <h3 class="part-title"><?php echo htmlspecialchars($part['name']); ?></h3>
                            </a>
                        </div>

                        <div>
                            <div class="price-row">
                                <span class="price-value">$<?php echo number_format($part['price'], 2); ?></span>
                                <?php if ($part['stock'] > 0) { ?>
                                    <span class="stock-status in-stock"><?php echo $part['stock']; ?> In Stock</span>
                                <?php } else { ?>
                                    <span class="stock-status out-of-stock">Backorder</span>
                                <?php } ?>
                            </div>

                            <form action="cart.php" method="POST">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo $part['id']; ?>">
                                <button type="submit" class="action-btn" <?php echo ($part['stock'] == 0) ? 'disabled style="opacity: 0.4;"' : ''; ?>>
                                    Add To Garage Manifest
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<footer class="footer">
    <p>&copy; <?php echo date('Y'); ?> GrannyVP Spare Parts Shop.</p>
</footer>

</body>
</html>
