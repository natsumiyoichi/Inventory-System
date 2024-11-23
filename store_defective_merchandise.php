<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "inventory_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Default quantity to add or deduct if none is passed
$defaultQuantity = 1;

if (isset($_GET['Item_id']) && isset($_GET['action'])) {
    $Item_id = $_GET['Item_id'];
    $action = $_GET['action'];

    // Validate the action parameter
    $valid_actions = ['add', 'deduct', 'bad', 'defective'];
    if (!in_array($action, $valid_actions)) {
        echo "Invalid action.";
        exit();
    }

    // Check if the product exists in the database
    $stmt = $conn->prepare("SELECT product_name FROM current_stock WHERE Item_id = ?");
    $stmt->bind_param("i", $Item_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($product_name);
        $stmt->fetch();

        // Check if the product exists in the defective_merchandise table
        $stmt2 = $conn->prepare("SELECT quantity FROM defective_merchandise WHERE Item_id = ?");
        $stmt2->bind_param("i", $Item_id);
        $stmt2->execute();
        $stmt2->store_result();

        if ($stmt2->num_rows > 0) {
            // If the item exists, update the quantity based on action
            $stmt2->bind_result($currentQuantity);
            $stmt2->fetch();

            if ($action === 'add') {
                $newQuantity = $currentQuantity + $defaultQuantity;
            } elseif ($action === 'deduct') {
                $newQuantity = max(0, $currentQuantity - $defaultQuantity); // Prevent negative quantity
            } elseif ($action === 'bad' || $action === 'defective') {
                $newQuantity = $currentQuantity + $defaultQuantity; // Similar handling for bad and defective
            }

            // Update defective merchandise table
            $updateStmt = $conn->prepare("UPDATE defective_merchandise SET quantity = ?, report_date = NOW() WHERE Item_id = ?");
            $updateStmt->bind_param("ii", $newQuantity, $Item_id);
            if ($updateStmt->execute()) {
                echo "Defective Merchandise Quantity has been updated for Item ID: $Item_id";
            } else {
                echo "Error updating defective merchandise quantity.";
            }
            $updateStmt->close();

        } else {
            // If the item does not exist, insert into the defective_merchandise table
            $insertStmt = $conn->prepare("INSERT INTO defective_merchandise (Item_id, product_name, quantity, report_date) SELECT Item_id, product_name, ?, NOW() FROM current_stock WHERE Item_id = ?");
            $insertStmt->bind_param("ii", $defaultQuantity, $Item_id);
            if ($insertStmt->execute()) {
                echo "Defective merchandise added successfully.";
            } else {
                echo "Error adding defective merchandise.";
            }
            $insertStmt->close();
        }

        $stmt2->close();
    } else {
        echo "Item not found in current stock.";
    }

    $stmt->close();

} else {
    echo "Invalid request. Please provide Item_id and action.";
}

$conn->close();
?>
