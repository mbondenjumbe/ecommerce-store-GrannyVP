<?php
require_once 'config/db.php';

try {
    // 1. Wipe out any existing mockup data safely
    $pdo->exec("TRUNCATE TABLE products");
    echo "🗑️ Existing records cleared safely.<br>";

    // 2. Setup generation parameters for massive structural varieties
    $categories = ['brakes', 'engine', 'electrical', 'filters', 'body'];

    $detailsMap = [
        'brakes' => [
            'names' => ['Heavy Duty Front Brake Caliper', 'Ceramic Brake Pad Kit', 'Slotted Brake Rotor Set', 'Braided Brake Hose', 'Brake Master Cylinder'],
            'prefix' => 'BRK-'
        ],
        'engine' => [
            'names' => ['High-Performance Carburetor Kit', 'Forged Piston Assembly', 'Timing Belt & Water Pump Kit', 'Cylinder Head Gasket', 'Oil Pump Drive Assembly'],
            'prefix' => 'ENG-'
        ],
        'electrical' => [
            'names' => ['High-Output Alternator', 'Heavy Duty Starter Motor', 'Platinum Spark Plug Pack', 'Ignition Coil Pack', 'LED Headlight Conversion Kit'],
            'prefix' => 'ELE-'
        ],
        'filters' => [
            'names' => ['Premium Engine Oil Filter', 'High-Flow Air Filter Element', 'Activated Carbon Cabin Filter', 'Inline Fuel Filter Assembly', 'Transmission Filter Kit'],
            'prefix' => 'FLT-'
        ],
        'body' => [
            'names' => ['Convex Side View Mirror Assembly', 'Reinforced Front Fender Liner', 'Gas-Charged Hood Strut Kit', 'Replacement Radiator Grille', 'Door Handle Assembly'],
            'prefix' => 'BDY-'
        ]
    ];

    $vehicles = [
        'Toyota Corolla 2015-2022', 
        'Honda Civic 2018-2024', 
        'Ford F-150 2010-2020', 
        'Mercedes-Benz C-Class 2016-2023',
        'Universal Fitment Configuration'
    ];

    $conditions = ['New', 'Refurbished', 'Used'];

    // 3. Prepare the massive bulk SQL transaction
    $sql = "INSERT INTO products (name, description, price, image_url, category, oem_number, compatibility, stock, item_condition) 
            VALUES (:name, :description, :price, :image_url, :category, :oem_number, :compatibility, :stock, :item_condition)";
    $stmt = $pdo->prepare($sql);

    $pdo->beginTransaction();

    // Loop to continuously build your unique items
    for ($i = 1; $i <= 300; $i++) {
        $randomCategory = $categories[array_rand($categories)];
        $pool = $detailsMap[$randomCategory];
        
        $baseName = $pool['names'][array_rand($pool['names'])];
        $productName = "$baseName (Part Variant #$i)";
        $description = "This heavy-duty $productName is engineered to exact factory specifications, ensuring peak operational efficiency and full compatibility with safety standards.";
        
        // Random price generation between \$15.00 and \$450.00
        $price = round(rand(1500, 45000) / 100, 2);
        $oemNumber = $pool['prefix'] . (100000 + $i);
        $compatibility = $vehicles[array_rand($vehicles)];
        $stock = rand(0, 50);
        $condition = $conditions[array_rand($conditions)];
        
        // Use a high-quality auto parts placeholder image from Unsplash
        $imageUrl = 'public/part.jpg';

        $stmt->execute([
            ':name' => $productName,
            ':description' => $description,
            ':price' => $price,
            ':image_url' => $imageUrl,
            ':category' => $randomCategory,
            ':oem_number' => $oemNumber,
            ':compatibility' => $compatibility,
            ':stock' => $stock,
            ':item_condition' => $condition
        ]);
    }

    $pdo->commit();
    echo "⚙️ Success! 300 unique e-commerce spare parts injected cleanly into <b>grannyvp_db</b> database.";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ Execution Error: " . $e->getMessage();
}
?>
