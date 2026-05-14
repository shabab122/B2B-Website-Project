<?php
// product_diss.php
// Database connection
$host = '127.0.0.1';
$dbname = 'db_trimart';
$username = 'root'; // Change as needed
$password = ''; // Change as needed

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
require_once 'dbconfig.php';

// Fetch products from database
$stmt = $pdo->prepare("
    SELECT p.*, si.price, c.company_name 
    FROM products p 
    JOIN supplier_inventory si ON p.product_id = si.product_id 
    JOIN companies c ON si.supplier_company_id = c.company_id 
    WHERE si.status = 'Available' 
    ORDER BY p.product_id
");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For demo purposes, if no products in database, create sample data with images
if (empty($products)) {
    $products = [
        [
            'sku' => '#UPY34',
            'product_name' => '3/4" New Cotton Fabric',
            'price' => 20.00,
            'company_name' => 'beshideshi',
            'image' => 'https://www.beshideshi.com/wp-content/uploads/2024/11/01.jpg'
        ],
        [
            'sku' => '#LD534',
            'product_name' => '3/4" New Cotton Lanyards',
            'price' => 18.00,
            'company_name' => 'bashabangladesh',
            'image' => 'https://bashabangladesh.com/wp-content/uploads/2023/03/Lanyard-1.jpg'
        ],
        [
            'sku' => '#TBUSB50',
            'product_name' => 'Samia Craft Stylish Everyday Canvas Tote Bag For Women with Zipper ',
            'price' => 70.00,
            'company_name' => 'Samia Craft',
            'image' => 'https://stx-v3-static-assets.obs.as-south-208.rcloud.reddotdigitalit.com/samiacraft/images/products/320933083cf94a3b992f/1740229906986_1.webp'
        ],
        [
            'sku' => '#19942',
            'product_name' => ' Time Scale Transparent 500ml Water Drinking Bottle',
            'price' => 58.00,
            'company_name' => 'BdStall',
            'image' => 'https://cdn.bdstall.com/product-image/giant_277762.jpg'
        ],
        [
            'sku' => '#Silk',
            'product_name' => 'Silk Organza Fabric Purple Color Saree',
            'price' => 400.00,
            'company_name' => 'beshideshi',
            'image' => 'https://www.beshideshi.com/wp-content/uploads/2025/08/43-2.jpg'
        ],
        [
            'sku' => '#LDUS34',
            'product_name' => '3/4 Inch Dye Sublimation Lanyards',
            'price' => 14.00,
            'company_name' => 'LanyardsBD',
            'image' => 'https://tse1.mm.bing.net/th/id/OIP.3nVVRTiooWrVL7asRBXrQAHaHa?cb=12&rs=1&pid=ImgDetMain&o=7&rm=3'
        ],
        [
            'sku' => '#Seren',
            'product_name' => '11 oz. Ceramic Mug',
            'price' => 130.00,
            'company_name' => 'MonnoShop',
            'image' => 'https://monno-shop.com/cdn/shop/files/Expression-Mugs-1X1-1_e7fdb8f1-40b7-40e1-82a1-d9f8b1c867ce_360x.jpg?v=1758792788'
        ],
        [
            'sku' => '#Overlay',
            'product_name' => '18 oz. Bistro Ceramic Color Coded Coffee Mugs',
            'price' => 100.00,
            'company_name' => 'MonnoShop',
            'image' => 'https://monno-shop.com/cdn/shop/files/Black-Overlay-Mug-1X1-2_360x.jpg?v=1757843329'
        ],
        [
            'sku' => '#2SR9A15',
            'product_name' => "Badge bundle made in Bangladesh label icon emblem isolated",
            'price' => 25.70,
            'company_name' => 'Alamy',
            'image' => 'https://c7.alamy.com/comp/2SR9A15/badge-bundle-made-in-bangladesh-label-icon-emblem-isolated-on-white-background-vector-illustration-2SR9A15.jpg'
        ],
        [
            'sku' => '#WBSG1/2',
            'product_name' => 'Squid Game Black 1/2 inch',
            'price' => 80.00,
            'company_name' => 'Wristbands-House',
            'image' => 'https://wristbands-house.com/media/sites/product-type/IMG_5439.jpeg'
        ],
        [
            'sku' => '#LDEDS34',
            'product_name' => '3/4" Double Ended Dye-Sublimation Lanyard',
            'price' => 10.35,
            'company_name' => 'LanyardsBD',
            'image' => 'https://th.bing.com/th/id/OIP.E_L2i_7cBqXY48lVmTDGtQHaHa?o=7&cb=12rm=3&rs=1&pid=ImgDetMain&o=7&rm=3'
        ],
        [
            'sku' => '#FRW00913',
            'product_name' => 'Ceramic Rugged Usable Solid White Mug',
            'price' => 119.009,
            'company_name' => 'Feri Wala Limited',
            'image' => 'https://images.othoba.com/images/thumbs/0649875_ceramic-rugged-usable-solid-white-mug.jpeg'
        ],
        [
            'sku' => '#MugTumbler',
            'product_name' => ' Rambler Stainless Steel Vacuum Insulated Tumbler',
            'price' => 48.98,
            'company_name' => 'XBRANDS',
            'image' => 'https://m.media-amazon.com/images/I/51cNG0PFtdL._SL1500_.jpg'
        ],
        [
            'sku' => '#BRODI',
            'product_name' => 'Drinkware 40oz Mug Tumbler FlowState Stanley',
            'price' => 300.94,
            'company_name' => 'DARAZ',
            'image' => 'https://tse2.mm.bing.net/th/id/OIP.HmeSWFVYzXzXyWqbimvBBAHaHa?cb=12&w=1080&h=1080&rs=1&pid=ImgDetMain&o=7&rm=3'
        ],
        [
            'sku' => '#EasyBre',
            'product_name' => 'Baahs Easy Breezy Jute Fanny Pack',
            'price' => '410.00',
            'company_name' => 'BAAH',
            'image' => 'https://ds.rokomari.store/rokomari110/ProductNew20190903/260X372/Baahs_Checkered_Jute_Fanny_Pack-Baah-c3f7e-325482.jpg'
        ],
        [
            'sku' => '#BCROV09',
            'product_name' => 'Oval Carabiner Retractable Badge Reel w/ Belt Clip',
            'price' => 10.86,
            'company_name' => 'AsianPrinting',
            'image' => 'https://tse3.mm.bing.net/th/id/OIP.CNGgTy_OIAyh093S4YH8VAHaHa?cb=12&rs=1&pid=ImgDetMain&o=7&rm=3'
        ],
        [
            'sku' => '#WBCR7',
            'product_name' => 'Cristiano Ronaldo Black 1/2 inch',
            'price' => 45.00,
            'company_name' => 'Wristbands-House',
            'image' => 'https://wristbands-house.com/media/sites/product-type/FullSizeRender_h1G2ZIT.jpeg'
        ],
        [
            'sku' => '#SCBM319',
            'product_name' => 'Kalonbd retro one-shoulder handbags- Black',
            'price' => 400.15,
            'company_name' => 'Kalonbd',
            'image' => 'https://kalonbd.com/cdn/shop/files/imgi_16_O1CN01kmqVef1FCbXN0Y5M3__2216687410451-0-cib_jpg.webp?v=1754221973&width=800'
        ],
        [
            'sku' => '#TBUSB800',
            'product_name' => 'Kalonbd flowers art handbag Decorated (15 x 16)',
            'price' => 650.00,
            'company_name' => 'Kalonbd',
            'image' => 'https://kalonbd.com/cdn/shop/files/imgi_69_Hb728c2660a884e9eaa10fff99fae88b7E.jpg?v=1753034069&width=800'
        ],
        [
            'sku' => '#USBI2004',
            'product_name' => 'Swivel USB Drive',
            'price' => 150.00,
            'company_name' => 'BAESUS',
            'image' => 'https://www.qualitylogoproducts.com/custom-flash-drives/swivel-flash-drive-hq-647946.jpg'
        ]
    ];
} else {
    // Add image URLs to products from database
    $productImages = [
        'Lanyard' => 'https://images.unsplash.com/photo-1589330273594-fade1ee91647?w=400&h=300&fit=crop',
        'Tote Bag' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400&h=300&fit=crop',
        'Water Bottle' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=400&h=300&fit=crop',
        'T-shirt' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&h=300&fit=crop',
        'Mug' => 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400&h=300&fit=crop',
        'Wristband' => 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=400&h=300&fit=crop',
        'Badge' => 'https://images.unsplash.com/photo-1589330273594-fade1ee91647?w=400&h=300&fit=crop',
        'Tumbler' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=400&h=300&fit=crop',
        'Bag' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400&h=300&fit=crop',
        'USB' => 'https://images.unsplash.com/photo-1589330273594-fade1ee91647?w=400&h=300&fit=crop'
    ];
    
    foreach($products as &$product) {
        $category = strtolower($product['category'] ?? '');
        if (strpos($category, 'lanyard') !== false) {
            $product['image'] = $productImages['Lanyard'];
        } elseif (strpos($category, 'bag') !== false) {
            $product['image'] = $productImages['Tote Bag'];
        } elseif (strpos($category, 'bottle') !== false || strpos($category, 'drinkware') !== false) {
            $product['image'] = $productImages['Water Bottle'];
        } elseif (strpos($category, 'shirt') !== false || strpos($category, 'apparel') !== false) {
            $product['image'] = $productImages['T-shirt'];
        } elseif (strpos($category, 'mug') !== false) {
            $product['image'] = $productImages['Mug'];
        } elseif (strpos($category, 'wristband') !== false) {
            $product['image'] = $productImages['Wristband'];
        } else {
            $product['image'] = $productImages['Badge']; // Default image
        }
    }
    unset($product); // Break the reference
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriMart - Top Sellers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .product-card {
            transition: all 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .brand-tag {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.9);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 10;
        }
        
        .price-tag {
            color: #dc2626;
            font-weight: 700;
        }
        
        .learn-more-btn {
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #991b1b, #7f1d1d);
        }
        
        .learn-more-btn:hover {
            background: linear-gradient(135deg, #7f1d1d, #450a0a);
            transform: scale(1.05);
        }
        
        .category-btn {
            transition: all 0.2s ease;
        }
        
        .category-btn:hover, .category-btn.active {
            background-color: #991b1b;
            color: white;
        }
        
        .search-box {
            transition: all 0.3s ease;
        }
        
        .search-box:focus {
            box-shadow: 0 0 0 3px rgba(153, 27, 27, 0.3);
            border-color: #991b1b;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
        
        .stagger-animation > * {
            opacity: 0;
        }
        
        .stagger-animation > *:nth-child(1) { animation-delay: 0.1s; }
        .stagger-animation > *:nth-child(2) { animation-delay: 0.2s; }
        .stagger-animation > *:nth-child(3) { animation-delay: 0.3s; }
        .stagger-animation > *:nth-child(4) { animation-delay: 0.4s; }
        .stagger-animation > *:nth-child(5) { animation-delay: 0.5s; }
        .stagger-animation > *:nth-child(6) { animation-delay: 0.6s; }
        .stagger-animation > *:nth-child(7) { animation-delay: 0.7s; }
        .stagger-animation > *:nth-child(8) { animation-delay: 0.8s; }
        .stagger-animation > *:nth-child(9) { animation-delay: 0.9s; }
        .stagger-animation > *:nth-child(10) { animation-delay: 1.0s; }
        
        .product-image {
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .product-image {
            transform: scale(1.05);
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-10">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-red-800 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-lg">TM</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800">TriMart</h1>
                </div>
                
                <!-- Navigation -->
                <nav class="flex items-center space-x-6">
                    <div class="relative group">
                        <button class="flex items-center space-x-1 text-gray-700 hover:text-red-700 font-medium py-2">
                            <span>CATEGORIES</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="absolute hidden group-hover:block bg-white shadow-lg rounded-lg mt-2 w-48 py-2 z-20">
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-700">All Products</a>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-700">Lanyards</a>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-700">Tote Bags</a>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-700">Drinkware</a>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-700">Apparel</a>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-700">Badges & Reels</a>
                        </div>
                    </div>
                    
                    <!-- Search Box -->
                    <div class="relative">
                        <input type="text" placeholder="Search products..." class="search-box pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-700 w-64">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </nav>
                
                <!-- User Actions -->
                <div class="flex items-center space-x-4">
                    <button class="text-gray-600 hover:text-red-700">
                        <i class="fas fa-shopping-cart text-xl"></i>
                    </button>
                    <button class="text-gray-600 hover:text-red-700">
                        <i class="fas fa-user text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Filter and Sort Section -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4">
                <span class="text-gray-700 font-medium">Filter Results:</span>
                <div class="flex space-x-2">
                    <button class="category-btn active px-4 py-2 bg-red-100 text-red-800 rounded-lg font-medium">All</button>
                    <button class="category-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium">Lanyards</button>
                    <button class="category-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium">Bags</button>
                    <button class="category-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium">Drinkware</button>
                    <button class="category-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium">Apparel</button>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <span class="text-gray-700"><?php echo count($products); ?> Products</span>
                <div class="flex items-center space-x-2">
                    <span class="text-gray-700">Sort by:</span>
                    <select class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-red-700">
                        <option>Popularity: High</option>
                        <option>Price: Low to High</option>
                        <option>Price: High to Low</option>
                        <option>Newest First</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 stagger-animation">
            <?php foreach($products as $product): ?>
            <div class="product-card bg-white fade-in">
                <div class="relative h-48 bg-gray-200 flex items-center justify-center overflow-hidden">
                    <!-- Product Image -->
                    <img 
                        src="<?php echo $product['image'] ?? 'https://images.unsplash.com/photo-1589330273594-fade1ee91647?w=400&h=300&fit=crop'; ?>" 
                        alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                        class="product-image w-full h-full object-cover"
                    >
                    <div class="brand-tag"><?php echo htmlspecialchars($product['company_name']); ?></div>
                </div>
                <div class="p-4">
                    <div class="text-sm text-gray-500 font-medium mb-1"><?php echo htmlspecialchars($product['sku']); ?></div>
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                    <div class="flex justify-between items-center mt-4">
                        <div class="price-tag text-lg">As low as $<?php echo number_format($product['price'], 2); ?></div>
                        <button class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition-colors font-medium">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <div class="flex justify-center mt-12">
            <div class="flex space-x-2">
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300">Previous</button>
                <button class="px-4 py-2 bg-red-700 text-white rounded-lg font-medium">1</button>
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300">2</button>
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300">3</button>
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300">Next</button>
            </div>
        </div>
    </main>

    <!-- New Footer Section -->
    <footer class="bg-gray-900 text-white animate__animated animate__fadeInUp">
        <div class="container mx-auto px-4 py-12">
            <!-- Main Footer Links -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <!-- Solutions Column -->
                <div class="animate__animated animate__fadeInUp animate__delay-1s">
                    <h3 class="text-xl font-bold mb-6 text-red-400 flex items-center">
                        <i class="fas fa-cogs mr-3"></i>
                        Solutions
                    </h3>
                    <ul class="space-y-3">
                        <li>
                            <a href="#" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center group">
                                <i class="fas fa-chevron-right text-red-500 mr-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                                For Distributors
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center group">
                                <i class="fas fa-chevron-right text-red-500 mr-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                                For Suppliers
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center group">
                                <i class="fas fa-chevron-right text-red-500 mr-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                                For Shippers
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Education Column -->
                <div class="animate__animated animate__fadeInUp animate__delay-2s">
                    <h3 class="text-xl font-bold mb-6 text-red-300 flex items-center">
                        <i class="fas fa-graduation-cap mr-3"></i>
                        Education
                    </h3>
                    <ul class="space-y-3">
                        <li>
                            <a href="#" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center group">
                                <i class="fas fa-chevron-right text-red-400 mr-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                                United International University
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center group">
                                <i class="fas fa-chevron-right text-red-400 mr-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                                Market Research
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center group">
                                <i class="fas fa-chevron-right text-red-400 mr-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                                Industry News
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- About TriMart Column -->
                <div class="animate__animated animate__fadeInUp animate__delay-3s">
                    <h3 class="text-xl font-bold mb-6 text-red-200 flex items-center">
                        <i class="fas fa-info-circle mr-3"></i>
                        About TriMart
                    </h3>
                    <ul class="space-y-3">
                        <li>
                            <a href="#" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center group">
                                <i class="fas fa-chevron-right text-red-300 mr-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                                Contact Us
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center group">
                                <i class="fas fa-chevron-right text-red-300 mr-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                                Our Story
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center group">
                                <i class="fas fa-chevron-right text-red-300 mr-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                                Meet Our Team
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-700 my-8"></div>

            <!-- Bottom Footer -->
            <div class="flex flex-col md:flex-row justify-between items-center animate__animated animate__fadeIn">
                <!-- Copyright -->
                <div class="mb-4 md:mb-0">
                    <p class="text-gray-400 text-sm">
                        ©2025, The TriMart Marketplace®. All Rights Reserved.
                    </p>
                </div>

                <!-- Policy Links -->
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300 text-sm">
                        Terms of Use
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300 text-sm">
                        Privacy Policy
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300 text-sm">
                        Cookie Policy
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Live Chat Widget -->
    <div class="fixed bottom-6 right-6 z-20">
        <div class="bg-red-700 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg cursor-pointer hover:bg-red-800 transition-colors">
            <i class="fas fa-comment text-xl"></i>
        </div>
        <div class="absolute bottom-16 right-0 w-80 bg-white shadow-xl rounded-lg hidden">
            <div class="bg-red-700 text-white p-4 rounded-t-lg">
                <h3 class="font-semibold">We're here to help!</h3>
                <p class="text-red-100 text-sm">Ask us anything</p>
            </div>
            <div class="p-4 h-64 overflow-y-auto">
                <div class="bg-gray-100 rounded-lg p-3 mb-4 max-w-xs">
                    <p class="text-gray-700">Welcome to our site, if you need help simply reply to this message, we are online and ready to help.</p>
                </div>
            </div>
            <div class="p-4 border-t border-gray-200">
                <div class="flex space-x-2">
                    <input type="text" placeholder="Type your message..." class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-red-700">
                    <button class="bg-red-700 text-white px-4 py-2 rounded-lg hover:bg-red-800 transition-colors">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple animation trigger
        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('.fade-in');
            animatedElements.forEach(el => {
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            });
            
            // Live chat toggle
            const chatButton = document.querySelector('.fixed .bg-red-700');
            const chatWindow = document.querySelector('.fixed .absolute');
            
            chatButton.addEventListener('click', function() {
                chatWindow.classList.toggle('hidden');
            });
            
            // Category filter buttons
            const categoryButtons = document.querySelectorAll('.category-btn');
            categoryButtons.forEach(button => {
                button.addEventListener('click', function() {
                    categoryButtons.forEach(btn => btn.classList.remove('active', 'bg-red-100', 'text-red-800'));
                    categoryButtons.forEach(btn => btn.classList.add('bg-gray-100', 'text-gray-700'));
                    
                    this.classList.remove('bg-gray-100', 'text-gray-700');
                    this.classList.add('active', 'bg-red-100', 'text-red-800');
                });
            });
        });
    </script>
</body>
</html>