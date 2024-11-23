<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = '';
$dbname = "inventory_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize messages
$successMessage = '';
$errorMessage = '';

// Check if form was submitted for adding a product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_name'], $_POST['quantity'])) {
    $product_name = $_POST['product_name'];
    $quantity = $_POST['quantity'];

    // Check if the product already exists in the database
    $stmt = $conn->prepare("SELECT Item_id FROM current_stock WHERE product_name = ?");
    $stmt->bind_param("s", $product_name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Product already exists
        $errorMessage = "This product has already been added to the inventory.";
    } else {
        // Insert product data into the database
        $stmt = $conn->prepare("INSERT INTO current_stock (product_name, quantity) VALUES (?, ?)");
        $stmt->bind_param("si", $product_name, $quantity);
        if ($stmt->execute()) {
            $Item_id = $stmt->insert_id;
            $stmt->close();
        }
    }
}
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Inventory System</title>
    <script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Open+Sans:wght@400;700&family=Pacifico&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background: #f5f5dc; /* Lightest shade of brown */
        }
        .sidebar {
            background: linear-gradient(135deg, #6f4e37, #3e2723); /* Coffee gradient effect */
        }
        .sidebar h1 {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            font-family: 'Pacifico', cursive;
        }
        .sidebar a {
            transition: color 0.3s;
        }
        .sidebar a:hover {
            color: #ffeb3b;
        }
        .button {
            transition: transform 0.3s, box-shadow 0.3s;
            background: #8b5e34; /* Brown */
            color: #fff;
        }
        .button:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.2);
            background: #a67c52; /* Light brown */
        }
        .saddle-button {
            background: #8b4513; /* Saddle brown */
            color: #fff;
        }
        .saddle-button:hover {
            transform: translateY(-5px);
            background: #a0522d; /* Slightly lighter saddle brown */
        }
        .modal {
            transition: opacity 0.3s;
        }
        .modal.show {
            opacity: 1;
        }
        .modal.hide {
            opacity: 0;
        }
    </style>
</head>
<body class="flex">
    <!-- Sidebar -->
    <div class="sidebar w-1/4 h-screen p-4 text-white">
        <div class="flex flex-col items-center">
            <img alt="Coffee Geney logo" src="logo.jpg" width="100" height="100" style="border-radius: 50%;"/>
            <h1 class="text-3xl font-bold mb-8">Coffee Geney</h1>
        </div>
        <nav>
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
                    <a class="block text-lg text-red-500" href="logout.php">Logout</a>
                </li>
            </ul>
        </nav>
    </div>

   <!-- Main Content -->
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold mb-8" style="font-family: 'Pacifico', cursive;"><i class="fas fa-box text-4xl"></i> Inventory</h1>
            <div class="relative">
            </div>
        </div>
        <p class="text-lg mb-8">Welcome to Coffee Geney inventory system.</p>
        
        <!-- Notification Section -->
        <div id="notification" class="alert alert-info alert-dismissible fade show" role="alert" style="display:none;">
            <strong>Warning!</strong> <span id="notificationMessage"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <button class="button text-2xl font-bold py-8 rounded-lg" onclick="openQrScanner()"><i class="fas fa-qrcode text-3xl"></i>
                Update Stocks
            </button>
            <button class="saddle-button text-2xl font-bold py-8 rounded-lg" onclick="ProductForm()"><i class="fas fa-plus text-3xl"></i>
                Add Product
            </button>
        </div>

        <!-- Add Product Modal -->
        <div id="addProductModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
            <div class="bg-white p-4 rounded-lg" style="width: 400px;">
                <h2 class="text-2xl font-bold mb-4">Add New Product</h2>
                <form action="generate_qr.php" method="post" id="qrForm">
                    <label for="product_name" class="block mb-2">Product Name:</label>
                    <input type="text" id="product_name" name="product_name" required class="border p-2 w-full mb-4"/>
                    
                    <label for="quantity" class="block mb-2">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" required class="border p-2 w-full mb-4"/>
                    
                    <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded mt-4">Generate QR Code</button>
                </form>
                <button class="bg-blue-500 text-white py-2 px-4 rounded mt-4" onclick="closeAddProductForm()">Close</button>
            </div>
        </div>

        <!-- QR Scanner Modal -->
        <div id="qrScannerModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
            <div class="bg-white p-4 rounded-lg" style="width: 400px;">
                <h2 class="text-2xl font-bold mb-4">Scan QR Code</h2>
                <video id="cameraStream" width="100%" height="100%" autoplay></video>
                <button class="bg-red-500 text-white py-2 px-4 rounded mt-4" onclick="closeQrScanner()">Close Scanner</button>
            </div>
        </div>
    </div>

    <script>
        let currentStream;
        let actionType;

        function openQrScanner(action) {
            document.getElementById('qrScannerModal').classList.remove('hidden');
            actionType = action;
            openCameraApp();
        }

        function closeQrScanner() {
            if (currentStream) {
                const tracks = currentStream.getTracks();
                tracks.forEach(track => track.stop());
            }
            document.getElementById('qrScannerModal').classList.add('hidden');
        }

        function openCameraApp() {
            const video = document.getElementById("cameraStream");
            navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
                .then(stream => {
                    currentStream = stream;
                    video.srcObject = stream;
                    video.setAttribute("playsinline", true);

                    video.addEventListener("loadedmetadata", () => {
                        setInterval(() => scanQrCode(video), 500);
                    });
                })
                .catch(error => console.error("Camera access error:", error));
        }

       async function scanQrCode(video) {
    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d");
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, imageData.width, imageData.height);

    if (code) {
        try {
            const url = new URL(code.data); // Parse QR code as a URL
            const itemId = url.searchParams.get("Item_id"); // Extract Item_id
            
            if (itemId) {
                closeQrScanner(); // Stop the camera and close the scanner modal

                // Redirect the user to the action selection page
                const actionUrl = `action_selection.php?Item_id=${itemId}`;
                window.location.href = actionUrl;
            } else {
                alert("Invalid QR code format: Item_id not found.");
            }
        } catch (e) {
            alert("Invalid QR code format.");
        }
    }
}


            // Function to handle storing bad merchandise
            async function storeBadMerchandise(itemId, action) {
                const response = await fetch(`store_bad_merchandise.php?Item_id=${itemId}&action=${action}`);
                const data = await response.text();
                console.log("Store bad merchandise response:", data);
                alert(data); // Show server response to the user
            }

            // Function to handle storing defective merchandise
            async function storeDefectiveMerchandise(itemId, action) {
                const response = await fetch(`store_defective_merchandise.php?Item_id=${itemId}&action=${action}`);
                const data = await response.text();
                console.log("Store defective merchandise response:", data);
                alert(data); // Show server response to the user
            }

        // Function to handle adding or deducting stock
        async function updateStockQuantity(itemId, action) {
            const response = await fetch(`update_quantity.php?Item_id=${itemId}&action=${action}`);
            const data = await response.text();
            console.log("Update stock quantity response:", data);

            // Display the low stock warning if returned from PHP
            if (data.includes("Warning:")) {
                document.getElementById('notificationMessage').textContent = data;
                document.getElementById('notification').style.display = 'block';

                // Automatically hide the notification after 3 seconds
                setTimeout(() => {
                    document.getElementById('notification').style.display = 'none';
                }, 3000); // 3000ms = 3 seconds
            } else {
                alert(data); // Show regular response
            }
        }

        function ProductForm() {
            document.getElementById('addProductModal').classList.remove('hidden');
        }

        function closeAddProductForm() {
            document.getElementById('addProductModal').classList.add('hidden');
        }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

