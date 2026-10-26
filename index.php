<?php
require_once 'config/db.php';
require_once 'components/navbar.php';

try {
    $stmt = $pdo->query("SELECT * FROM products WHERE stock > 5 ORDER BY RAND() LIMIT 4");
    $featuredParts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $featuredParts = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrannyVP - Quality Spare Parts Shop</title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .hero-banner {
            background: linear-gradient(rgba(15, 23, 42, 0.85), rgba(2, 6, 23, 0.95)), 
                        url('public/part.jpg');
            background-size: cover;
            background-position: center;
            border-radius: 12px;
            padding: 60px 40px;
            margin-bottom: 40px;
            border: 1px solid #1e293b;
        }
        .hero-title { font-size: 2.5rem; font-weight: 900; color: #ffffff; margin-bottom: 12px; line-height: 1.2; }
        .hero-subtitle { color: #94a3b8; font-size: 1.1rem; max-width: 600px; margin-bottom: 24px; }
        .cta-link-btn { background-color: #f97316; color: white; padding: 12px 24px; border-radius: 6px; font-weight: 700; font-size: 0.95rem; display: inline-block; }
        .cta-link-btn:hover { background-color: #ea580c; }
    </style>
</head>
<body>

<div class="directory-container">
    
    <div class="hero-banner">
        <h1 class="hero-title">Granny's Garage:<br>Quality Spare Parts for Classic & Modern Machines</h1>
        <p class="hero-subtitle">Instantly search and order across our live inventory track of 300 heavy duty components.</p>
        <a href="products.php" class="cta-link-btn">Shop Current Directory</a>
    </div>

    <h2 class="directory-title">Featured Mechanical Components</h2>
    
    <!-- This container matches the horizontal CSS rules exactly -->
    <div class="horizontal-parts-row">
        <?php foreach ($featuredParts as $part) { ?>
            <div class="product-card">
                <div class="image-window">
                    <img src="<?php echo htmlspecialchars($part['image_url']); ?>" alt="<?php echo htmlspecialchars($part['name']); ?>">
                    <span class="condition-tag <?php echo strtolower($part['item_condition']); ?>">
                        <?php echo htmlspecialchars($part['item_condition']); ?>
                    </span>
                </div>

                <div class="card-details">
                    <div>
                        <div class="part-oem">OEM SERIAL: <?php echo htmlspecialchars($part['oem_number']); ?></div>
                        <a href="product-detail.php?id=<?php echo $part['id']; ?>">
                            <h3 class="part-title"><?php echo htmlspecialchars($part['name']); ?></h3>
                        </a>
                    </div>

                    <div>
                        <div class="price-row">
                            <span class="price-value">$<?php echo number_format($part['price'], 2); ?></span>
                            <span class="stock-status in-stock"><?php echo $part['stock']; ?> Available</span>
                        </div>

                        <form action="cart.php" method="POST">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo $part['id']; ?>">
                            <button type="submit" class="action-btn">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

</div>

<footer class="footer">
    <p>&copy; <?php echo date('Y'); ?> GrannyVP Spare Parts Shop.</p>
</footer>

</body>
</html>
