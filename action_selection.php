<?php
date_default_timezone_set('Asia/Manila'); 
// Ensure an Item_id is passed in the URL
if (!isset($_GET['Item_id'])) {
    die("Item ID is required.");
}

$Item_id = htmlspecialchars($_GET['Item_id']);
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get current date and time for the report_date field
$current_date = date('Y-m-d H:i:s');

// Fetch product name from the current_stock table
$sql_get_product_name = "SELECT product_name FROM current_stock WHERE Item_id = ?";
$stmt_get_product_name = $conn->prepare($sql_get_product_name);
$stmt_get_product_name->bind_param("i", $Item_id);
$stmt_get_product_name->execute();
$stmt_get_product_name->bind_result($product_name);
$stmt_get_product_name->fetch();
$stmt_get_product_name->close();

// Process the action and update the database
$actionCompleted = false;
$showWarning = false;

if ($action) {
    // SQL to update quantity based on action
    switch ($action) {
        case 'add':
            // Add stock to the current_stock table and update report_date
            $sql_add = "UPDATE current_stock SET quantity = quantity + 1, report_date = ? WHERE Item_id = ?";
            $stmt_add = $conn->prepare($sql_add);
            $stmt_add->bind_param("si", $current_date, $Item_id);
            $stmt_add->execute();
            $stmt_add->close();

            $actionCompleted = true;
            break;

        case 'deduct':
            // Deduct stock from the current_stock table and update report_date
            $sql_deduct = "UPDATE current_stock SET quantity = quantity - 1, report_date = ? WHERE Item_id = ?";
            $stmt_deduct = $conn->prepare($sql_deduct);
            $stmt_deduct->bind_param("si", $current_date, $Item_id);
            $stmt_deduct->execute();
            $stmt_deduct->close();

            $actionCompleted = true;
            break;

        case 'bad':
            // Check if record exists in the bad_merchandise table
            $sql_check_bad = "SELECT quantity FROM bad_merchandise WHERE Item_id = ?";
            $stmt_check_bad = $conn->prepare($sql_check_bad);
            $stmt_check_bad->bind_param("i", $Item_id);
            $stmt_check_bad->execute();
            $stmt_check_bad->store_result();

            if ($stmt_check_bad->num_rows > 0) {
                // Record exists, update the quantity and report_date
                $sql_bad_update = "UPDATE bad_merchandise SET quantity = quantity + 1, report_date = ? WHERE Item_id = ?";
                $stmt_bad_update = $conn->prepare($sql_bad_update);
                $stmt_bad_update->bind_param("si", $current_date, $Item_id);
                $stmt_bad_update->execute();
                $stmt_bad_update->close();
            } else {
                // Record doesn't exist, insert a new one with product_name
                $sql_bad_insert = "INSERT INTO bad_merchandise (Item_id, product_name, quantity, report_date) VALUES (?, ?, 1, ?)";
                $stmt_bad_insert = $conn->prepare($sql_bad_insert);
                $stmt_bad_insert->bind_param("iss", $Item_id, $product_name, $current_date);
                $stmt_bad_insert->execute();
                $stmt_bad_insert->close();
            }

            $stmt_check_bad->close();
            $actionCompleted = true;
            break;

        case 'defective':
            // Check if record exists in the defective_merchandise table
            $sql_check_defective = "SELECT quantity FROM defective_merchandise WHERE Item_id = ?";
            $stmt_check_defective = $conn->prepare($sql_check_defective);
            $stmt_check_defective->bind_param("i", $Item_id);
            $stmt_check_defective->execute();
            $stmt_check_defective->store_result();

            if ($stmt_check_defective->num_rows > 0) {
                // Record exists, update the quantity and report_date
                $sql_defective_update = "UPDATE defective_merchandise SET quantity = quantity + 1, report_date = ? WHERE Item_id = ?";
                $stmt_defective_update = $conn->prepare($sql_defective_update);
                $stmt_defective_update->bind_param("si", $current_date, $Item_id);
                $stmt_defective_update->execute();
                $stmt_defective_update->close();
            } else {
                // Record doesn't exist, insert a new one with product_name
                $sql_defective_insert = "INSERT INTO defective_merchandise (Item_id, product_name, quantity, report_date) VALUES (?, ?, 1, ?)";
                $stmt_defective_insert = $conn->prepare($sql_defective_insert);
                $stmt_defective_insert->bind_param("iss", $Item_id, $product_name, $current_date);
                $stmt_defective_insert->execute();
                $stmt_defective_insert->close();
            }

            $stmt_check_defective->close();
            $actionCompleted = true;
            break;

        default:
            $actionCompleted = false;
            break;
    }
}

// Fetch the updated quantity of the product from the current_stock table after the action
$sql_get_quantity = "SELECT quantity FROM current_stock WHERE Item_id = ?";
$stmt_get_quantity = $conn->prepare($sql_get_quantity);
$stmt_get_quantity->bind_param("i", $Item_id);
$stmt_get_quantity->execute();
$stmt_get_quantity->bind_result($quantity);
$stmt_get_quantity->fetch();
$stmt_get_quantity->close();

if ($quantity < 10) {
    $showWarning = true;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Action Selection</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
</head>
<body class="flex" style="background: #f5f5dc;">
    <!-- Sidebar -->
    <div class="bg-[#a67c52] w-1/4 h-screen p-6 flex flex-col items-center">
        <button class="bg-transparent mb-4">
            <img alt="Coffee Geney logo" src="logo.jpg" width="100" height="100" class="rounded-full"/>
        </button>
        <h1 class="text-white text-2xl font-bold mb-8">Coffee Geney</h1>
        <nav class="text-white flex flex-col items-center">
            <ul>
                <li class="mb-4 flex items-center">
                    <i class="fas fa-box mr-2"></i>
                    <a class="text-lg" href="inventory.php">Inventory</a>
                </li>
                <li class="mb-4 flex items-center">
                    <i class="fas fa-file-alt mr-2"></i>
                    <a class="text-lg" href="report.php">Report</a>
                </li>
                <li class="mb-4 flex items-center">
                    <i class="fas fa-user mr-2"></i>
                    <a class="text-lg" href="account.php">Account</a>
                </li>
                <li class="mb-4 flex items-center">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    <a class="text-lg text-red-500" href="logout.php">Logout</a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800">Action Section</h1>
        </div>

        <?php if ($actionCompleted): ?>
            <div class="bg-white shadow-md rounded-lg p-6 w-full md:w-96 mx-auto text-center">
                <p class="mb-6 text-lg">Action <strong><?php echo ucfirst($action); ?></strong> was successful for Item ID: <strong><?php echo $Item_id; ?></strong></p>
                <a href="inventory.php" class="block bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition duration-300">Return to Inventory</a>
            </div>
        <?php else: ?>
            <div class="bg-white shadow-md rounded-lg p-6 w-full md:w-96 mx-auto text-center">
                <p class="mb-6 text-lg">Please select an action for Item ID: <strong><?php echo $Item_id; ?></strong></p>
                <div class="space-y-4">
                    <a href="action_selection.php?Item_id=<?php echo $Item_id; ?>&action=add" 
                        class="block bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600 transition duration-300">Add Stock</a>
                    <a href="action_selection.php?Item_id=<?php echo $Item_id; ?>&action=deduct" 
                        class="block bg-yellow-500 text-white py-2 px-4 rounded hover:bg-yellow-600 transition duration-300">Deduct Stock</a>
                    <a href="action_selection.php?Item_id=<?php echo $Item_id; ?>&action=bad" 
                        class="block bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600 transition duration-300">Mark as Bad</a>
                    <a href="action_selection.php?Item_id=<?php echo $Item_id; ?>&action=defective" 
                        class="block bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition duration-300">Mark as Defective</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- JavaScript alert logic -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Only show the warning if it's triggered by add or deduct action
            <?php if ($showWarning): ?>
                var alertBox = document.createElement('div');
                alertBox.textContent = 'Warning: Stock is below 10!';
                alertBox.style.position = 'fixed';
                alertBox.style.top = '10px';
                alertBox.style.right = '10px';
                alertBox.style.backgroundColor = '#ffcc00';
                alertBox.style.color = '#000';
                alertBox.style.padding = '10px';
                alertBox.style.borderRadius = '5px';
                alertBox.style.zIndex = '1000';
                
                document.body.appendChild(alertBox);

                setTimeout(function() {
                    alertBox.remove();
                }, 3000);
            <?php endif; ?>
        });
    </script>
</body>
</html>
