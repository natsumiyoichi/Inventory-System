<?php
$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'inventory_system');

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Update password
        $stmt->close();
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $new_password, $email);

        if ($stmt->execute()) {
            $success_message = "Password updated successfully. You can now log in with your new password.";
        } else {
            $error_message = "Something went wrong. Please try again.";
        }
    } else {
        $error_message = "No account is associated with this email address.";
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
    <title>Forgot Password</title>
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
            <h2>Forgot Password</h2>

            <!-- Display error or success message as a pop-up alert -->
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-popup show" role="alert">
                    <strong>Oops!</strong> <?php echo $error_message; ?>
                </div>
            <?php elseif ($success_message): ?>
                <div class="alert alert-success alert-popup show" role="alert">
                    <strong>Success!</strong> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="forgot_password.php">
                <label>Email:</label>
                <input type="email" name="email" required><br>
                <label>New Password:</label>
                <input type="password" name="new_password" required><br>
                <button type="submit">Reset Password</button>
            </form>
            <p>Remembered your password? <a href="index.php">Log in here</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add a delay before showing the alert and set it to disappear after 3 seconds
        window.addEventListener('DOMContentLoaded', function() {
            <?php if ($error_message || $success_message): ?>
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
