<?php
session_start();
require_once 'dbconfig.php'; // Add this line

// Redirect if already logged in
if (isset($_SESSION["user_id"]) && isset($_SESSION["role"])) {
    if ($_SESSION["role"] == 'Supplier') {
        header("location: supplier[1].php");
    } elseif ($_SESSION["role"] == 'Distributor') {
        header("location: distributor_dashboard.php");
    } else {
        header("location: supplier[1].php");
    }
    exit;
}

$email = "";
$login_error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $login_error = "Both email and password are required.";
    } else {
        try {
            $conn = getDBConnection(); // Use the function from dbconfig.php

            // Join with companies table to get the company_role
            $stmt = $conn->prepare("
                SELECT u.user_id, u.company_id, u.full_name, u.password_hash, c.company_role 
                FROM users u
                JOIN companies c ON u.company_id = c.company_id
                WHERE u.email = ?
            ");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($user_id, $company_id, $full_name, $hashed_password, $company_role);
                
                if ($stmt->fetch()) {
                    // Verify password and redirect based on role
                    if (password_verify($password, $hashed_password)) {
                        session_regenerate_id();
                        
                        // Save all session data
                        $_SESSION["user_id"] = $user_id;
                        $_SESSION["company_id"] = $company_id;
                        $_SESSION["full_name"] = $full_name;
                        $_SESSION["email"] = $email;
                        $_SESSION["role"] = $company_role;

                        // Redirect logic
                        if ($company_role == 'Supplier') {
                            header("location: supplier[1].php"); 
                        } elseif ($company_role == 'Distributor') {
                            header("location: distributor_dashboard.php");
                        } elseif ($company_role == 'Shipper') {
                            header("location: shipper_dashboard.php");
                        } else {
                            header("location: supplier[1].php");
                        }
                        exit;
                    } else {
                        $login_error = "Invalid email or password provided.";
                    }
                }
            } else {
                $login_error = "No account found with that email address.";
            }
            $stmt->close();
        } catch (Exception $e) {
            $login_error = "Database error. Please try again.";
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign In - Trimart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#EF4444', // red-500
                            '600': '#DC2626', // red-600
                            '700': '#B91C1C'  // red-700
                        },
                    },
                    fontFamily: { 'poppins': ['Poppins', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        body { background-color: #f3f4f6; } /* A light gray background */
        .form-container {
            max-width: 450px;
            width: 100%;
        }
    </style>
</head>
<body class="font-poppins flex items-center justify-center min-h-screen p-4">

    <div class="form-container bg-white p-8 md:p-10 rounded-2xl shadow-xl">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-primary-700">Trimart</h1>
            <h2 class="text-2xl font-semibold text-gray-800 mt-4">Welcome Back!</h2>
            <p class="text-gray-500 mt-1">Sign in to your account to continue.</p>
        </div>

        <?php if (!empty($login_error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                <p><?php echo $login_error; ?></p>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="mb-5">
                <label class="block mb-2 font-semibold text-gray-700" for="email">
                    <i class="fas fa-envelope text-primary mr-2"></i>Email Address
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input type="email" id="email" name="email" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>">
                </div>
            </div>

            <div class="mb-5">
                <label class="block mb-2 font-semibold text-gray-700" for="password">
                    <i class="fas fa-lock text-primary mr-2"></i>Password
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-key text-gray-400"></i>
                    </div>
                    <input type="password" id="password" name="password" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter your password">
                </div>
            </div>

            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center text-gray-600">
                    <input type="checkbox" name="remember" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                    <span class="ml-2">Remember me</span>
                </label>
                <a href="forgot.php" class="text-sm font-semibold text-primary hover:text-primary-700">Forgot password? <i class="fas fa-arrow-right ml-1"></i></a>
            </div>

            <div>
                <button type="submit" class="w-full bg-primary hover:bg-primary-600 text-white px-8 py-3 rounded-lg font-semibold text-lg shadow-lg transition duration-300 transform hover:-translate-y-1">
                    <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                </button>
            </div>

            <div class="mt-8 text-center">
                <p class="text-gray-600">
                    Don't have an account? 
                    <a href="sup1.php" class="text-primary font-semibold hover:text-primary-700 transition duration-200 ml-1">
                        Sign Up
                    </a>
                </p>
            </div>
        </form>
    </div>

</body>
</html>
