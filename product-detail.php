<?php
require_once 'config/db.php';
require_once 'components/navbar.php';

// Capture the target component ID safely from the URL parameters
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Query the database for this specific item configuration
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute([':id' => $product_id]);
$part = $stmt->fetch(PDO::FETCH_ASSOC);

// Fallback if the part tracking token doesn't exist
if (!$part) {
    echo "<div class='directory-container' style='text-align:center; padding:60px;'>
            <h2 style='color:#ef4444;'>Component Matrix Not Found</h2>
            <p style='color:#94a3b8; margin:16px 0;'>The requested part tracking profile does not exist in inventory.</p>
            <a href='products.php' class='action-btn' style='display:inline-block; max-width:200px;'>Return to Catalog</a>
          </div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Inspect: <?php echo htmlspecialchars($part['name']); ?></title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .detail-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 30px; background-color: #0f172a; border: 1px solid #1e293b; border-radius: 12px; padding: 30px; }
        .detail-image-box { background-color: #020617; border: 1px solid #1e293b; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; min-height: 300px; position: relative; }
        .detail-image-box img { width: 100%; height: 100%; object-fit: cover; }
        .specs-table { width: 100%; margin-top: 20px; border-collapse: collapse; font-size: 0.9rem; }
        .specs-table td { padding: 12px; border-bottom: 1px solid #1e293b; }
        .specs-table td:first-child { color: #64748b; font-weight: bold; width: 35%; }
        .spec-badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; }
        @media (max-width: 768px) { .detail-layout { grid-template-columns: 1fr; padding: 20px; } }
    </style>
</head>
<body>

<div class="directory-container">
    <div class="detail-layout">
        
        <!-- Left Side: Component Image Display Window -->
        <div class="detail-image-box">
            <img src="<?php echo htmlspecialchars($part['image_url']); ?>" alt="Part Blueprint Profile">
            <span class="condition-tag <?php echo strtolower($part['item_condition']); ?>" style="position: absolute; top: 16px; left: 16px; font-size: 0.8rem; padding: 4px 12px;">
                <?php echo htmlspecialchars($part['item_condition']); ?>
            </span>
        </div>

        <!-- Right Side: Technical Specifications & Ordering Dash -->
        <div>
            <span style="color: #f97316; font-size: 0.8rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;">
                🛠️ Certified Spare Component
            </span>
            <h1 style="font-size: 1.8rem; font-weight: 900; margin: 8px 0 12px 0; color: white; line-height: 1.3;">
                <?php echo htmlspecialchars($part['name']); ?>
            </h1>

            <div style="font-size: 2rem; font-weight: 900; color: #f97316; margin-bottom: 20px;">
                $<?php echo number_format($part['price'], 2); ?>
            </div>

            <p style="color: #cbd5e1; font-size: 0.95rem; line-height: 1.6; margin-bottom: 24px;">
                <?php echo htmlspecialchars($part['description']); ?>
            </p>

            <!-- Technical Parameter Matrix Table -->
            <table class="specs-table">
                <tr>
                    <td>OEM Serial Reference</td>
                    <td style="font-family: monospace; font-weight: bold; color: #f1f5f9;">
                        <?php echo htmlspecialchars($part['oem_number']); ?>
                    </td>
                </tr>
                <tr>
                    <td>System Group Category</td>
                    <td style="text-transform: capitalize; color: #cbd5e1;">
                        <?php echo htmlspecialchars($part['category']); ?>
                    </td>
                </tr>
                <tr>
                    <td>Fitment Compatibility</td>
                    <td style="color: #10b981; font-weight: 600;">
                        <?php echo htmlspecialchars($part['compatibility']); ?>
                    </td>
                </tr>
                <tr>
                    <td>Warehouse Suffix Status</td>
                    <td>
                        <?php if ($part['stock'] > 0) { ?>
                            <span class="spec-badge in-stock"><?php echo $part['stock']; ?> Units Ready</span>
                        <?php } else { ?>
                            <span class="spec-badge out-of-stock">Backorder Waitlist</span>
                        <?php } ?>
                    </td>
                </tr>
            </table>

            <!-- Add to Garage Core Action Workflow -->
            <form action="cart.php" method="POST" style="margin-top: 30px;">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?php echo $part['id']; ?>">
                <button type="submit" class="action-btn" style="padding: 14px; font-size: 1rem; border-radius: 8px;"
                    <?php echo ($part['stock'] == 0) ? 'disabled style="opacity: 0.4; cursor: not-allowed;"' : ''; ?>>
                    🛒 Add Component to Order Manifest
                </button>
            </form>
        </div>

    </div>
</div>

<footer class="footer" style="margin-top: 60px;">
    <p>&copy; <?php echo date('Y'); ?> GrannyVP Spare Parts Shop. Real-Time SQL Query Instance.</p>
</footer>

</body>
</html>
