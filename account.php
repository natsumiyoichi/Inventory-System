<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'inventory_system');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all users
$query = "SELECT * FROM users";
$result = $conn->query($query);

// Handle form submissions for Add, Update, or Delete operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_user'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_message = "Email already registered. Please choose another one.";
        } else {
            // Insert new user if email doesn't exist
            $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $email, $password);
            $stmt->execute();
            $success_message = "User added successfully!";
        }
    } elseif (isset($_POST['update_user'])) {
        $id = $_POST['user_id'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Update user details
        $stmt = $conn->prepare("UPDATE users SET email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssi", $email, $password, $id);
        $stmt->execute();
        $success_message = "User updated successfully!";
    } elseif (isset($_POST['delete_user'])) {
        $id = $_POST['user_id'];

        // Delete user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $success_message = "User deleted successfully!";
    }
}

// Re-fetch users after the operation
$result = $conn->query($query);

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Accounts - Coffee Geney</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Pacifico&display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background: #f5f5dc;
        }
        .sidebar {
            background: linear-gradient(135deg, #6f4e37, #3e2723);
        }
        .sidebar h1 {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            font-family: 'Pacifico', cursive;
        }
        .button {
            background: #8b5e34;
            color: white;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .button {
            background: #8b5e34; /* Base button color */
            color: white;
            transition: transform 0.3s, box-shadow 0.3s, background-color 0.3s;
        }

        .button:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.2);
            background: #8b5e34; /* Same as base color */
        }

        .bg-blue-500 {
            background: #3b82f6; /* Base color for blue button */
            color: white;
            transition: transform 0.3s, box-shadow 0.3s, background-color 0.3s;
        }

        .bg-blue-500:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.2);
            background: #3b82f6; /* Same as base color */
        }

        .bg-red-500 {
            background: #ef4444; /* Base color for red button */
            color: white;
            transition: transform 0.3s, box-shadow 0.3s, background-color 0.3s;
        }

        .bg-red-500:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.2);
            background: #ef4444; /* Same as base color */
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .fade {
            transition: opacity 1s ease-out;
        }
        .fade-out {
            opacity: 0;
        }
    </style>
</head>
<body class="flex flex-col md:flex-row">
    <!-- Sidebar -->
    <div class="sidebar w-1/4 h-screen p-4">
        <div class="flex flex-col items-center">
            <button class="bg-transparent mb-4">
                <img alt="Coffee Geney logo" src="logo.jpg" width="100" height="100" style="border-radius: 50%;"/>
            </button>
            <h1 class="text-white text-2xl font-bold mb-8">Coffee Geney</h1>
        </div>
        <nav class="text-white">
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
    <div class="flex-1 p-10">
        <h1 class="text-4xl font-bold mb-8"><i class="fas fa-user text-5xl"></i> Manage Accounts</h1>

        <!-- Add User Section -->
        <h2 class="text-2xl font-bold mb-4">Add New User</h2>
        <form method="post" class="space-y-4 mb-8">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none" required>
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none" required>
            </div>
            <button type="submit" name="add_user" class="button py-2 px-4 rounded-lg">Add User</button>
        </form>

        <!-- Feedback Messages -->
        <?php if (isset($error_message)): ?>
            <div class="bg-red-500 text-white py-2 px-4 rounded mb-4"><?php echo $error_message; ?></div>
        <?php elseif (isset($success_message)): ?>
            <div id="successMessage" class="bg-green-500 text-white py-2 px-4 rounded mb-4 fade"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <!-- Existing Users Table -->
        <h2 class="text-2xl font-bold mb-4">Existing Users</h2>
        <table class="min-w-full bg-white border border-gray-200">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b text-left">Email</th>
                    <th class="py-2 px-4 border-b text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="py-2 px-4 border-b">
                            <!-- Edit Button -->
                            <button onclick="openModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['email']); ?>')" class="bg-blue-500 text-white py-1 px-4 rounded-lg hover:transform hover:translate-y-[-5px] hover:shadow-md">Update</button>
                            <!-- Delete Button -->
                            <form method="post" class="inline-block">
                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_user" class="bg-red-500 text-white py-1 px-4 rounded-lg hover:transform hover:translate-y-[-5px] hover:shadow-md">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for Editing User -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 class="text-2xl font-bold mb-4">Update User</h2>
            <form method="post">
                <input type="hidden" id="update_user_id" name="user_id">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" id="update_email" name="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none" required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" id="update_password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none" required>
                </div>
                <button type="submit" name="update_user" class="button py-2 px-4 rounded-lg mt-4">Update User</button>
            </form>
        </div>
    </div>

    <script>
        // Function to open the modal and fill in the form fields
        function openModal(id, email) {
            document.getElementById('update_user_id').value = id;
            document.getElementById('update_email').value = email;
            document.getElementById('updateModal').style.display = "block";
        }

        // Function to close the modal
        function closeModal() {
            document.getElementById('updateModal').style.display = "none";
        }

        // Function to fade out success message after 3 seconds
        setTimeout(function() {
            var successMessage = document.getElementById('successMessage');
            if (successMessage) {
                successMessage.classList.add('fade-out');
            }
        }, 3000);
    </script>
</body>
</html>
