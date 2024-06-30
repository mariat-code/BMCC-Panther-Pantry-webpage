<?php
include 'auth.php';
include 'dbconnect.php';
include 'accesibles.php';

$errorFlag = false; // Initialize error flag
$errorMessages = ""; // Initialize error messages variable
$successFlag = false; // Initialize success flag
$successMessages = ""; // Initialize success messages variable

function fetchReportData($conn, $reportType, $startDate, $endDate) {
    switch ($reportType) {
        case 'registrations':
            $stmt = $conn->prepare("SELECT * FROM students WHERE registrationDate BETWEEN ? AND ?");
            break;
        case 'orders':
            $stmt = $conn->prepare("SELECT * FROM orders WHERE orderDate BETWEEN ? AND ?");
            break;
        case 'household':
            $stmt = $conn->prepare("SELECT * FROM orders WHERE orderDate BETWEEN ? AND ?");
            break;
        default:
            return [];
    }
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
    return $data;
}

function fetchTotalCount($conn, $reportType, $startDate, $endDate) {
    switch ($reportType) {
        case 'registrations':
            $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM students WHERE registrationDate BETWEEN ? AND ?");
            break;
        case 'orders':
            $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM orders WHERE orderDate BETWEEN ? AND ?");
            break;
        case 'household':
            $stmt = $conn->prepare("SELECT SUM(adults18_64 + childrenUnder18 + adults65Plus) AS count FROM orders WHERE orderDate BETWEEN ? AND ?");
            break;
        default:
            return 0;
    }
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'];
}

function getFirstAndLastDayOfMonth() {
    $firstDay = date('Y-m-01');
    $lastDay = date('Y-m-t');
    return [$firstDay, $lastDay];
}

list($defaultStartDate, $defaultEndDate) = getFirstAndLastDayOfMonth();

function generateCSV($reportData, $reportType) {
    ob_start();
    $filename = $reportType . "_report_" . date("YmdHis") . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=' . $filename);
    $output = fopen('php://output', 'w');

    switch ($reportType) {
        case 'registrations':
            fputcsv($output, ['CUNY ID', 'First Name', 'Last Name', 'Registration Date']);
            foreach ($reportData as $row) {
                fputcsv($output, [$row['cunyID'], $row['firstName'], $row['lastName'], $row['registrationDate']]);
            }
            break;

        case 'orders':
            fputcsv($output, ['CUNY ID', 'First Name', 'Last Name', 'Order Date', 'Semester', 'Non Perishable', 'Perishable', 'Toiletry', 'Oil', 'Pre-Packed Bag', 'Adults 18-64', 'Children Under 18', 'Adults 65+', 'Staff']);
            foreach ($reportData as $row) {
                fputcsv($output, [$row['cunyID'], $row['firstName'], $row['lastName'], $row['orderDate'], $row['semester'], $row['nonPerishable'], $row['perishable'], $row['toiletry'], $row['oil'], $row['prePackedBag'], $row['adults18_64'], $row['childrenUnder18'], $row['adults65Plus'], $row['staff']]);
            }
            break;

        case 'household':
            fputcsv($output, ['CUNY ID', 'First Name', 'Last Name', 'Order Date', 'Adults 18-64', 'Children Under 18', 'Adults 65+']);
            foreach ($reportData as $row) {
                fputcsv($output, [$row['cunyID'], $row['firstName'], $row['lastName'], $row['orderDate'], $row['adults18_64'], $row['childrenUnder18'], $row['adults65Plus']]);
            }
            break;
    }

    fclose($output);
    ob_end_flush();
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generateCSV'])) {
    $reportType = get_safe('reportType');
    $startDate = get_safe('startDate');
    $endDate = get_safe('endDate');

    if (is_null($reportType) || is_null($startDate) || is_null($endDate)) {
        echo "Error: Missing report type or date range.";
        exit;
    }

    if (!$conn) {
        die('Could not connect: ' . mysqli_error($conn));
    }

    $reportData = fetchReportData($conn, $reportType, $startDate, $endDate);
    generateCSV($reportData, $reportType);
}

function backupTable($conn, $sourceTable, $backupTable) {
    global $errorFlag, $errorMessages, $successFlag, $successMessages; // Access the global error and success flags and messages

    // Drop the backup table if it exists
    $dropTable = "DROP TABLE IF EXISTS `$backupTable`";
    if ($conn->query($dropTable) === TRUE) {
        $successMessages .= "Backup table '$backupTable' dropped successfully.<br>";
    } else {
        $errorFlag = true;
        $errorMessages .= "<span class='debug-output'>Error dropping table '$backupTable': " . $conn->error . "<br></span>";
    }

    // Create a new backup table with the structure of the source table
    $createTable = "CREATE TABLE `$backupTable` LIKE `$sourceTable`";
    if ($conn->query($createTable) === TRUE) {
        $successMessages .= "Backup table '$backupTable' created successfully.<br>";
    } else {
        $errorFlag = true;
        $errorMessages .= "<span class='debug-output'>Error creating table '$backupTable': " . $conn->error . "<br></span>";
    }

    // Copy data from the source table to the backup table
    $copyData = "INSERT INTO `$backupTable` SELECT * FROM `$sourceTable`";
    if ($conn->query($copyData) === TRUE) {
        $successMessages .= "Data copied to table '$backupTable' successfully.<br>";
        $successFlag = true;
    } else {
        $errorFlag = true;
        $errorMessages .= "<span class='debug-output'>Error copying data to table '$backupTable': " . $conn->error . "<br></span>";
    }
}

function restoreTable($conn, $backupTable, $targetTable) {
    global $errorFlag, $errorMessages, $successFlag, $successMessages; // Access the global error and success flags and messages

    // Delete all rows in the target table to remove existing data
    $deleteData = "DELETE FROM `$targetTable`";
    if ($conn->query($deleteData) === TRUE) {
        $successMessages .= "Data from table '$targetTable' deleted successfully.<br>";
    } else {
        $errorFlag = true;
        $errorMessages .= "<span class='debug-output'>Error deleting data from table '$targetTable': " . $conn->error . "<br></span>";
    }

    // Copy data from the backup table to the target table
    $copyData = "INSERT INTO `$targetTable` SELECT * FROM `$backupTable`";
    if ($conn->query($copyData) === TRUE) {
        $successMessages .= "Data restored to table '$targetTable' successfully.<br>";
        $successFlag = true;
    } else {
        $errorFlag = true;
        $errorMessages .= "<span class='debug-output'>Error restoring data to table '$targetTable': " . $conn->error . "<br></span>";
    }
}

function importCSV($conn, $filePath, $tableName) {
    global $errorFlag, $errorMessages, $successFlag, $successMessages; // Access the global error and success flags and messages

    if (($handle = fopen($filePath, 'r')) !== FALSE) {
        $headers = fgetcsv($handle, 1000, ','); // Get the CSV headers

        // Fetch table columns
        $tableColumns = getTableColumns($conn, $tableName);
        
        // Check if the headers match the target table columns
        if ($headers !== $tableColumns) {
            $errorFlag = true;
            $errorMessages .= "<span class='debug-output'>Error: The CSV headers do not match the columns in the $tableName table.<br></span>";
        } else {
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $values = implode("','", array_map([$conn, 'real_escape_string'], $data));
                $sql = "INSERT INTO `$tableName` (`" . implode("`,`", $headers) . "`) VALUES ('$values')";
                if (!$conn->query($sql)) {
                    $errorFlag = true;
                    $errorMessages .= "<span class='debug-output'>Error importing data: " . $conn->error . "<br></span>";
                }
            }
            fclose($handle);
            if (!$errorFlag) {
                $successMessages .= "<span class='debug-output'>Data imported successfully into table '$tableName'.<br></span>";
                $successFlag = true;
            }
        }
    } else {
        $errorFlag = true;
        $errorMessages .= "<span class='debug-output'>Error opening CSV file.<br></span>";
    }
}

function getTableColumns($conn, $tableName) {
    $columns = [];
    $result = $conn->query("SHOW COLUMNS FROM `$tableName`");
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    return $columns;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['generateCSV'])) {
        $reportType = get_safe('reportType');
        $startDate = get_safe('startDate');
        $endDate = get_safe('endDate');

        if (is_null($reportType) || is_null($startDate) || is_null($endDate)) {
            echo "Error: Missing report type or date range.";
            exit;
        }

        if (!$conn) {
            die('Could not connect: ' . mysqli_error($conn));
        }

        $reportData = fetchReportData($conn, $reportType, $startDate, $endDate);
        generateCSV($reportData, $reportType);
    } elseif (isset($_POST['backupDatabases'])) {
        if (!$conn) {
            die('Could not connect: ' . mysqli_error($conn));
        }

        // Backup tables within the pantherPantry database
        backupTable($conn, 'orders', 'ordersbkup');
        backupTable($conn, 'students', 'studentsbkup');

        if (!$errorFlag) {
            $successFlag = true;
            $successMessages .= "<span class='debug-output'>Tables backed up successfully.</span>";
        }
    } elseif (isset($_POST['restoreDatabases'])) {
        if (!$conn) {
            die('Could not connect: ' . mysqli_error($conn));
        }

        // Restore data from the backup tables to the original tables
        restoreTable($conn, 'ordersbkup', 'orders');
        restoreTable($conn, 'studentsbkup', 'students');

        if (!$errorFlag) {
            $successFlag = true;
            $successMessages .= "<span class='debug-output'>Tables restored successfully.</span>";
        }
    } elseif (isset($_POST['importCSV'])) {
        if (!$conn) {
            die('Could not connect: ' . mysqli_error($conn));
        }

        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true); // Create directory if it doesn't exist
        }

        $targetFile = $targetDir . basename($_FILES["csvFile"]["name"]);
        $tableName = get_safe('tableName');

        // Check if file is a CSV
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        if ($fileType != "csv") {
            echo "Only CSV files are allowed.<br>";
        } else {
            if (move_uploaded_file($_FILES["csvFile"]["tmp_name"], $targetFile)) {
                importCSV($conn, $targetFile, $tableName);
            } else {
                echo "Error uploading file.<br>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Report</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @media print {
            .print-logo {
                display: block;
                width: 100%;
                text-align: center;
                margin-bottom: 20px;
            }
            .print-logo img {
                width: 150px; /* Adjust the size of the logo */
            }
            .no-print {
                display: none;
            }
            .page-content {
                margin-top: 20px; /* Adjust as needed to avoid overlap with logo */
            }
        }
    </style>
    <script>function printReport() {window.print();}</script>
</head>
<body class="report-background">
    <?php include 'navbar.php'; ?>
    <br>
 <div class="page-content" id="dynamicContainer">
    <h1 class="no-print">Generate Report</h1>
     <!-- Error Container -->
     <?php if ($errorFlag): ?>
            <div id="error-container" class="error-container">
                <?php echo $errorMessages; ?>
            </div>
        <?php endif; ?>

        <!-- Success Container -->
        <?php if ($successFlag): ?>
            <div id="success-container" class="success-message">
                <?php echo $successMessages; ?>
            </div>
        <?php endif; ?>

    <div class="formContainer no-print">
        <div class="mainSection">
            <form action="report.php" method="post" class="centerForm no-Print mainForm">
                <label for="reportType">Report Type:</label>
                <select name="reportType" id="reportType" required>
                    <option value="registrations">Registrations</option>
                    <option value="orders">Orders</option>
                    <option value="household">Household People Served</option>
                </select>
                <br><br>
                <label for="startDate">Start Date:</label>
                <input type="date" id="startDate" name="startDate" value="<?php echo htmlspecialchars($defaultStartDate); ?>" required>
                <br><br>
                <label for="endDate">End Date:</label>
                <input type="date" id="endDate" name="endDate" value="<?php echo htmlspecialchars($defaultEndDate); ?>" required>
                <br><br>
                <div class="buttonContainer">
                    <button type="submit" name="generateReport">
                        <i class="fas fa-eye"></i> View Report
                    </button>
                    <button type="submit" name="generateCSV">
                        <i class="fas fa-download"></i> Download Report
                    </button>
                    <button type="button" class="printButton" onclick="printReport()">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                    <button type="button" id="toggleCsvForm" class="toggleButton">
                        <i class="fas fa-upload"></i> Upload Data
                    </button>
                    <button type="button" id="toggleBackupForm" class="toggleButton">
                        <i class="fas fa-database"></i> Backups
                    </button>
                </div>
            </form>
        </div>

        <div class="sideForms" class="backupForm">
            <!-- CSV Upload Form -->
            <form action="report.php" method="post" enctype="multipart/form-data" class="centerForm noPrint" id="csvForm" style="display: none;">
                <label for="csvFile">Upload .CSV File:</label>
                <input type="file" name="csvFile" id="csvFile" required>
                <br><br>
                <label for="tableName">Table Name:</label>
                <select name="tableName" id="tableName" required>
                    <option value="students">Students</option>
                    <option value="orders">Orders</option>
                </select>
                <br><br>
                <div class="backupButtons">
                    <input type="submit" name="importCSV" value="Import from Spreadsheet">
                </div>
            </form>

            <!-- Backup and Restore Form -->
            <div id="backupForm" class="backupForm" style="display: none;">
                <form action="report.php" method="post" class="centerForm noPrint">
                    <div class="backupButtons">
                        <input type="submit" name="backupDatabases" value="Backup Data" onclick="return confirm('Are you sure you want to backup the data?');">
                        <input type="submit" name="restoreDatabases" value="Restore Data" onclick="return confirm('Are you sure you want to restore the data?');">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['generateCSV']) && !isset($_POST['backupDatabases']) && !isset($_POST['restoreDatabases'])) {
        $reportType = get_safe('reportType');
        $startDate = get_safe('startDate');
        $endDate = get_safe('endDate');

        if (is_null($reportType) || is_null($startDate) || is_null($endDate)) {
            echo "Error: Missing report type or date range.";
            exit;
        }

        if (!$conn) {
            die('Could not connect: ' . mysqli_error($conn));
        }

        $totalCount = fetchTotalCount($conn, $reportType, $startDate, $endDate);
        $reportData = fetchReportData($conn, $reportType, $startDate, $endDate);

        echo "<h3>Total $reportType from $startDate to $endDate: " . htmlspecialchars($totalCount) . "</h3>";

        switch ($reportType) {
            case 'registrations':
                echo "<h2>Registrations Report from $startDate to $endDate</h2>";
                echo "<table class='tablestyles'><thead><tr><th>CUNY ID</th><th>First Name</th><th>Last Name</th><th>Registration Date</th></tr></thead><tbody>";
                foreach ($reportData as $row) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['cunyID']) . "</td>
                            <td>" . htmlspecialchars($row['firstName']) . "</td>
                            <td>" . htmlspecialchars($row['lastName']) . "</td>
                            <td>" . htmlspecialchars($row['registrationDate']) . "</td>
                          </tr>";
                }
                echo "</tbody></table>";
                break;

            case 'orders':
                echo "<h2>Orders Report from $startDate to $endDate</h2>";
                echo "<table class='tablestyles'><thead><tr><th>CUNY ID</th><th>First Name</th><th>Last Name</th><th>Order Date</th><th>Semester</th><th>Non Perishable</th><th>Perishable</th><th>Toiletry</th><th>Oil</th><th>Pre-Packed Bag</th><th>Adults 18-64</th><th>Children Under 18</th><th>Adults 65+</th><th>Staff</th></tr></thead><tbody>";
                foreach ($reportData as $row) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['cunyID']) . "</td>
                            <td>" . htmlspecialchars($row['firstName']) . "</td>
                            <td>" . htmlspecialchars($row['lastName']) . "</td>
                            <td>" . htmlspecialchars($row['orderDate']) . "</td>
                            <td>" . htmlspecialchars($row['semester']) . "</td>
                            <td>" . htmlspecialchars($row['nonPerishable']) . "</td>
                            <td>" . htmlspecialchars($row['perishable']) . "</td>
                            <td>" . htmlspecialchars($row['toiletry']) . "</td>
                            <td>" . htmlspecialchars($row['oil']) . "</td>
                            <td>" . htmlspecialchars($row['prePackedBag']) . "</td>
                            <td>" . htmlspecialchars($row['adults18_64']) . "</td>
                            <td>" . htmlspecialchars($row['childrenUnder18']) . "</td>
                            <td>" . htmlspecialchars($row['adults65Plus']) . "</td>
                            <td>" . htmlspecialchars($row['staff']) . "</td>
                          </tr>";
                }
                echo "</tbody></table>";
                break;

            case 'household':
                echo "<h2>Household People Served Report from $startDate to $endDate</h2>";
                echo "<table class='tablestyles'><thead><tr><th>CUNY ID</th><th>First Name</th><th>Last Name</th><th>Order Date</th><th>Adults 18-64</th><th>Children Under 18</th><th>Adults 65+</th></tr></thead><tbody>";
                foreach ($reportData as $row) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['cunyID']) . "</td>
                            <td>" . htmlspecialchars($row['firstName']) . "</td>
                            <td>" . htmlspecialchars($row['lastName']) . "</td>
                            <td>" . htmlspecialchars($row['orderDate']) . "</td>
                            <td>" . htmlspecialchars($row['adults18_64']) . "</td>
                            <td>" . htmlspecialchars($row['childrenUnder18']) . "</td>
                            <td>" . htmlspecialchars($row['adults65Plus']) . "</td>";
                }
                echo "</tbody></table>";
                break;
        }

        $conn->close();
    }

    if ($errorFlag) {
        echo "<div class='error-container'>$errorMessages</div>";
    }
    if ($successFlag) {
        echo "<div class='success-message'>$successMessages</div>";
    }
    ?>


    <br><br>
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

        <script>
        document.getElementById("toggleCsvForm").addEventListener("click", function() {
        var csvForm = document.getElementById("csvForm");
        var errorContainer = document.getElementById("errorContainer");
        var successContainer = document.getElementById("successContainer");

        // Clear error and success messages
        if (errorContainer) {
            errorContainer.innerHTML = "";
            errorContainer.style.display = "none";
        }
        if (successContainer) {
            successContainer.innerHTML = "";
            successContainer.style.display = "none";
        }

        // Toggle CSV form visibility
        csvForm.style.display = csvForm.style.display === "none" || csvForm.style.display === "" ? "block" : "none";
        });

        document.getElementById("toggleBackupForm").addEventListener("click", function() {
            var backupForm = document.getElementById("backupForm");

            // Toggle Backup form visibility
            backupForm.style.display = backupForm.style.display === "none" || backupForm.style.display === "" ? "block" : "none";
        });

        // Ensure forms are hidden on page load to reset the state
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("csvForm").style.display = "none";
            document.getElementById("backupForm").style.display = "none";
        });
    </script>
  </div>
      <br>
<footer class="footer no-print">
    <div class="footer-content">
        <img src="misc/bmcclogo.png" alt="BMCC Logo" class="footer-logo left">
        <p>Borough of Manhattan Community College<br>
        The City University of New York<br>
        199 Chambers Street, New York, NY, 10007<br>
        Phone: (212) 220-8000</p>
        <img src="misc/cunylogo.png" alt="CUNY Logo" class="footer-logo right">
    </div>
</footer>
    <script src="layouts/resize.js"></script>
</body>
</html>
