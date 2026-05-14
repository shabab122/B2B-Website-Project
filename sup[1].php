<?php
require_once 'dbconfig.php'; // Add this line

// Initialize variables
$company_name = $company_role = $first_name = $last_name = $email = $phone = $tin = "";
$business_license_number = $trade_license_number = $warehouse_location = $company_website = "";
$user_title = "";

$errors = [];
$success_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    function sanitize_input($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    // --- GATHER AND SANITIZE FORM DATA ---
    $company_name = sanitize_input($_POST['company_name']);
    $company_role = sanitize_input($_POST['company_role']);
    $tin = sanitize_input($_POST['tin']);
    $business_license_number = sanitize_input($_POST['business_license_number']);
    $trade_license_number = sanitize_input($_POST['trade_license_number']);
    $warehouse_location = sanitize_input($_POST['warehouse_location']);
    $company_website = sanitize_input($_POST['company_website']);
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $full_name = $first_name . ' ' . $last_name;
    $user_title = sanitize_input($_POST['user_title']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // --- SERVER-SIDE VALIDATION ---
    if (empty($company_name)) { $errors[] = "Company Name is required."; }
    if (empty($company_role)) { $errors[] = "Company Role is required."; }
    if (empty($tin)) { $errors[] = "TIN is required."; }
    if (empty($business_license_number)) { $errors[] = "Business License Number is required."; }
    if (empty($trade_license_number)) { $errors[] = "Trade License Number is required."; }
    if (empty($first_name)) { $errors[] = "First Name is required."; }
    if (empty($last_name)) { $errors[] = "Last Name is required."; }
    if (empty($user_title)) { $errors[] = "Your Title is required."; }
    if (empty($email)) { $errors[] = "Email Address is required."; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Invalid email format."; }
    if (empty($phone)) { $errors[] = "Phone Number is required."; }
    if (empty($password)) { $errors[] = "Password is required."; }
    if ($password !== $confirm_password) { $errors[] = "Passwords do not match."; }

    // --- DATABASE INTERACTION ---
    if (empty($errors)) {
        try {
            $conn = getDBConnection(); // Use the function from dbconfig.php

            $stmt_check = $conn->prepare("SELECT company_id FROM companies WHERE tin = ? OR business_license_number = ? OR trade_license_number = ? OR company_email = ?");
            $stmt_check->bind_param("ssss", $tin, $business_license_number, $trade_license_number, $email);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $errors[] = "A company with the provided TIN, License Number, or Email already exists.";
            }
            $stmt_check->close();

            $stmt_check_user = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt_check_user->bind_param("s", $email);
            $stmt_check_user->execute();
            if ($stmt_check_user->get_result()->num_rows > 0) {
                $errors[] = "A user with this email address already exists.";
            }
            $stmt_check_user->close();

            if (empty($errors)) {
                $conn->begin_transaction();
                try {
                    $stmt_company = $conn->prepare("INSERT INTO companies (company_name, company_role, tin, business_license_number, trade_license_number, warehouse_location, company_email, company_website) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt_company->bind_param("ssssssss", $company_name, $company_role, $tin, $business_license_number, $trade_license_number, $warehouse_location, $email, $company_website);
                    $stmt_company->execute();

                    $new_company_id = $conn->insert_id;
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $is_primary_contact = true;

                    $stmt_user = $conn->prepare("INSERT INTO users (company_id, full_name, title, email, phone, password_hash, is_primary_contact) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt_user->bind_param("isssssi", $new_company_id, $full_name, $user_title, $email, $phone, $password_hash, $is_primary_contact);
                    $stmt_user->execute();

                    $conn->commit();
                    $success_message = "Registration successful! Your company and user account have been created.";
                    
                    // Clear form fields
                    $_POST = [];
                    $company_name = $company_role = $first_name = $last_name = $email = $phone = $tin = "";
                    $business_license_number = $trade_license_number = $warehouse_location = $company_website = "";
                    $user_title = "";

                } catch (mysqli_sql_exception $exception) {
                    $conn->rollback();
                    $errors[] = "Error during registration. Please try again.";
                    error_log("Registration error: " . $exception->getMessage());
                } finally {
                    if (isset($stmt_company)) $stmt_company->close();
                    if (isset($stmt_user)) $stmt_user->close();
                }
            }
        } catch (Exception $e) {
            $errors[] = "Database connection failed. Please try again.";
            error_log("Database connection error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign Up - Trimart</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#EF4444',
          },
          fontFamily: { 'poppins': ['Poppins', 'sans-serif'] }
        }
      }
    }
  </script>
  
  <style>
    .form-card { backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.85); }
    .gradient-bg { background: white; }
    .floating-shape { position: absolute; border-radius: 50%; background: rgba(255, 0, 0, 0.1); }
    .intro-section { background: linear-gradient(135deg, #EF4444 0%, #B91C1C 100%); }
  </style>
</head>
<body class="font-poppins gradient-bg min-h-screen flex flex-col items-center justify-start p-4 relative">
  
  <div class="floating-shape w-72 h-72 -top-20 -left-20 z-0"></div>
  <div class="floating-shape w-96 h-96 -bottom-32 -right-32 z-0"></div>
  <div class="floating-shape w-64 h-64 top-1/2 right-1/4 z-0"></div>
  
  <div class="intro-section w-full max-w-4xl rounded-t-2xl text-white p-8 z-10 mt-10">
    <div class="flex flex-col md:flex-row items-center justify-between">
      <div class="md:w-2/3 mb-6 md:mb-0">
        <h1 class="text-3xl md:text-4xl font-bold mb-4">Create Your Account</h1>
        <p class="text-xl md:text-2xl mb-2">Join Trimart as a business partner</p>
        <p class="text-red-100">Register today to access exclusive business opportunities and grow with us</p>
      </div>
      <div class="md:w-1/3 flex justify-center">
        <div class="bg-white/20 rounded-full p-4"><i class="fas fa-handshake text-5xl text-white"></i></div>
      </div>
    </div>
  </div>
  
  <div class="form-card rounded-b-2xl shadow-2xl w-full max-w-4xl overflow-auto z-10 mb-10">
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="w-full p-8">
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Oops!</strong> <span class="block sm:inline">Please fix the errors:</span>
                <ul class="list-disc ml-5 mt-2">
                    <?php foreach ($errors as $error): ?><li><?php echo $error; ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Success!</strong> <span class="block sm:inline"><?php echo $success_message; ?></span>
            </div>
        <?php endif; ?>

        <div class="mb-6"><h2 class="text-2xl font-bold text-red-600">Business & User Information</h2></div>
        
        <p class="text-sm text-gray-800 mb-6">Fields marked with <span class="text-red-500 font-bold">*</span> are mandatory.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            <!-- All form fields go here... -->
            <!-- Company Name -->
            <div>
              <label class="block mb-2 font-semibold text-gray-800" for="company_name"><span class="text-red-500 mr-1">*</span> Company Name</label>
              <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-building text-red-400"></i></div><input type="text" id="company_name" name="company_name" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Your Company Name" value="<?php echo htmlspecialchars($company_name); ?>"></div>
            </div>
            <!-- Company Role -->
            <div>
                <label class="block mb-2 font-semibold text-gray-800" for="company_role"><span class="text-red-500 mr-1">*</span> Company Role</label>
                <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-user-tag text-red-400"></i></div>
                    <select id="company_role" name="company_role" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 appearance-none">
                        <option value="" disabled <?php if(empty($company_role)) echo 'selected'; ?>>Select a role...</option>
                        <option value="Distributor" <?php if($company_role == 'Distributor') echo 'selected'; ?>>Distributor</option>
                        <option value="Supplier" <?php if($company_role == 'Supplier') echo 'selected'; ?>>Supplier</option>
                        <option value="Shipper" <?php if($company_role == 'Shipper') echo 'selected'; ?>>Shipper</option>
                    </select>
                </div>
            </div>
            <!-- First Name -->
            <div>
                <label class="block mb-2 font-semibold text-gray-800" for="first_name"><span class="text-red-500 mr-1">*</span> First Name</label>
                <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-user text-red-400"></i></div><input type="text" id="first_name" name="first_name" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Your First Name" value="<?php echo htmlspecialchars($first_name); ?>"></div>
            </div>
            <!-- Last Name -->
            <div>
                <label class="block mb-2 font-semibold text-gray-800" for="last_name"><span class="text-red-500 mr-1">*</span> Last Name</label>
                <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-user text-red-400"></i></div><input type="text" id="last_name" name="last_name" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Your Last Name" value="<?php echo htmlspecialchars($last_name); ?>"></div>
            </div>
            <!-- Email -->
            <div>
                <label class="block mb-2 font-semibold text-gray-800" for="email"><span class="text-red-500 mr-1">*</span> Your Email (for Login)</label>
                <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-envelope text-red-400"></i></div><input type="email" id="email" name="email" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="you@company.com" value="<?php echo htmlspecialchars($email); ?>"></div>
            </div>
            <!-- Phone -->
            <div>
              <label class="block mb-2 font-semibold text-gray-800" for="phone"><span class="text-red-500 mr-1">*</span> Phone Number</label>
              <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-phone text-red-400"></i></div><input type="tel" id="phone" name="phone" required placeholder="01xxxxxxxxx" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" value="<?php echo htmlspecialchars($phone); ?>"></div>
            </div>
            <!-- Password -->
            <div>
              <label class="block mb-2 font-semibold text-gray-800" for="password"><span class="text-red-500 mr-1">*</span> Password</label>
              <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-lock text-red-400"></i></div><input type="password" id="password" name="password" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Create a strong password"></div>
            </div>
            <!-- Confirm Password -->
            <div>
              <label class="block mb-2 font-semibold text-gray-800" for="confirm_password"><span class="text-red-500 mr-1">*</span> Confirm Password</label>
              <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-lock text-red-400"></i></div><input type="password" id="confirm_password" name="confirm_password" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Re-enter your password"></div>
            </div>
             <!-- Your Title -->
            <div>
                <label class="block mb-2 font-semibold text-gray-800" for="user_title"><span class="text-red-500 mr-1">*</span> Your Title in Company</label>
                <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-briefcase text-red-400"></i></div>
                    <select id="user_title" name="user_title" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 appearance-none">
                        <option value="" disabled <?php if(empty($user_title)) echo 'selected'; ?>>Select your title...</option>
                        <option value="Owner" <?php if($user_title == 'Owner') echo 'selected'; ?>>Owner</option>
                        <option value="Employee" <?php if($user_title == 'Employee') echo 'selected'; ?>>Employee</option>
                    </select>
                </div>
            </div>
             <!-- TIN -->
            <div>
                <label class="block mb-2 font-semibold text-gray-800" for="tin"><span class="text-red-500 mr-1">*</span> TIN (Tax ID)</label>
                <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-file-invoice text-red-400"></i></div><input type="text" id="tin" name="tin" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Enter TIN" value="<?php echo htmlspecialchars($tin); ?>"></div>
            </div>
            <!-- Business License -->
            <div>
                <label class="block mb-2 font-semibold text-gray-800" for="business_license_number"><span class="text-red-500 mr-1">*</span> Business License Number</label>
                <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-id-card text-red-400"></i></div><input type="text" id="business_license_number" name="business_license_number" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Enter business license" value="<?php echo htmlspecialchars($business_license_number); ?>"></div>
            </div>
            <!-- Trade License -->
            <div>
                <label class="block mb-2 font-semibold text-gray-800" for="trade_license_number"><span class="text-red-500 mr-1">*</span> Trade License Number</label>
                <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-file-contract text-red-400"></i></div><input type="text" id="trade_license_number" name="trade_license_number" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Enter trade license" value="<?php echo htmlspecialchars($trade_license_number); ?>"></div>
            </div>
             <!-- Warehouse -->
            <div class="md:col-span-2">
                <label class="block mb-2 font-semibold text-gray-800" for="warehouse_location">Warehouse Location</label>
                <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-warehouse text-red-400"></i></div><input type="text" id="warehouse_location" name="warehouse_location" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Enter warehouse location" value="<?php echo htmlspecialchars($warehouse_location); ?>"></div>
            </div>
             <!-- Website -->
             <div class="md:col-span-2">
              <label class="block mb-2 font-semibold text-gray-800" for="company_website">Company Website</label>
              <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-globe text-red-400"></i></div><input type="url" id="company_website" name="company_website" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="https://example.com" value="<?php echo htmlspecialchars($company_website); ?>"></div>
            </div>
        </div>

        <div class="text-center mt-8">
          <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg font-semibold text-lg shadow-lg transition duration-300 transform hover:-translate-y-1">
            <i class="fas fa-user-plus mr-2"></i> Create Account
          </button>
        </div>

        <div class="mt-8 text-center">
          <p class="text-gray-700">
            Already have an account? 
            <a href="sin[1].php" class="text-red-600 font-semibold hover:text-red-700 transition duration-200 ml-1">
              Sign in here <i class="fas fa-arrow-right ml-1"></i>
            </a>
          </p>
        </div>
    </form>
  </div>
</body>
</html>
