<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My First Phone Shop</title>
    <style>
        /* 1. BASIC CSS STYLING */
        body { font-family: sans-serif; background-color: #f4f4f4; padding: 20px; }
        .shop-container { display: flex; gap: 20px; justify-content: center; }
        
        .product-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            width: 250px;
            text-align: center;
        }
        .product-card img { width: 100px; height: auto; margin-bottom: 10px; }
        .price { color: green; font-weight: bold; font-size: 1.2em; }
        .btn { 
            background-color: #007bff; color: white; 
            padding: 10px; text-decoration: none; border-radius: 5px; 
            display: inline-block; margin-top: 10px;
        }
    </style>
</head>
<body>

    <h1 style="text-align:center;">Welcome to PhoneStore</h1>

    <?php
    // 2. THE "FAKE" DATABASE (PHP Array)
    // Instead of SQL, we type the data right here.
    $products = [
        [
            "name" => "iPhone 15",
            "price" => 999,
            "image" => "https://upload.wikimedia.org/wikipedia/commons/thumb/c/c2/IPhone_15_logo.svg/100px-IPhone_15_logo.svg.png" 
        ],
        [
            "name" => "Samsung S24",
            "price" => 899,
            "image" => "https://upload.wikimedia.org/wikipedia/commons/thumb/2/23/Samsung_Galaxy_S24_Logo.svg/100px-Samsung_Galaxy_S24_Logo.svg.png"
        ],
        [
            "name" => "Google Pixel 8",
            "price" => 699,
            "image" => "https://upload.wikimedia.org/wikipedia/commons/thumb/3/3f/Google_Pixel_8_logo.svg/100px-Google_Pixel_8_logo.svg.png"
        ]
    ];
    ?>

    <div class="shop-container">
        <?php foreach ($products as $product): ?>
            
            <div class="product-card">
                <img src="<?php echo $product['image']; ?>" alt="Phone">
                
                <h3><?php echo $product['name']; ?></h3>
                <div class="price">$<?php echo $product['price']; ?></div>
                
                <a href="#" class="btn">Add to Cart</a>
            </div>

        <?php endforeach; ?>
    </div>

</body>
</html>