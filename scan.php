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

// Process POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Decode JSON request body
    $input = json_decode(file_get_contents("php://input"), true);

    if (isset($input['Item_id'], $input['action'])) {
        $Item_id = intval($input['Item_id']);
        $action = $input['action']; // 'add' or 'deduct'

        // Determine quantity adjustment based on action
        $quantityChange = $action === "add" ? 1 : ($action === "deduct" ? -1 : 0);

        if ($quantityChange !== 0) {
            // Update the stock quantity
            $stmt = $conn->prepare("UPDATE current_stock SET quantity = quantity + ? WHERE Item_id = ?");
            $stmt->bind_param("ii", $quantityChange, $Item_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Stock updated successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to update stock. Item_id might not exist."]);
            }
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "message" => "Invalid action."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid input data."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}

$conn->close();
?>
