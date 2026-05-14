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

if ($_SESSION['role'] !== 'Shipper') {
    session_unset();
    session_destroy();
    header("Location: sin[1].php?error=access_denied");
    exit();
}

// --- 3. INCLUDE DATABASE CONNECTION ---
require_once 'dbconfig.php';

// Get user data from session
$shipper_name = htmlspecialchars($_SESSION['full_name']);
$shipper_email = htmlspecialchars($_SESSION['email']);
$company_id = $_SESSION['company_id'];

// --- 4. HANDLE ORDER ACTIONS ---
$action_message = "";
$action_message_type = "";

// Handle Start Delivery
if (isset($_GET['start_delivery']) && isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    
    try {
        $conn = getDBConnection();
        
        // Update delivery tracking status
        $stmt = $conn->prepare("UPDATE delivery_tracking SET status = 'InTransit' WHERE order_id = ? AND shipper_company_id = ?");
        $stmt->bind_param("ii", $order_id, $company_id);
        $stmt->execute();
        
        // Update order status
        $stmt_order = $conn->prepare("UPDATE orders SET order_status = 'Shipped' WHERE order_id = ?");
        $stmt_order->bind_param("i", $order_id);
        $stmt_order->execute();
        
        if ($stmt->affected_rows > 0) {
            $action_message = "Delivery started for Order #$order_id!";
            $action_message_type = "success";
        }
        
    } catch (Exception $e) {
        $action_message = "Error starting delivery: " . $e->getMessage();
        $action_message_type = "error";
    }
}

// Handle Mark as Delivered
if (isset($_GET['mark_delivered']) && isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    
    try {
        $conn = getDBConnection();
        
        // Update delivery tracking status
        $stmt = $conn->prepare("UPDATE delivery_tracking SET status = 'Delivered' WHERE order_id = ? AND shipper_company_id = ?");
        $stmt->bind_param("ii", $order_id, $company_id);
        $stmt->execute();
        
        // Update order status
        $stmt_order = $conn->prepare("UPDATE orders SET order_status = 'Delivered' WHERE order_id = ?");
        $stmt_order->bind_param("i", $order_id);
        $stmt_order->execute();
        
        if ($stmt->affected_rows > 0) {
            $action_message = "Order #$order_id marked as delivered!";
            $action_message_type = "success";
        }
        
    } catch (Exception $e) {
        $action_message = "Error updating delivery: " . $e->getMessage();
        $action_message_type = "error";
    }
}

// Handle Assign Order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_order'])) {
    $order_id = intval($_POST['order_id']);
    $tracking_number = trim($_POST['tracking_number']);
    
    try {
        $conn = getDBConnection();
        
        // Check if order exists and get supplier info
        $stmt_check = $conn->prepare("
            SELECT o.order_id, s.company_name as supplier_name, d.company_name as distributor_name
            FROM orders o
            JOIN companies s ON o.supplier_company_id = s.company_id
            JOIN companies d ON o.distributor_company_id = d.company_id
            WHERE o.order_id = ?
        ");
        $stmt_check->bind_param("i", $order_id);
        $stmt_check->execute();
        $order_exists = $stmt_check->get_result()->fetch_assoc();
        $stmt_check->close();
        
        if ($order_exists) {
            // Assign order to shipper
            $stmt_assign = $conn->prepare("
                INSERT INTO delivery_tracking (order_id, shipper_company_id, tracking_number, status) 
                VALUES (?, ?, ?, 'Assigned')
            ");
            $stmt_assign->bind_param("iis", $order_id, $company_id, $tracking_number);
            $stmt_assign->execute();
            
            // Update order status
            $stmt_update = $conn->prepare("UPDATE orders SET shipper_company_id = ?, order_status = 'Shipped' WHERE order_id = ?");
            $stmt_update->bind_param("ii", $company_id, $order_id);
            $stmt_update->execute();
            
            $action_message = "Order #$order_id assigned successfully!";
            $action_message_type = "success";
        } else {
            $action_message = "Order not found!";
            $action_message_type = "error";
        }
        
    } catch (Exception $e) {
        $action_message = "Error assigning order: " . $e->getMessage();
        $action_message_type = "error";
    }
}

// Handle Update Status
if (isset($_GET['update_status']) && isset($_GET['order_id']) && isset($_GET['status'])) {
    $order_id = intval($_GET['order_id']);
    $status = $_GET['status'];
    
    try {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("UPDATE delivery_tracking SET status = ? WHERE order_id = ? AND shipper_company_id = ?");
        $stmt->bind_param("sii", $status, $order_id, $company_id);
        $stmt->execute();
        
        // Also update order status
        $order_status = ($status == 'Delivered') ? 'Delivered' : 'Shipped';
        $stmt_order = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
        $stmt_order->bind_param("si", $order_status, $order_id);
        $stmt_order->execute();
        
        if ($stmt->affected_rows > 0) {
            $action_message = "Order status updated successfully!";
            $action_message_type = "success";
        }
        
    } catch (Exception $e) {
        $action_message = "Error updating status: " . $e->getMessage();
        $action_message_type = "error";
    }
}

// --- 5. FETCH REAL DATA FROM DATABASE ---
try {
    $conn = getDBConnection();
    
    // Fetch assigned orders count
    $stmt_assigned = $conn->prepare("SELECT COUNT(*) FROM delivery_tracking WHERE shipper_company_id = ? AND status = 'Assigned'");
    $stmt_assigned->bind_param("i", $company_id);
    $stmt_assigned->execute();
    $assignedOrders = $stmt_assigned->get_result()->fetch_row()[0];
    $stmt_assigned->close();
    
    // Fetch in transit count
    $stmt_transit = $conn->prepare("SELECT COUNT(*) FROM delivery_tracking WHERE shipper_company_id = ? AND status = 'InTransit'");
    $stmt_transit->bind_param("i", $company_id);
    $stmt_transit->execute();
    $inTransitOrders = $stmt_transit->get_result()->fetch_row()[0];
    $stmt_transit->close();
    
    // Fetch delivered count
    $stmt_delivered = $conn->prepare("SELECT COUNT(*) FROM delivery_tracking WHERE shipper_company_id = ? AND status = 'Delivered'");
    $stmt_delivered->bind_param("i", $company_id);
    $stmt_delivered->execute();
    $deliveredOrders = $stmt_delivered->get_result()->fetch_row()[0];
    $stmt_delivered->close();
    
    // Fetch returned count
    $stmt_returned = $conn->prepare("SELECT COUNT(*) FROM delivery_tracking WHERE shipper_company_id = ? AND status = 'Returned'");
    $stmt_returned->bind_param("i", $company_id);
    $stmt_returned->execute();
    $returnedOrders = $stmt_returned->get_result()->fetch_row()[0];
    $stmt_returned->close();
    
    // Fetch assigned orders with details
    $stmt_orders = $conn->prepare("
        SELECT dt.tracking_id, dt.order_id, dt.tracking_number, dt.status,
               o.total_amount, c.company_name as distributor_name,
               s.company_name as supplier_name, u.full_name as receiver_name
        FROM delivery_tracking dt
        JOIN orders o ON dt.order_id = o.order_id
        JOIN companies c ON o.distributor_company_id = c.company_id
        JOIN companies s ON o.supplier_company_id = s.company_id
        LEFT JOIN users u ON o.distributor_company_id = u.company_id AND u.is_primary_contact = 1
        WHERE dt.shipper_company_id = ? AND dt.status = 'Assigned'
        ORDER BY dt.tracking_id DESC
    ");
    $stmt_orders->bind_param("i", $company_id);
    $stmt_orders->execute();
    $assignedOrdersData = $stmt_orders->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_orders->close();
    
    // Fetch recent orders (all statuses)
    $stmt_recent = $conn->prepare("
        SELECT dt.tracking_id, dt.order_id, dt.tracking_number, dt.status, dt.last_updated,
               o.total_amount, c.company_name as distributor_name,
               s.company_name as supplier_name, u.full_name as receiver_name
        FROM delivery_tracking dt
        JOIN orders o ON dt.order_id = o.order_id
        JOIN companies c ON o.distributor_company_id = c.company_id
        JOIN companies s ON o.supplier_company_id = s.company_id
        LEFT JOIN users u ON o.distributor_company_id = u.company_id AND u.is_primary_contact = 1
        WHERE dt.shipper_company_id = ?
        ORDER BY dt.last_updated DESC
        LIMIT 10
    ");
    $stmt_recent->bind_param("i", $company_id);
    $stmt_recent->execute();
    $recentOrders = $stmt_recent->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_recent->close();
    
    // Fetch orders available for assignment (orders without shipper)
    $stmt_available = $conn->prepare("
        SELECT o.order_id, o.total_amount, c.company_name as distributor_name,
               s.company_name as supplier_name, u.full_name as receiver_name
        FROM orders o
        JOIN companies c ON o.distributor_company_id = c.company_id
        JOIN companies s ON o.supplier_company_id = s.company_id
        LEFT JOIN users u ON o.distributor_company_id = u.company_id AND u.is_primary_contact = 1
        WHERE o.shipper_company_id IS NULL AND o.order_status = 'Confirmed'
        ORDER BY o.order_id DESC
        LIMIT 10
    ");
    $stmt_available->execute();
    $availableOrders = $stmt_available->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_available->close();
    
    // Fetch payment data
    $stmt_payments = $conn->prepare("
        SELECT t.transaction_id, t.amount, t.status, t.transaction_date,
               o.order_id, c.company_name
        FROM transactions t
        JOIN orders o ON t.order_id = o.order_id
        JOIN companies c ON o.distributor_company_id = c.company_id
        WHERE o.shipper_company_id = ?
        ORDER BY t.transaction_date DESC
        LIMIT 10
    ");
    $stmt_payments->bind_param("i", $company_id);
    $stmt_payments->execute();
    $paymentsData = $stmt_payments->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_payments->close();
    
} catch (Exception $e) {
    error_log("Shipper dashboard error: " . $e->getMessage());
    $assignedOrders = 0;
    $inTransitOrders = 0;
    $deliveredOrders = 0;
    $returnedOrders = 0;
    $assignedOrdersData = [];
    $recentOrders = [];
    $availableOrders = [];
    $paymentsData = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trimart Shipper Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    :root {
      --primary: #EF4444;
      --primary-600: #DC2626;
      --primary-700: #B91C1C;
    }
    
    .sidebar-link { 
      transition: all 0.3s; 
      border-left: 4px solid transparent; 
    }
    .sidebar-link:hover { 
      background: linear-gradient(90deg, #fef2f2, #fee2e2); 
      border-left-color: var(--primary); 
      color: var(--primary-700); 
    }
    .sidebar-link.active { 
      background: linear-gradient(90deg, #fef2f2 0%, #fee2e2 100%); 
      border-left: 4px solid var(--primary) !important; 
      color: var(--primary-700) !important; 
      font-weight: 600; 
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2); 
    }
    .order-card { 
      transition: all 0.4s ease; 
      border-left: 4px solid transparent; 
    }
    .order-card:hover { 
      transform: translateY(-5px); 
      border-left-color: var(--primary); 
      box-shadow: 0 12px 20px rgba(239, 68, 68, 0.1), 0 4px 6px rgba(139,92,246,0.1); 
    }
    .tracking-card:hover { 
      transform: translateY(-3px); 
      box-shadow: 0 6px 12px rgba(239, 68, 68, 0.08); 
    }
    .stat-card { 
      transition: all 0.3s ease; 
    }
    .stat-card:hover { 
      transform: translateY(-5px); 
      box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
    }
    
    /* CSS-only Tab System */
    .tab-content {
      display: none;
    }
    
    #dashboard-tab:checked ~ .main-content #dashboard-content,
    #assigned-tab:checked ~ .main-content #assigned-content,
    #recent-tab:checked ~ .main-content #recent-content,
    #transit-tab:checked ~ .main-content #transit-content,
    #delivered-tab:checked ~ .main-content #delivered-content,
    #returned-tab:checked ~ .main-content #returned-content,
    #agents-tab:checked ~ .main-content #agents-content,
    #payments-tab:checked ~ .main-content #payments-content,
    #profile-tab:checked ~ .main-content #profile-content {
      display: block;
    }
    
    #dashboard-tab:checked ~ .sidebar #dashboard-link,
    #assigned-tab:checked ~ .sidebar #assigned-link,
    #recent-tab:checked ~ .sidebar #recent-link,
    #transit-tab:checked ~ .sidebar #transit-link,
    #delivered-tab:checked ~ .sidebar #delivered-link,
    #returned-tab:checked ~ .sidebar #returned-link,
    #agents-tab:checked ~ .sidebar #agents-link,
    #payments-tab:checked ~ .sidebar #payments-link,
    #profile-tab:checked ~ .sidebar #profile-link {
      background: linear-gradient(90deg, #fef2f2 0%, #fee2e2 100%); 
      border-left: 4px solid var(--primary) !important; 
      color: var(--primary-700) !important; 
      font-weight: 600; 
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2); 
    }
    
    /* Status badges */
    .status-badge {
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 500;
    }
    .status-assigned { background-color: #e0e7ff; color: #4f46e5; }
    .status-transit { background-color: #fef3c7; color: #d97706; }
    .status-delivered { background-color: #d1fae5; color: #065f46; }
    .status-returned { background-color: #fee2e2; color: #991b1b; }
    
    /* Priority badges */
    .priority-high { background-color: #fee2e2; color: #dc2626; }
    .priority-medium { background-color: #fef3c7; color: #d97706; }
    .priority-low { background-color: #d1fae5; color: #065f46; }
    
    /* New theme colors */
    .bg-primary { background-color: var(--primary); }
    .bg-primary-600 { background-color: var(--primary-600); }
    .bg-primary-700 { background-color: var(--primary-700); }
    .text-primary { color: var(--primary); }
    .text-primary-600 { color: var(--primary-600); }
    .text-primary-700 { color: var(--primary-700); }
    .border-primary { border-color: var(--primary); }
    .border-primary-600 { border-color: var(--primary-600); }
    .border-primary-700 { border-color: var(--primary-700); }
  </style>
</head>
<body class="bg-gradient-to-r from-gray-50 to-gray-100 font-sans">

  <!-- Hidden radio buttons to control tabs -->
  <input type="radio" name="tab" id="dashboard-tab" class="hidden" checked>
  <input type="radio" name="tab" id="assigned-tab" class="hidden">
  <input type="radio" name="tab" id="recent-tab" class="hidden">
  <input type="radio" name="tab" id="transit-tab" class="hidden">
  <input type="radio" name="tab" id="delivered-tab" class="hidden">
  <input type="radio" name="tab" id="returned-tab" class="hidden">
  <input type="radio" name="tab" id="agents-tab" class="hidden">
  <input type="radio" name="tab" id="payments-tab" class="hidden">
  <input type="radio" name="tab" id="profile-tab" class="hidden">

  <!-- Navigation Bar -->
  <nav class="navbar bg-white shadow-sm py-3 px-6">
    <div class="container-fluid flex justify-between items-center">
      <a class="navbar-brand flex items-center" href="#">
        <i class="fas fa-shipping-fast text-primary text-2xl me-2"></i>
        <span class="font-bold text-gray-800 text-xl">Trimart Shipper</span>
      </a>
      <div class="flex items-center">
        <div class="relative mr-4">
          <input type="text" class="border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Search orders...">
          <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
        <div class="dropdown relative">
          <button class="dropdown-toggle flex items-center text-gray-700 hover:text-primary">
            <i class="fas fa-user-circle text-xl me-1"></i>
            <span class="font-medium"><?php echo $shipper_name; ?></span>
            <i class="fas fa-chevron-down ml-1 text-sm"></i>
          </button>
          <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden">
            <a href="#" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              <i class="fas fa-user me-2"></i>Profile
            </a>
            <a href="#" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              <i class="fas fa-cog me-2"></i>Settings
            </a>
            <div class="border-t my-1"></div>
            <a href="?action=logout" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <!-- Action Messages -->
  <?php if ($action_message): ?>
    <div class="ml-64 mt-2 p-4">
      <div class="<?php echo $action_message_type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?> px-4 py-3 rounded relative">
        <?php echo $action_message; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Sidebar -->
  <div class="sidebar fixed top-0 left-0 w-64 h-full bg-white border-r shadow-lg flex flex-col z-10 mt-16">
    <nav class="flex-1 overflow-y-auto py-4">
      <ul class="space-y-1">
        <li>
          <label for="dashboard-tab" class="sidebar-link flex items-center py-3 px-4 cursor-pointer active" id="dashboard-link">
            <div class="w-6 text-center"><i class="fas fa-tachometer-alt text-primary"></i></div>
            <span class="ml-3">Dashboard</span>
          </label>
        </li>
        <li>
          <label for="assigned-tab" class="sidebar-link flex items-center py-3 px-4 cursor-pointer" id="assigned-link">
            <div class="w-6 text-center"><i class="fas fa-clipboard-list text-primary"></i></div>
            <span class="ml-3">Assigned Orders</span>
          </label>
        </li>
        <li>
          <label for="recent-tab" class="sidebar-link flex items-center py-3 px-4 cursor-pointer" id="recent-link">
            <div class="w-6 text-center"><i class="fas fa-clock text-primary"></i></div>
            <span class="ml-3">Recent Orders</span>
          </label>
        </li>
        <li>
          <label for="transit-tab" class="sidebar-link flex items-center py-3 px-4 cursor-pointer" id="transit-link">
            <div class="w-6 text-center"><i class="fas fa-truck-moving text-primary"></i></div>
            <span class="ml-3">In Transit</span>
          </label>
        </li>
        <li>
          <label for="delivered-tab" class="sidebar-link flex items-center py-3 px-4 cursor-pointer" id="delivered-link">
            <div class="w-6 text-center"><i class="fas fa-check-circle text-primary"></i></div>
            <span class="ml-3">Delivered</span>
          </label>
        </li>
        <li>
          <label for="returned-tab" class="sidebar-link flex items-center py-3 px-4 cursor-pointer" id="returned-link">
            <div class="w-6 text-center"><i class="fas fa-undo text-primary"></i></div>
            <span class="ml-3">Returned Orders</span>
          </label>
        </li>
        <li>
          <label for="payments-tab" class="sidebar-link flex items-center py-3 px-4 cursor-pointer" id="payments-link">
            <div class="w-6 text-center"><i class="fas fa-credit-card text-primary"></i></div>
            <span class="ml-3">Payments & Billing</span>
          </label>
        </li>
        <li>
          <label for="profile-tab" class="sidebar-link flex items-center py-3 px-4 cursor-pointer" id="profile-link">
            <div class="w-6 text-center"><i class="fas fa-user-circle text-primary"></i></div>
            <span class="ml-3">My Profile</span>
          </label>
        </li>
      </ul>
    </nav>
  </div>

  <!-- Main Content -->
  <div class="main-content ml-64 mt-16 p-6">

    <!-- Dashboard Content -->
    <div class="tab-content" id="dashboard-content">
      <h2 class="text-3xl font-bold mb-4 text-primary-700">Dashboard Overview</h2>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">
        <!-- Assigned Orders -->
        <div class="stat-card bg-white rounded-xl shadow-md p-5 flex flex-col justify-between border-l-4 border-primary">
          <p class="text-gray-500">Assigned Orders</p>
          <p class="text-2xl font-bold text-primary"><?php echo $assignedOrders; ?></p>
        </div>
        <!-- In Transit -->
        <div class="stat-card bg-white rounded-xl shadow-md p-5 flex flex-col justify-between border-l-4 border-blue-500">
          <p class="text-gray-500">In Transit</p>
          <p class="text-2xl font-bold text-blue-600"><?php echo $inTransitOrders; ?></p>
        </div>
        <!-- Delivered -->
        <div class="stat-card bg-white rounded-xl shadow-md p-5 flex flex-col justify-between border-l-4 border-green-500">
          <p class="text-gray-500">Delivered</p>
          <p class="text-2xl font-bold text-green-600"><?php echo $deliveredOrders; ?></p>
        </div>
        <!-- Returned Orders -->
        <div class="stat-card bg-white rounded-xl shadow-md p-5 flex flex-col justify-between border-l-4 border-red-500">
          <p class="text-gray-500">Returned Orders</p>
          <p class="text-2xl font-bold text-red-600"><?php echo $returnedOrders; ?></p>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-md p-6">
          <h3 class="text-xl font-bold text-gray-800 mb-4">Recent Activity</h3>
          <div class="space-y-4">
            <?php if (!empty($recentOrders)): ?>
              <?php foreach (array_slice($recentOrders, 0, 3) as $order): ?>
                <div class="flex items-center space-x-3">
                  <div class="w-3 h-3 rounded-full 
                    <?php echo $order['status'] == 'Delivered' ? 'bg-green-500' : 
                           ($order['status'] == 'InTransit' ? 'bg-blue-500' : 
                           ($order['status'] == 'Assigned' ? 'bg-yellow-500' : 'bg-red-500')); ?>">
                  </div>
                  <p class="text-gray-600">
                    Order #<?php echo $order['order_id']; ?> 
                    <?php echo strtolower($order['status']); ?> 
                    to <?php echo $order['receiver_name'] ?: $order['distributor_name']; ?>
                  </p>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="text-gray-500">No recent activity</p>
            <?php endif; ?>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6">
          <h3 class="text-xl font-bold text-gray-800 mb-4">Performance Metrics</h3>
          <div class="space-y-3">
            <div class="flex justify-between">
              <p class="text-gray-600">On-time Delivery Rate</p>
              <p class="font-bold text-green-600">94%</p>
            </div>
            <div class="flex justify-between">
              <p class="text-gray-600">Customer Satisfaction</p>
              <p class="font-bold text-blue-600">4.7/5</p>
            </div>
            <div class="flex justify-between">
              <p class="text-gray-600">Return Rate</p>
              <p class="font-bold text-red-600">2.3%</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Assigned Orders Content -->
    <div class="tab-content" id="assigned-content">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-primary-700">Assigned Orders</h2>
        <button onclick="openAssignModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-600 transition flex items-center">
          <i class="fas fa-plus mr-2"></i> Assign New Order
        </button>
      </div>

      <!-- Assign Order Modal -->
      <div id="assignModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
          <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Assign New Order</h3>
            <form method="POST" action="">
              <input type="hidden" name="assign_order" value="1">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Order ID</label>
                <select name="order_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary" required>
                  <option value="">Select Order</option>
                  <?php foreach ($availableOrders as $order): ?>
                    <option value="<?php echo $order['order_id']; ?>">
                      Order #<?php echo $order['order_id']; ?> - <?php echo $order['distributor_name']; ?> - $<?php echo number_format($order['total_amount'], 2); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tracking Number</label>
                <input type="text" name="tracking_number" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary" required>
              </div>
              <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeAssignModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-600">Assign</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Orders Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (!empty($assignedOrdersData)): ?>
          <?php foreach ($assignedOrdersData as $order): ?>
            <div class="order-card bg-white p-5 rounded-xl shadow-md border-l-4 border-primary">
              <p class="font-semibold text-lg">Order #<?php echo $order['order_id']; ?></p>
              <p class="text-gray-500">Receiver: <?php echo $order['receiver_name'] ?: $order['distributor_name']; ?></p>
              <p class="text-gray-500">From: <?php echo $order['supplier_name']; ?></p>
              <p class="text-gray-500">Amount: $<?php echo number_format($order['total_amount'], 2); ?></p>
              <p class="text-gray-500 mt-2">Tracking: <?php echo $order['tracking_number']; ?></p>
              <div class="flex justify-between items-center mt-3">
                <span class="status-badge status-assigned"><?php echo $order['status']; ?></span>
                <span class="status-badge priority-high">High</span>
              </div>
              <div class="mt-3 flex space-x-2">
                <button class="px-3 py-1 bg-blue-500 text-white rounded-lg text-sm">View Details</button>
                <a href="?start_delivery=1&order_id=<?php echo $order['order_id']; ?>" 
                   class="px-3 py-1 bg-green-500 text-white rounded-lg text-sm hover:bg-green-600 transition"
                   onclick="return confirm('Start delivery for Order #<?php echo $order['order_id']; ?>?')">
                  Start Delivery
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-span-3 text-center py-8">
            <i class="fas fa-clipboard-list text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500 text-lg">No assigned orders</p>
            <p class="text-gray-400">Orders assigned to you will appear here</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Recent Orders Content -->
    <div class="tab-content" id="recent-content">
      <h2 class="text-3xl font-bold mb-4 text-blue-700">Recent Orders</h2>
      <p class="text-gray-600 mb-6">All recent orders with detailed information</p>

      <!-- Orders Table -->
      <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Number</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tracking</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (!empty($recentOrders)): ?>
              <?php foreach ($recentOrders as $order): ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $order['order_id']; ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $order['distributor_name']; ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $order['supplier_name']; ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$<?php echo number_format($order['total_amount'], 2); ?></td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="status-badge 
                      <?php echo $order['status'] == 'Assigned' ? 'status-assigned' : 
                             ($order['status'] == 'InTransit' ? 'status-transit' : 
                             ($order['status'] == 'Delivered' ? 'status-delivered' : 'status-returned')); ?>">
                      <?php echo $order['status']; ?>
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $order['tracking_number']; ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                    <?php if ($order['status'] == 'Assigned'): ?>
                      <a href="?start_delivery=1&order_id=<?php echo $order['order_id']; ?>" class="text-green-600 hover:text-green-900">Start Delivery</a>
                    <?php elseif ($order['status'] == 'InTransit'): ?>
                      <a href="?mark_delivered=1&order_id=<?php echo $order['order_id']; ?>" class="text-green-600 hover:text-green-900">Mark Delivered</a>
                    <?php else: ?>
                      <span class="text-gray-400">Completed</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                  No recent orders found
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Payments & Billing Content -->
    <div class="tab-content" id="payments-content">
      <h2 class="text-3xl font-bold mb-4 text-primary-700">Payments & Billing</h2>
      <p class="text-gray-600 mb-6">Manage payments and invoices</p>
      
      <!-- Payment Overview -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">
        <?php
        $pending_amount = 0;
        $overdue_amount = 0;
        $paid_amount = 0;
        $total_amount = 0;
        
        foreach ($paymentsData as $payment) {
          $total_amount += $payment['amount'];
          if ($payment['status'] == 'Pending') $pending_amount += $payment['amount'];
          if ($payment['status'] == 'Failed') $overdue_amount += $payment['amount'];
          if ($payment['status'] == 'Completed') $paid_amount += $payment['amount'];
        }
        ?>
        <div class="stat-card bg-white rounded-xl shadow-md p-5 flex flex-col justify-between border-l-4 border-yellow-500">
          <p class="text-gray-500">Pending Payments</p>
          <p class="text-2xl font-bold text-yellow-600">$<?php echo number_format($pending_amount, 2); ?></p>
          <p class="text-sm text-gray-500">Pending transactions</p>
        </div>
        <div class="stat-card bg-white rounded-xl shadow-md p-5 flex flex-col justify-between border-l-4 border-red-500">
          <p class="text-gray-500">Failed Payments</p>
          <p class="text-2xl font-bold text-red-600">$<?php echo number_format($overdue_amount, 2); ?></p>
          <p class="text-sm text-gray-500">Failed transactions</p>
        </div>
        <div class="stat-card bg-white rounded-xl shadow-md p-5 flex flex-col justify-between border-l-4 border-green-500">
          <p class="text-gray-500">Paid This Month</p>
          <p class="text-2xl font-bold text-green-600">$<?php echo number_format($paid_amount, 2); ?></p>
          <p class="text-sm text-gray-500">Completed transactions</p>
        </div>
        <div class="stat-card bg-white rounded-xl shadow-md p-5 flex flex-col justify-between border-l-4 border-blue-500">
          <p class="text-gray-500">Total Balance</p>
          <p class="text-2xl font-bold text-blue-600">$<?php echo number_format($total_amount, 2); ?></p>
          <p class="text-sm text-gray-500">All transactions</p>
        </div>
      </div>

      <!-- Payment History -->
      <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold text-gray-800">Payment History</h3>
          <div class="flex space-x-2">
            <button class="px-3 py-1 bg-primary text-white rounded-lg text-sm active">All</button>
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Pending</button>
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Completed</button>
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Failed</button>
          </div>
        </div>
        
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Number</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (!empty($paymentsData)): ?>
              <?php foreach ($paymentsData as $payment): ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary-600"><?php echo $payment['transaction_id']; ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $payment['order_id']; ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $payment['company_name']; ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$<?php echo number_format($payment['amount'], 2); ?></td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                      <?php echo $payment['status'] == 'Completed' ? 'bg-green-100 text-green-800' : 
                             ($payment['status'] == 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                      <?php echo $payment['status']; ?>
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M j, Y', strtotime($payment['transaction_date'])); ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                    <?php if ($payment['status'] == 'Pending'): ?>
                      <button class="text-green-600 hover:text-green-900">Process</button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                  No payment history found
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Profile Content -->
    <div class="tab-content" id="profile-content">
      <h2 class="text-3xl font-bold mb-6 text-primary-700">My Profile</h2>

      <div class="bg-white rounded-xl shadow-lg p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Personal Info -->
        <div>
          <div class="flex items-center space-x-4 mb-6">
            <div class="w-16 h-16 rounded-full bg-primary flex items-center justify-center text-white text-xl font-bold">
              <?php echo strtoupper(substr($shipper_name, 0, 2)); ?>
            </div>
            <div>
              <h3 class="text-xl font-bold"><?php echo $shipper_name; ?></h3>
              <p class="text-gray-500">Shipper Partner</p>
            </div>
          </div>

          <div class="space-y-3">
            <p><span class="font-semibold">Name:</span> <?php echo $shipper_name; ?></p>
            <p><span class="font-semibold">Email:</span> <?php echo $shipper_email; ?></p>
            <p><span class="font-semibold">Company ID:</span> <?php echo $company_id; ?></p>
          </div>

          <div class="mt-6 space-x-3">
            <button class="px-4 py-2 border border-primary text-primary rounded-lg hover:bg-red-50 transition">
              <i class="fas fa-key mr-2"></i> Change Password
            </button>
            <button class="px-4 py-2 border border-primary text-primary rounded-lg hover:bg-red-50 transition">
              <i class="fas fa-edit mr-2"></i> Edit Profile
            </button>
          </div>
        </div>
        <!-- Delivery Statistics -->
        <div class="bg-gray-50 rounded-xl p-5 flex flex-col justify-center space-y-4">
          <h3 class="font-semibold text-lg mb-2">Delivery Statistics</h3>
          <p><span class="font-semibold">Total Deliveries:</span> <?php echo $deliveredOrders; ?></p>
          <p><span class="font-semibold">Pending Deliveries:</span> <?php echo $assignedOrders + $inTransitOrders; ?></p>
          <p><span class="font-semibold">Success Rate:</span> <?php echo $deliveredOrders > 0 ? round(($deliveredOrders / ($deliveredOrders + $returnedOrders)) * 100, 1) : 0; ?>%</p>
        </div>
      </div>
    </div>

  </div>

  <script>
    // Dropdown functionality
    document.querySelector('.dropdown-toggle').addEventListener('click', function() {
      const dropdownMenu = this.nextElementSibling;
      dropdownMenu.classList.toggle('hidden');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
      if (!event.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
          menu.classList.add('hidden');
        });
      }
    });

    // Assign Order Modal
    function openAssignModal() {
      document.getElementById('assignModal').classList.remove('hidden');
    }

    function closeAssignModal() {
      document.getElementById('assignModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('assignModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeAssignModal();
      }
    });

    // Filter buttons for payments
    document.querySelectorAll('#payments-content .flex.space-x-2 button').forEach(button => {
      button.addEventListener('click', function() {
        // Remove active class from all buttons
        document.querySelectorAll('#payments-content .flex.space-x-2 button').forEach(btn => {
          btn.classList.remove('active', 'bg-primary', 'text-white');
          btn.classList.add('border', 'border-gray-300', 'text-gray-700');
        });
        
        // Add active class to clicked button
        this.classList.add('active', 'bg-primary', 'text-white');
        this.classList.remove('border', 'border-gray-300', 'text-gray-700');
        
        // Filter logic would go here in a real application
        const filter = this.textContent.toLowerCase();
        console.log(`Filtering by: ${filter}`);
      });
    });

    // Auto-hide success messages
    <?php if ($action_message): ?>
      setTimeout(() => {
        const messageEl = document.querySelector('.bg-green-100, .bg-red-100');
        if (messageEl) {
          messageEl.style.display = 'none';
        }
      }, 5000);
    <?php endif; ?>
  </script>
</body>
</html>