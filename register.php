<?php
session_start();
$registration_success = false; // Flag to trigger popup after successful registration
$error_message = ""; // Variable to store error messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $conn = new mysqli('localhost', 'root', '', 'inventory_system');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error_message = "This email is already registered.";
    } else {
        if ($password !== $confirm_password) {
            $error_message = "Passwords do not match!";
        } else {
            // Store the password as plain text (no hashing)
            $stored_password = $password;

            // Insert the new user into the database
            $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $email, $stored_password);

            if ($stmt->execute()) {
                $registration_success = true; // Set success flag
            } else {
                $error_message = "Error: Could not register. Please try again later.";
            }
        }
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
    <title>Inventory System - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Modal styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            width: 300px;
        }

        .modal-content h3 {
            margin: 0 0 20px;
        }

        .modal-content button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .error-message {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>INVENTORY SYSTEM</h1>
        <div class="login-box">
            <h2>Register</h2>
            <form method="post" action="register.php">
                <label>Email:</label>
                <input type="email" name="email" required><br>
                <label>Password:</label>
                <input type="password" name="password" required><br>
                <label>Confirm Password:</label>
                <input type="password" name="confirm_password" required><br>
                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <a href="index.php">Log in here</a></p>
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal popup -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <h3>Registration Successful!</h3>
            <p>Your account has been created.</p>
            <button onclick="redirectToLogin()">Go to Login</button>
        </div>
    </div>

    <script>
        // Check if registration was successful
        <?php if ($registration_success): ?>
            document.getElementById("successModal").style.display = "flex";
        <?php endif; ?>

        // Redirect to login page
        function redirectToLogin() {
            window.location.href = 'index.php';
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
