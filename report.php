<html>
<head>
    <title>Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Pacifico&display=swap" rel="stylesheet"/>
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
    <div class="w-full p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold"><i class="fas fa-file-alt text-4xl"></i> Reports</h1>
            <div class="relative"></div>
        </div>
        <p class="text-lg mb-8">Coffee Geney Stock Report</p>
        <div class="grid grid-cols-2 gap-4 mb-8">
            <button class="button py-4 px-8 rounded-md" onclick="toggleReport('currentStocksReport')"><i class="fas fa-clipboard-list text-3xl"></i> Current Stocks</button>
            <button class="button py-4 px-8 rounded-md" onclick="toggleReport('badMerchandiseReport')"><i class="fas fa-ban text-3xl"></i> Bad Merchandise</button>
            <button class="button py-4 px-8 rounded-md col-span-2" onclick="toggleReport('defectiveMerchandiseReport')"><i class="fas fa-times-circle text-3xl"></i> Defective Merchandise</button>
        </div>
        <!-- Report Sections -->
        <div class="reportSection bg-white p-8 rounded-md shadow-md hidden" id="currentStocksReport">
            <h2 class="text-2xl font-bold mb-4">Current Stocks Report</h2>
            <div class="border p-4 mb-4 h-96" id="currentStocksContent">
                <?php
                // Fetch and display data from current_stock table
                $host = 'localhost';
                $user = 'root';
                $pass = '';
                $db = 'inventory_system';

                $conn = new mysqli($host, $user, $pass, $db);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $sql = "SELECT * FROM current_stock";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    echo "<table class='w-full text-left border' id='currentStockTable'>";
                    echo "<tr><th class='p-2 border'>Product Name</th><th class='p-2 border'>Quantity</th><th class='p-2 border'>Last Report Date</th></tr>";
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td class='p-2 border'>" . htmlspecialchars($row['product_name']) . "</td>";
                        echo "<td class='p-2 border'>" . htmlspecialchars($row['quantity']) . "</td>";
                        echo "<td class='p-2 border'>" . htmlspecialchars($row['report_date']) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p class='text-gray-600'>No current stocks found.</p>";
                }
                ?>
            </div>
            <!-- Download and Print Buttons -->
            <div class="flex space-x-4 mt-4">
            <button class="button py-2 px-4 rounded-md" onclick="downloadReport('currentStockTable', 'Current Stocks Report')">Download</button>
            <button class="button py-2 px-4 rounded-md" onclick="printReport('currentStockTable', 'Current Stocks Report')">Print</button>
            </div>
        </div>
        <!-- Bad Merchandise Report Section -->
        <div class="reportSection bg-white p-8 rounded-md shadow-md hidden" id="badMerchandiseReport">
            <h2 class="text-2xl font-bold mb-4">Bad Merchandise Report</h2>
            <div class="border p-4 mb-4 h-96 overflow-auto" id="badMerchandiseContent">
                <?php
                $sql = "SELECT id, product_name, quantity, DATE_FORMAT(report_date, '%Y-%m-%d %H:%i:%s') AS report_date FROM bad_merchandise";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    echo "<table class='w-full text-left border' id='badMerchandiseTable'>";
                    echo "<tr><th class='p-2 border'>Product Name</th><th class='p-2 border'>Quantity</th><th class='p-2 border'>Last Report Date</th><th class='p-2 border'>Action</th></tr>";
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td class='p-2 border'>" . htmlspecialchars($row['product_name']) . "</td>";
                        echo "<td class='p-2 border'>" . htmlspecialchars($row['quantity']) . "</td>";
                        echo "<td class='p-2 border'>" . htmlspecialchars($row['report_date']) . "</td>";
                        echo "<td class='p-2 border'>
                                <form action='reset_quantity.php' method='POST'>
                                    <input type='hidden' name='reportType' value='bad_merchandise'>
                                    <input type='hidden' name='productId' value='" . $row['id'] . "'>
                                    <button class='saddle-button py-1 px-4 rounded-md' type='submit'>Reset</button>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p class='text-gray-600'>No bad merchandise records found.</p>";
                }
                ?>
            </div>
            <!-- Download and Print Buttons -->
            <div class="flex space-x-4 mt-4">
            <button class="button py-2 px-4 rounded-md" onclick="downloadReport('badMerchandiseTable', 'Bad Merchandise Report')">Download</button>
            <button class="button py-2 px-4 rounded-md" onclick="printReport('badMerchandiseTable', 'Bad Merchandise Report')">Print</button>
            </div>
        </div>
        <!-- Defective Merchandise Report Section -->
        <div class="reportSection bg-white p-8 rounded-md shadow-md hidden" id="defectiveMerchandiseReport">
            <h2 class="text-2xl font-bold mb-4">Defective Merchandise Report</h2>
            <div class="border p-4 mb-4 h-96 overflow-auto" id="badMerchandiseContent">
                <?php
                // Fetch and display data from the bad_merchandise table
                $host = 'localhost';
                $user = 'root';
                $pass = '';
                $db = 'inventory_system';

                $conn = new mysqli($host, $user, $pass, $db);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $sql = "SELECT id, product_name, quantity, DATE_FORMAT(report_date, '%Y-%m-%d %H:%i:%s') AS report_date FROM defective_merchandise";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    echo "<table class='w-full text-left border' id='defectiveMerchandiseTable'>";
                    echo "<tr><th class='p-2 border'>Product Name</th><th class='p-2 border'>Quantity</th><th class='p-2 border'>Last Report Date</th><th class='p-2 border'>Action</th></tr>";
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td class='p-2 border'>" . htmlspecialchars($row['product_name']) . "</td>";
                        echo "<td class='p-2 border'>" . htmlspecialchars($row['quantity']) . "</td>";
                        echo "<td class='p-2 border'>" . htmlspecialchars($row['report_date']) . "</td>";
                        echo "<td class='p-2 border'>
                                <form action='reset_quantity.php' method='POST'>
                                    <input type='hidden' name='reportType' value='defective_merchandise'>
                                    <input type='hidden' name='productId' value='" . $row['id'] . "'>
                                    <button class='saddle-button py-1 px-4 rounded-md' type='submit'>Reset</button>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p class='text-gray-600'>No defective merchandise records found.</p>";
                }
                ?>
            </div>
            <!-- Download and Print Buttons -->
            <div class="flex space-x-4 mt-4">
            <button class="button py-2 px-4 rounded-md" onclick="downloadReport('defectiveMerchandiseTable', 'Defective Merchandise Report')">Download</button>
            <button class="button py-2 px-4 rounded-md" onclick="printReport('defectiveMerchandiseTable', 'Defective Merchandise Report')">Print</button>
            </div>
        </div>
        </div>
    <script>
        function toggleReport(reportId) {
            const reportSections = document.querySelectorAll(".reportSection");
            reportSections.forEach(section => section.classList.add("hidden"));
            document.getElementById(reportId).classList.toggle("hidden");
        }
        function downloadReport(tableId, reportTitle) {
            const table = document.getElementById(tableId);
            let csv = `${reportTitle}\n\n`; // Add report title to CSV header
            const rows = table.querySelectorAll('tr');
            
            // Loop through each row and only extract the desired columns
            rows.forEach(row => {
                const cols = row.querySelectorAll('td');
                if (cols.length > 0) {  // Skip header row
                    const rowData = [
                        cols[0].innerText,  // Product Name
                        cols[1].innerText,  // Quantity
                        cols[2].innerText   // Last Report Date
                    ];
                    csv += rowData.join(',') + '\n';
                }
            });

            const link = document.createElement('a');
            link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
            link.download = `${reportTitle}.csv`; // Use report title in filename
            link.click();
        }

        function printReport(tableId, reportTitle) {
            const table = document.getElementById(tableId);
            const newWindow = window.open('', '', 'width=800, height=600');
            newWindow.document.write('<html><head><title>Print Report</title></head><body>');
            newWindow.document.write(`<h2>${reportTitle}</h2>`); // Add report title before the table

            // Create a new table for printing with only the necessary columns
            const newTable = document.createElement('table');
            const headerRow = table.querySelector('tr');
            const newHeader = newTable.insertRow();

            // Create header cells
            newHeader.insertCell().innerText = 'Product Name';
            newHeader.insertCell().innerText = 'Quantity';
            newHeader.insertCell().innerText = 'Last Report Date';

            // Loop through table rows and add only the desired columns
            const rows = table.querySelectorAll('tr');
            rows.forEach((row, index) => {
                if (index > 0) {  // Skip the header row
                    const newRow = newTable.insertRow();
                    const cols = row.querySelectorAll('td');
                    if (cols.length > 0) {
                        newRow.insertCell().innerText = cols[0].innerText;  // Product Name
                        newRow.insertCell().innerText = cols[1].innerText;  // Quantity
                        newRow.insertCell().innerText = cols[2].innerText;  // Last Report Date
                    }
                }
            });

            newWindow.document.write(newTable.outerHTML); // Write the new table to the document
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.print();
        }
    </script>
</body>
</html>