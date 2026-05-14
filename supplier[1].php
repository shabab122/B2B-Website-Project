<?php
session_start();

// --- 1. HANDLE LOGOUT ---
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: sin[1].php');
    exit;
}

// --- 2. PROTECT THE PAGE ---
if (!isset($_SESSION['user_id'])) {
    header("Location: sin[1].php");
    exit();
}

if ($_SESSION['role'] !== 'Supplier') {
    session_unset();
    session_destroy();
    header("Location: sin[1].php?error=access_denied");
    exit();
}

// --- 3. INCLUDE DATABASE CONNECTION ---
require_once 'dbconfig.php';

// Get user data from session
$supplier_name = htmlspecialchars($_SESSION['full_name']);
$supplier_email = htmlspecialchars($_SESSION['email']);
$company_id = $_SESSION['company_id'];

// --- 4. HANDLE INVENTORY FORM SUBMISSION ---
$inventory_message = "";
$inventory_message_type = ""; // success or error

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $product_name = trim($_POST['product_name']);
    $product_sku = trim($_POST['product_sku']);
    $product_category = trim($_POST['product_category']);
    $product_price = floatval($_POST['product_price']);
    $product_stock = intval($_POST['product_stock']);
    $product_description = trim($_POST['product_description']);
    
    try {
        $conn = getDBConnection();
        $conn->begin_transaction();
        
        // First, insert into products table
        $stmt_product = $conn->prepare("INSERT INTO products (product_name, description, sku, category) VALUES (?, ?, ?, ?)");
        $stmt_product->bind_param("ssss", $product_name, $product_description, $product_sku, $product_category);
        $stmt_product->execute();
        $new_product_id = $conn->insert_id;
        $stmt_product->close();
        
        // Then, insert into supplier_inventory table
        $status = $product_stock > 0 ? 'Available' : 'OutOfStock';
        $stmt_inventory = $conn->prepare("INSERT INTO supplier_inventory (supplier_company_id, product_id, stock_quantity, price, status) VALUES (?, ?, ?, ?, ?)");
        $stmt_inventory->bind_param("iiids", $company_id, $new_product_id, $product_stock, $product_price, $status);
        $stmt_inventory->execute();
        $stmt_inventory->close();
        
        $conn->commit();
        $inventory_message = "Product added successfully!";
        $inventory_message_type = "success";
        
    } catch (Exception $e) {
        $conn->rollback();
        $inventory_message = "Error adding product: " . $e->getMessage();
        $inventory_message_type = "error";
        error_log("Inventory add error: " . $e->getMessage());
    }
}

// --- 5. FETCH REAL DATA FROM DATABASE ---
try {
    $conn = getDBConnection();
    
    // Fetch total orders for this supplier
    $stmt_orders = $conn->prepare("SELECT COUNT(*) FROM orders WHERE supplier_company_id = ?");
    $stmt_orders->bind_param("i", $company_id);
    $stmt_orders->execute();
    $totalOrders = $stmt_orders->get_result()->fetch_row()[0];
    $stmt_orders->close();
    
    // Fetch inventory items count
    $stmt_inventory = $conn->prepare("SELECT COUNT(*) FROM supplier_inventory WHERE supplier_company_id = ?");
    $stmt_inventory->bind_param("i", $company_id);
    $stmt_inventory->execute();
    $inventoryItems = $stmt_inventory->get_result()->fetch_row()[0];
    $stmt_inventory->close();
    
    // Fetch pending payments (orders with pending status)
    $stmt_payments = $conn->prepare("
        SELECT COUNT(*) 
        FROM orders o 
        LEFT JOIN transactions t ON o.order_id = t.order_id 
        WHERE o.supplier_company_id = ? 
        AND (t.status IS NULL OR t.status = 'Pending')
    ");
    $stmt_payments->bind_param("i", $company_id);
    $stmt_payments->execute();
    $pendingOrdersCount = $stmt_payments->get_result()->fetch_row()[0];
    $stmt_payments->close();
    
    // Calculate pending payment amount
    $pendingPayments = "$" . number_format($pendingOrdersCount * 1500, 2);
    
    // Fetch additional stats for the dashboard
    $stmt_products = $conn->prepare("SELECT COUNT(*) FROM supplier_inventory WHERE supplier_company_id = ? AND stock_quantity > 0");
    $stmt_products->bind_param("i", $company_id);
    $stmt_products->execute();
    $totalProducts = $stmt_products->get_result()->fetch_row()[0];
    $stmt_products->close();
    
    // Fetch pending orders count
    $stmt_pending_orders = $conn->prepare("SELECT COUNT(*) FROM orders WHERE supplier_company_id = ? AND order_status = 'Pending'");
    $stmt_pending_orders->bind_param("i", $company_id);
    $stmt_pending_orders->execute();
    $pendingOrders = $stmt_pending_orders->get_result()->fetch_row()[0];
    $stmt_pending_orders->close();
    
    // Fetch active clients (distributors with connections)
    $stmt_clients = $conn->prepare("
        SELECT COUNT(DISTINCT distributor_company_id) 
        FROM connections 
        WHERE supplier_company_id = ? AND status = 'Connected'
    ");
    $stmt_clients->bind_param("i", $company_id);
    $stmt_clients->execute();
    $activeClients = $stmt_clients->get_result()->fetch_row()[0];
    $stmt_clients->close();
    
    // Fetch revenue (sum of completed transactions)
    $stmt_revenue = $conn->prepare("
        SELECT COALESCE(SUM(amount), 0) 
        FROM transactions t 
        JOIN orders o ON t.order_id = o.order_id 
        WHERE o.supplier_company_id = ? AND t.status = 'Completed'
    ");
    $stmt_revenue->bind_param("i", $company_id);
    $stmt_revenue->execute();
    $revenue = $stmt_revenue->get_result()->fetch_row()[0];
    $stmt_revenue->close();
    
    // Fetch available stocks for inventory section
    $stmt_stocks = $conn->prepare("
        SELECT p.product_id, p.product_name, p.sku, si.stock_quantity, si.price, si.status 
        FROM supplier_inventory si 
        JOIN products p ON si.product_id = p.product_id 
        WHERE si.supplier_company_id = ? 
        ORDER BY si.last_updated DESC 
        LIMIT 3
    ");
    $stmt_stocks->bind_param("i", $company_id);
    $stmt_stocks->execute();
    $availableStocks = $stmt_stocks->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_stocks->close();
    
    // Fetch pending quotes
    $stmt_quotes = $conn->prepare("
        SELECT q.quote_id, c.company_name, qi.quantity, qi.offered_price 
        FROM quotes q 
        JOIN companies c ON q.distributor_company_id = c.company_id 
        JOIN quote_items qi ON q.quote_id = qi.quote_id 
        WHERE q.supplier_company_id = ? AND q.status = 'Requested' 
        LIMIT 2
    ");
    $stmt_quotes->bind_param("i", $company_id);
    $stmt_quotes->execute();
    $pendingQuotes = $stmt_quotes->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_quotes->close();
    
} catch (Exception $e) {
    // If database queries fail, use default values
    error_log("Dashboard data fetch error: " . $e->getMessage());
    $totalOrders = 0;
    $inventoryItems = 0;
    $pendingPayments = "$0.00";
    $totalProducts = 0;
    $pendingOrders = 0;
    $activeClients = 0;
    $revenue = 0;
    $availableStocks = [];
    $pendingQuotes = [];
}

// Format revenue for display
$formattedRevenue = "$" . number_format($revenue, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard | Trimart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#991b1b',
                        'primary-600': '#0b0707ff',
                        'primary-700': '#B91C1C',
                    },
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar { transition: all 0.3s ease; }
        .sidebar.collapsed { width: 70px; }
        .sidebar.collapsed .sidebar-text { display: none; }
        .main-content { transition: all 0.3s ease; flex:1; padding: 20px; overflow-y:auto; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        body { font-family: 'Poppins', sans-serif; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1000; justify-content: center; align-items: center; }
        .modal-content { background-color: white; border-radius: 8px; max-width: 90%; max-height: 90%; overflow-y: auto; }
        .dropdown-menu { display: none; position: absolute; top: 100%; right: 0; background-color: white; min-width: 160px; box-shadow: 0 8px 16px rgba(0,0,0,0.1); border-radius: 8px; z-index: 1000; margin-top: 8px; }
        .dropdown-menu.show { display: block; }
        .dropdown-item { display: block; padding: 10px 16px; text-decoration: none; color: #333; border-bottom: 1px solid #f0f0f0; }
        .dropdown-item:hover { background-color: #f8f9fa; }
        .dropdown-item:last-child { border-bottom: none; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <div class="sidebar bg-primary text-white w-64 flex flex-col">
            <div class="p-4 flex items-center justify-between border-b border-primary-700">
                <div class="flex items-center">
                    <i class="fas fa-warehouse text-2xl mr-3"></i>
                    <span class="sidebar-text text-xl font-bold">Trimart Supplier</span>
                </div>
                <button id="toggleSidebar" class="text-white focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="#" class="flex items-center p-3 rounded-lg bg-primary-700">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            <span class="sidebar-text">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="#inventory" class="flex items-center p-3 rounded-lg hover:bg-primary-700">
                            <i class="fas fa-boxes mr-3"></i>
                            <span class="sidebar-text">Inventory & Stock</span>
                        </a>
                    </li>
                    <li>
                        <a href="#purchase-orders" class="flex items-center p-3 rounded-lg hover:bg-primary-700">
                            <i class="fas fa-file-invoice-dollar mr-3"></i>
                            <span class="sidebar-text">Purchase Orders</span>
                        </a>
                    </li>
                    <li>
                        <a href="#production" class="flex items-center p-3 rounded-lg hover:bg-primary-700">
                            <i class="fas fa-industry mr-3"></i>
                            <span class="sidebar-text">Orders in Production</span>
                        </a>
                    </li>
                    <li>
                        <a href="#clients" class="flex items-center p-3 rounded-lg hover:bg-primary-700">
                            <i class="fas fa-users mr-3"></i>
                            <span class="sidebar-text">Client Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="#payments" class="flex items-center p-3 rounded-lg hover:bg-primary-700">
                            <i class="fas fa-money-bill-wave mr-3"></i>
                            <span class="sidebar-text">Payment & Billing</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="p-4 border-t border-primary-700">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="ml-3 sidebar-text">
                        <p class="font-medium"><?php echo $supplier_name; ?></p>
                        <p class="text-sm text-red-200"><?php echo $supplier_email; ?></p>
                    </div>
                </div>
                <!-- FIXED LOGOUT LINK - Now goes to sin[1].php -->
                <a href="?action=logout" class="mt-2 block text-sm text-white hover:underline">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </div>
        

        <div class="main-content">
            <header class="bg-white shadow-sm z-10">
                <div class="flex justify-between items-center p-4">
                    <h1 class="text-2xl font-bold text-gray-800">Supplier Dashboard</h1>
                    <div class="flex items-center space-x-4">
                        <h1 class="text-3xl font-bold">Welcome, <?php echo $supplier_name; ?>!</h1>
                        <div class="relative">
                            <i class="fas fa-bell text-gray-500 text-xl"></i>
                            <span class="absolute -top-1 -right-1 bg-primary text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">3</span>
                        </div>
                        <span class="font-medium text-gray-700">Supplier</span>
                    </div>
                </div>
            </header>

            <!-- Inventory Message Display -->
            <?php if ($inventory_message): ?>
                <div class="mb-4 p-4 rounded-lg <?php echo $inventory_message_type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                    <?php echo $inventory_message; ?>
                </div>
            <?php endif; ?>

            <!-- Main Dashboard Stats -->
            <section id="dashboard" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg card-hover shadow">
                    <h2 class="text-xl font-semibold mb-2">Total Orders</h2>
                    <p class="text-3xl font-bold"><?php echo $totalOrders; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg card-hover shadow">
                    <h2 class="text-xl font-semibold mb-2">Inventory Items</h2>
                    <p class="text-3xl font-bold"><?php echo $inventoryItems; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg card-hover shadow">
                    <h2 class="text-xl font-semibold mb-2">Pending Payments</h2>
                    <p class="text-3xl font-bold"><?php echo $pendingPayments; ?></p>
                </div>
            </section>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-md p-6 card-hover">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-500 text-sm">Total Products</p>
                            <h3 class="text-2xl font-bold mt-1"><?php echo $totalProducts; ?></h3>
                        </div>
                        <div class="p-3 bg-red-100 rounded-lg">
                            <i class="fas fa-box text-primary text-xl"></i>
                        </div>
                    </div>
                    <p class="text-green-500 text-sm mt-2"><i class="fas fa-arrow-up mr-1"></i> Live data</p>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6 card-hover">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-500 text-sm">Pending Orders</p>
                            <h3 class="text-2xl font-bold mt-1"><?php echo $pendingOrders; ?></h3>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-clipboard-list text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-red-500 text-sm mt-2"><i class="fas fa-clock mr-1"></i> Needs attention</p>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6 card-hover">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-500 text-sm">Active Clients</p>
                            <h3 class="text-2xl font-bold mt-1"><?php echo $activeClients; ?></h3>
                        </div>
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-users text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-green-500 text-sm mt-2"><i class="fas fa-handshake mr-1"></i> Connected distributors</p>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6 card-hover">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-500 text-sm">Revenue</p>
                            <h3 class="text-2xl font-bold mt-1"><?php echo $formattedRevenue; ?></h3>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-green-500 text-sm mt-2"><i class="fas fa-chart-line mr-1"></i> Total earnings</p>
                </div>
            </div>
            
            <!-- Inventory & Stock Management Section -->
            <section id="inventory" class="mb-12">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Inventory & Stock Management</h2>
                    <button onclick="openAddProductModal()" class="bg-primary hover:bg-primary-600 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add Product
                    </button>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Available Stocks -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="bg-primary text-white p-4">
                            <h3 class="font-bold flex items-center">
                                <i class="fas fa-boxes mr-2"></i> Available Stocks
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-4" id="available-stocks-list">
                                <?php if (!empty($availableStocks)): ?>
                                    <?php foreach ($availableStocks as $stock): ?>
                                        <div class="flex justify-between items-center border-b pb-3">
                                            <div>
                                                <p class="font-medium"><?php echo htmlspecialchars($stock['product_name']); ?></p>
                                                <p class="text-sm text-gray-500">SKU: <?php echo htmlspecialchars($stock['sku']); ?></p>
                                                <p class="text-sm text-gray-500">Price: $<?php echo number_format($stock['price'], 2); ?></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-bold"><?php echo $stock['stock_quantity']; ?> units</p>
                                                <?php 
                                                $statusClass = $stock['status'] == 'Available' ? 'bg-green-100 text-green-800' : 
                                                             ($stock['status'] == 'OutOfStock' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
                                                ?>
                                                <span class="status-badge <?php echo $statusClass; ?>"><?php echo $stock['status']; ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4 text-gray-500">
                                        <i class="fas fa-box-open text-3xl mb-2"></i>
                                        <p>No products in inventory</p>
                                        <p class="text-sm">Click "Add Product" to get started</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button class="w-full mt-4 text-primary hover:text-primary-700 font-medium flex items-center justify-center">
                                View All <i class="fas fa-chevron-right ml-2"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Add Products Form -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="bg-green-600 text-white p-4">
                            <h3 class="font-bold flex items-center">
                                <i class="fas fa-plus-circle mr-2"></i> Add Products
                            </h3>
                        </div>
                        <div class="p-4">
                            <form method="POST" action="" class="space-y-4">
                                <input type="hidden" name="add_product" value="1">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                                    <input type="text" name="product_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
                                    <input type="text" name="product_sku" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                                    <select name="product_category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                                        <option value="">Select Category</option>
                                        <option value="Apparel">Apparel</option>
                                        <option value="Drinkware">Drinkware</option>
                                        <option value="Stationery">Stationery</option>
                                        <option value="Tech Accessories">Tech Accessories</option>
                                        <option value="Promotional Items">Promotional Items</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea name="product_description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Product description..."></textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Price *</label>
                                        <input type="number" name="product_price" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="0.00" min="0" step="0.01" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Initial Stock *</label>
                                        <input type="number" name="product_stock" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" value="0" min="0" required>
                                    </div>
                                </div>
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-medium">
                                    <i class="fas fa-save mr-2"></i> Add to Inventory
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Inventory Actions -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="bg-blue-600 text-white p-4">
                            <h3 class="font-bold flex items-center">
                                <i class="fas fa-cogs mr-2"></i> Quick Actions
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-3">
                                <button onclick="openStockUpdateModal()" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-lg font-medium flex items-center justify-center">
                                    <i class="fas fa-edit mr-2"></i> Update Stock Levels
                                </button>
                                <button onclick="openPriceUpdateModal()" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2 rounded-lg font-medium flex items-center justify-center">
                                    <i class="fas fa-tag mr-2"></i> Update Prices
                                </button>
                                <button onclick="exportInventory()" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg font-medium flex items-center justify-center">
                                    <i class="fas fa-file-export mr-2"></i> Export Inventory
                                </button>
                                <button onclick="viewLowStock()" class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded-lg font-medium flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle mr-2"></i> View Low Stock
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Rest of your sections remain the same -->
            <!-- Purchase Orders Section -->
            <section id="purchase-orders" class="mb-12">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Purchase Orders</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Confirmed Quotes -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="bg-green-600 text-white p-4">
                            <h3 class="font-bold flex items-center">
                                <i class="fas fa-check-circle mr-2"></i> Confirmed Quotes
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-4">
                                <?php if (!empty($pendingQuotes)): ?>
                                    <?php foreach ($pendingQuotes as $quote): ?>
                                        <div class="border rounded-lg p-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="font-bold"><?php echo htmlspecialchars($quote['company_name']); ?></p>
                                                    <p class="text-sm text-gray-500">Quote #QT-<?php echo $quote['quote_id']; ?> | <?php echo $quote['quantity']; ?> items</p>
                                                </div>
                                                <span class="status-badge bg-green-100 text-green-800">Confirmed</span>
                                            </div>
                                            <div class="mt-3 flex justify-between items-center">
                                                <p class="text-gray-700">Total: <span class="font-bold">$<?php echo number_format($quote['offered_price'] * $quote['quantity'], 2); ?></span></p>
                                                <p class="text-sm text-gray-500">Due: Oct 28, 2025</p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4 text-gray-500">
                                        <i class="fas fa-file-invoice text-3xl mb-2"></i>
                                        <p>No confirmed quotes</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button class="w-full mt-4 text-green-600 hover:text-green-800 font-medium flex items-center justify-center">
                                View All <i class="fas fa-chevron-right ml-2"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Pending Quotes -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="bg-yellow-600 text-white p-4">
                            <h3 class="font-bold flex items-center">
                                <i class="fas fa-clock mr-2"></i> Pending Quotes
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-4">
                                <?php if (!empty($pendingQuotes)): ?>
                                    <?php foreach ($pendingQuotes as $quote): ?>
                                        <div class="border rounded-lg p-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="font-bold"><?php echo htmlspecialchars($quote['company_name']); ?></p>
                                                    <p class="text-sm text-gray-500">Quote #QT-<?php echo $quote['quote_id']; ?> | <?php echo $quote['quantity']; ?> items</p>
                                                </div>
                                                <span class="status-badge bg-yellow-100 text-yellow-800">Pending</span>
                                            </div>
                                            <div class="mt-3 flex justify-between items-center">
                                                <p class="text-gray-700">Total: <span class="font-bold">$<?php echo number_format($quote['offered_price'] * $quote['quantity'], 2); ?></span></p>
                                                <div class="flex space-x-2">
                                                    <button class="text-xs bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded">Accept</button>
                                                    <button class="text-xs bg-primary hover:bg-primary-600 text-white px-3 py-1 rounded">Reject</button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4 text-gray-500">
                                        <i class="fas fa-file-invoice text-3xl mb-2"></i>
                                        <p>No pending quotes</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button class="w-full mt-4 text-yellow-600 hover:text-yellow-800 font-medium flex items-center justify-center">
                                View All <i class="fas fa-chevron-right ml-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
            
        <!-- Orders in Production Section -->
            <section id="production" class="mb-12">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Orders in Production</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- In Production -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="bg-purple-600 text-white p-4">
                            <h3 class="font-bold flex items-center">
                                <i class="fas fa-cogs mr-2"></i> In Production
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-4">
                                <div class="border rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-bold">Order #ORD-7845</p>
                                            <p class="text-sm text-gray-500">Corporate Branding Co.</p>
                                        </div>
                                        <span class="status-badge bg-purple-100 text-purple-800">Production</span>
                                    </div>
                                    <div class="mt-3">
                                        <p class="text-gray-700">500 Custom T-Shirts</p>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                                            <div class="bg-purple-600 h-2.5 rounded-full" style="width: 65%"></div>
                                        </div>
                                        <p class="text-right text-xs text-gray-500 mt-1">65% complete</p>
                                    </div>
                                    <div class="mt-3 flex justify-between items-center">
                                        <p class="text-sm text-gray-500">Started: Oct 12, 2025</p>
                                        <p class="text-sm font-medium">Est. completion: Oct 25, 2025</p>
                                    </div>
                                </div>
                            </div>
                            <button class="w-full mt-4 text-purple-600 hover:text-purple-800 font-medium flex items-center justify-center">
                                View All <i class="fas fa-chevron-right ml-2"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Pending Orders -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="bg-primary text-white p-4">
                            <h3 class="font-bold flex items-center">
                                <i class="fas fa-clipboard-list mr-2"></i> Pending Orders
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-4">
                                <div class="border rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-bold">Order #ORD-7923</p>
                                            <p class="text-sm text-gray-500">Retail Chain Partners</p>
                                        </div>
                                        <span class="status-badge bg-red-100 text-primary">Pending</span>
                                    </div>
                                    <div class="mt-3">
                                        <p class="text-gray-700">750 Branded Pens & Notepads</p>
                                    </div>
                                    <div class="mt-3 flex justify-between items-center">
                                        <p class="text-sm text-gray-500">Received: Oct 14, 2025</p>
                                        <button class="text-xs bg-primary hover:bg-primary-600 text-white px-3 py-1 rounded">Start Production</button>
                                    </div>
                                </div>
                            </div>
                            <button class="w-full mt-4 text-primary hover:text-primary-700 font-medium flex items-center justify-center">
                                View All <i class="fas fa-chevron-right ml-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Client Management Section -->
            <section id="clients" class="mb-12">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Client Management</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Connected Distributors -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="bg-indigo-600 text-white p-4">
                            <h3 class="font-bold flex items-center">
                                <i class="fas fa-handshake mr-2"></i> Connected Distributors
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-4">
                                <div class="flex items-center border rounded-lg p-4">
                                    <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center mr-4">
                                        <i class="fas fa-building text-indigo-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-bold">Global Distributors Inc.</p>
                                        <p class="text-sm text-gray-500">Since Jan 2024 | 24 orders</p>
                                    </div>
                                    <span class="status-badge bg-green-100 text-green-800">Active</span>
                                </div>
                            </div>
                            <button class="w-full mt-4 text-indigo-600 hover:text-indigo-800 font-medium flex items-center justify-center">
                                View All <i class="fas fa-chevron-right ml-2"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Client Requests -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="bg-teal-600 text-white p-4">
                            <h3 class="font-bold flex items-center">
                                <i class="fas fa-user-plus mr-2"></i> Client Requests
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-4">
                                <div class="border rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-bold">City Retail Group</p>
                                            <p class="text-sm text-gray-500">Requested: Oct 14, 2025</p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button class="text-xs bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded">Accept</button>
                                            <button class="text-xs bg-primary hover:bg-primary-600 text-white px-3 py-1 rounded">Reject</button>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-700">"Interested in your premium apparel line for our retail stores."</p>
                                    </div>
                                </div>
                            </div>
                            <button class="w-full mt-4 text-teal-600 hover:text-teal-800 font-medium flex items-center justify-center">
                                View All <i class="fas fa-chevron-right ml-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Payment & Billing Section -->
            <section id="payments" class="mb-12">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Payment & Billing</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Transaction Log -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="bg-green-600 text-white p-4">
                            <h3 class="font-bold flex items-center">
                                <i class="fas fa-receipt mr-2"></i> Transaction Log
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center border-b pb-3">
                                    <div>
                                        <p class="font-medium">Payment Received</p>
                                        <p class="text-sm text-gray-500">Order #ORD-7821 • Oct 15, 2025</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-green-600">$1,245.50</p>
                                        <span class="status-badge bg-green-100 text-green-800">Completed</span>
                                    </div>
                                </div>
                            </div>
                            <button class="w-full mt-4 text-green-600 hover:text-green-800 font-medium flex items-center justify-center">
                                View All <i class="fas fa-chevron-right ml-2"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Due Payments -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="bg-red-600 text-white p-4">
                            <h3 class="font-bold flex items-center">
                                <i class="fas fa-clock mr-2"></i> Due Payments
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center border-b pb-3">
                                    <div>
                                        <p class="font-medium">Global Distributors Inc.</p>
                                        <p class="text-sm text-gray-500">Order #ORD-7845 • Due Oct 28, 2025</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-red-600">$1,850.00</p>
                                        <span class="status-badge bg-red-100 text-red-800">Overdue</span>
                                    </div>
                                </div>
                            </div>
                            <button class="w-full mt-4 text-red-600 hover:text-red-800 font-medium flex items-center justify-center">
                                View All <i class="fas fa-chevron-right ml-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
      
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle functionality
            const toggleSidebar = document.getElementById('toggleSidebar');
            const sidebar = document.querySelector('.sidebar');
            
            if (toggleSidebar && sidebar) {
                toggleSidebar.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                });
            }

            // Smooth scrolling for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });

        // Inventory Management Functions
        function openAddProductModal() {
            document.querySelector('input[name="product_name"]').focus();
        }

        function openStockUpdateModal() {
            alert('Stock update modal would open here. This would allow you to update quantities for existing products.');
        }

        function openPriceUpdateModal() {
            alert('Price update modal would open here. This would allow you to update prices for existing products.');
        }

        function exportInventory() {
            alert('Inventory export started! This would generate a CSV/Excel file of your current inventory.');
        }

        function viewLowStock() {
            alert('Showing low stock items. This would filter and display products with stock below minimum levels.');
        }

        // Auto-hide success message after 5 seconds
        <?php if ($inventory_message && $inventory_message_type === 'success'): ?>
            setTimeout(() => {
                const messageEl = document.querySelector('.bg-green-100');
                if (messageEl) {
                    messageEl.style.display = 'none';
                }
            }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>