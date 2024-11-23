<?php
// Include phpqrcode library
include('phpqrcode/qrlib.php');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize messages
$successMessage = '';
$errorMessage = '';
$productExists = false;

// Get form data
$product_name = trim($_POST['product_name']);
$quantity = intval($_POST['quantity']);

// Validate input
if (empty($product_name) || $quantity < 0) {
    $errorMessage = "Invalid input. Please provide a valid product name and quantity.";
} else {
    // Check if the product already exists in the database
    $stmt = $conn->prepare("SELECT Item_id FROM current_stock WHERE product_name = ?");
    $stmt->bind_param("s", $product_name);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($Item_id);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        $errorMessage = "Product '$product_name' already exists in the database.";
        $productExists = true;
        $stmt->close();
    } else {
        $stmt->close();

        // Insert data into database
        $stmt = $conn->prepare("INSERT INTO current_stock (product_name, quantity) VALUES (?, ?)");
        $stmt->bind_param("si", $product_name, $quantity);

        if ($stmt->execute()) {
            $Item_id = $stmt->insert_id;
            $stmt->close();

            // Generate sanitized file name using only product name
            $sanitized_product_name = preg_replace("/[^a-zA-Z0-9_-]/", "_", $product_name);
            $qrFilePath = $sanitized_product_name . ".png";  // Use product name directly

            // Improved URL for the QR code
            $baseURL = "http://localhost/inventory_system/update_quantity.php?Item_id=$Item_id";

            // Generate a single QR code pointing to update_quantity.php
            try {
                QRcode::png($baseURL, $qrFilePath, 'L', 10, 2);
                
                // Update the database with the QR code path
                $stmt = $conn->prepare("UPDATE current_stock SET qr_code_name = ? WHERE Item_id = ?");
                $stmt->bind_param("si", $qrFilePath, $Item_id);
                $stmt->execute();
                $stmt->close();

                $successMessage = "QR Code has been successfully generated for '$product_name'.";
            } catch (Exception $e) {
                $errorMessage = "Error generating QR code: " . $e->getMessage();
            }
        } else {
            $errorMessage = "Error adding product: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!-- HTML code for displaying messages and QR codes -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Generation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
</head>
<body class="flex bg-gray-100">

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
            <h1 class="text-4xl font-bold text-gray-800">QR Code Generation</h1>
        </div>

        <div class="mb-8">
            <p class="text-lg mb-4">The following actions were taken:</p>

            <!-- Display Success/Error Messages -->
            <?php if ($successMessage): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                    <?php echo $successMessage; ?>
                </div>
            <?php elseif ($errorMessage): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Display the generated QR code -->
        <?php if (!$productExists && isset($qrFilePath)): ?>
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800">Product QR Code</h2>
                <div class="flex justify-center mt-4">
                    <div class="text-center">
                        <img src="<?php echo $qrFilePath; ?>" alt="Product Action QR Code" class="border rounded-lg shadow-lg"/>
                        <p class="mt-2">QR Code for <?php echo $product_name; ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Return to Inventory Button -->
        <div class="flex justify-center">
            <a href="inventory.php" class="bg-blue-500 text-white py-2 px-6 rounded-lg hover:bg-blue-600 transition duration-300">Return to Inventory</a>
        </div>
    </div>
</body>
</html>
