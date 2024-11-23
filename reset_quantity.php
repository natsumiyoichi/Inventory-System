<?php
// Set the timezone to your desired timezone (e.g., 'Asia/Manila', 'America/New_York', etc.)
date_default_timezone_set('Asia/Manila');  // Set the timezone to Manila (adjust if needed)

// Check if 'reportType' and 'productId' are passed in the POST request
if (isset($_POST['reportType']) && isset($_POST['productId'])) {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db = 'inventory_system';

    // Create connection to the database
    $conn = new mysqli($host, $user, $pass, $db);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get the report type and product ID from the POST request
    $reportType = $_POST['reportType'];
    $productId = $_POST['productId'];
    $currentDate = date('Y-m-d H:i:s'); // Get the current date and time in the specified timezone

    // Perform the update operation based on the report type
    if ($reportType == 'bad_merchandise') {
        // Update quantity to 0 and reset the report date for the specific bad merchandise product
        $sql = "UPDATE bad_merchandise SET quantity = 0, report_date = ? WHERE id = ?";
        $stmt = $conn->prepare($sql); // Prepare the query
        $stmt->bind_param("si", $currentDate, $productId); // Bind the current date and product ID to the query
        $stmt->execute(); // Execute the query
        $stmt->close(); // Close the statement
        
        // Provide feedback to the user
        echo "<script>alert('Quantity for selected bad merchandise has been reset to 0 and report date updated.'); window.location.href='report.php';</script>";
    } elseif ($reportType == 'defective_merchandise') {
        // Update quantity to 0 and reset the report date for the specific defective merchandise product
        $sql = "UPDATE defective_merchandise SET quantity = 0, report_date = ? WHERE id = ?";
        $stmt = $conn->prepare($sql); // Prepare the query
        $stmt->bind_param("si", $currentDate, $productId); // Bind the current date and product ID to the query
        $stmt->execute(); // Execute the query
        $stmt->close(); // Close the statement
        
        // Provide feedback to the user
        echo "<script>alert('Quantity for selected defective merchandise has been reset to 0.'); window.location.href='report.php';</script>";
    } else {
        // Handle invalid requests
        echo "<script>alert('Invalid request.'); window.location.href='report.php';</script>";
    }

    // Close the connection
    $conn->close();
} else {
    // Handle missing 'reportType' or 'productId'
    echo "<script>alert('Invalid request.'); window.location.href='report.php';</script>";
}
?>
