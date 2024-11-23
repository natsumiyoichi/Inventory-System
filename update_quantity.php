<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve Item_id and action from the URL query string
$Item_id = $_GET['Item_id'];
$action = $_GET['action'];  // The action could be 'add' or 'deduct'

// Function to check if the stock is low
function checkLowStock($conn, $Item_id) {
    $stmt = $conn->prepare("SELECT quantity FROM current_stock WHERE Item_id = ?");
    $stmt->bind_param("i", $Item_id);
    $stmt->execute();
    $stmt->bind_result($quantity);
    $stmt->fetch();
    $stmt->close();
    
    if ($quantity <= 5) {
        echo "Warning: The stock for Item ID: $Item_id is running low ($quantity remaining). Please restock.";
    }
}

// Check if action contains 'add' or 'deduct'
if (strpos($action, 'add') !== false) {
    // Add quantity logic and update report_date
    $stmt = $conn->prepare("UPDATE current_stock SET quantity = quantity + 1, report_date = NOW() WHERE Item_id = ?");
    $stmt->bind_param("i", $Item_id);
    $stmt->execute();
    $stmt->close();

    // Check if stock is low after adding
    checkLowStock($conn, $Item_id);
    echo "Quantity has been updated for Item ID: $Item_id";
} elseif (strpos($action, 'deduct') !== false) {
    // Deduct quantity logic and update report_date
    $stmt = $conn->prepare("UPDATE current_stock SET quantity = quantity - 1, report_date = NOW() WHERE Item_id = ?");
    $stmt->bind_param("i", $Item_id);
    $stmt->execute();
    $stmt->close();

    // Check if stock is low after deduction
    checkLowStock($conn, $Item_id);
    echo "Current Stock Quantity has been updated for Item ID: $Item_id";
} else {
    echo "Invalid action";
}

$conn->close();
?>
