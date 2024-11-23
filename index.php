<?php
session_start(); // Start the session to manage user sessions

$error_message = ""; // Variable to store error messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'inventory_system');

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and bind
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // User exists, now verify the password
        $stmt->bind_result($stored_password);
        $stmt->fetch();

        // Compare stored password (plain-text) with the entered password
        if ($password === $stored_password) {
            // Password is correct, set session variable and redirect to inventory
            $_SESSION['email'] = $email; // Store user email in session
            $_SESSION['logged_in'] = true;
            header('Location: inventory.php'); // Redirect to inventory
            exit();
        } else {
            $error_message = "The credentials you entered are incorrect. Please check your email and password and try again.";
        }
    } else {
        $error_message = "No account is associated with this email address. Please check and try again.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Custom Styles for Right-Side Pop-up Alert */
        .alert-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            width: 300px;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .alert-popup.show {
            display: block;
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Add logo image here -->
        <img src="logo.jpg" alt="Inventory System Logo" style="width: 100px; margin-bottom: 10px;">
        <h1>INVENTORY SYSTEM</h1>
        <div class="login-box">
            <h2>Login</h2>

            <!-- Display error message as a pop-up alert -->
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-popup show" role="alert">
                    <strong>Oops!</strong> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="index.php">
                <label>Email:</label>
                <input type="email" name="email" required><br>
                <label>Password:</label>
                <input type="password" name="password" required><br>
                <button type="submit">Log In</button>
            </form>
            <p><a href="forgot_password.php">Forgot your password?</a></p>
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add a delay before showing the alert and set it to disappear after 3 seconds
        window.addEventListener('DOMContentLoaded', function() {
            <?php if ($error_message): ?>
                // Show the alert after the page loads
                setTimeout(function() {
                    const alert = document.querySelector('.alert-popup');
                    alert.classList.add('show');

                    // After 3 seconds, remove the alert from the screen
                    setTimeout(function() {
                        alert.classList.remove('show');
                    }, 3000); // Hide after 3 seconds
                }, 100);  // Delay to allow page to load
            <?php endif; ?>
        });
    </script>
</body>
</html>
