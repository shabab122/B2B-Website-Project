<?php
$email = "";
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars(trim($_POST['email']));

    if (empty($email)) {
        $error = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email format.";
    } else {
        // --- THIS IS WHERE YOU WOULD ADD YOUR EMAIL SENDING LOGIC ---
        // For now, we will just display a success message.
        // In a real application, you would:
        // 1. Check if this email exists in your 'users' database table.
        // 2. Generate a unique, secure, and expiring token.
        // 3. Store the token and its expiry date in the database against the user's record.
        // 4. Use an email library (like PHPMailer) to send a password reset link to the user's email.
        //    The link would look something like: https://yourwebsite.com/reset-password.php?token=...
        
        $message = "If an account with that email exists, a password reset link has been sent to it.";
        $email = ""; // Clear the input field after submission
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forgot Password - Trimart</title>
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
        body { background-color: #f3f4f6; }
        .form-container { max-width: 450px; width: 100%; }
    </style>
</head>
<body class="font-poppins flex items-center justify-center min-h-screen p-4">

    <div class="form-container bg-white p-8 md:p-10 rounded-2xl shadow-xl">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-primary-700">Trimart</h1>
            <h2 class="text-2xl font-semibold text-gray-800 mt-4">Reset Your Password</h2>
            <p class="text-gray-500 mt-1">Enter your email and we'll send you instructions.</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
                <p><?php echo $message; ?></p>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="mb-6">
                <label class="block mb-2 font-semibold text-gray-700" for="email">
                    <i class="fas fa-envelope text-primary mr-2"></i>Email Address
                </label>
                <input type="email" id="email" name="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter your registered email" value="<?php echo htmlspecialchars($email); ?>">
            </div>

            <div>
                <button type="submit" class="w-full bg-primary hover:bg-primary-600 text-white px-8 py-3 rounded-lg font-semibold text-lg shadow-lg transition duration-300">
                    <i class="fas fa-paper-plane mr-2"></i> Send Reset Link
                </button>
            </div>
        </form>

        <div class="mt-8 text-center">
            <p class="text-gray-600">
                Remember your password? 
                <a href="sin1.php" class="text-primary font-semibold hover:text-primary-700 transition duration-200 ml-1">
                    Back to Sign In
                </a>
            </p>
        </div>
    </div>

</body>
</html>
