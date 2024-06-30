<?php
include 'auth.php'; // Ensure this file checks admin status
include 'dbconnect.php';
include 'accesibles.php';

// Initialize messages
$errorFlag = false;
$errorMessages = "";
$successFlag = false;
$successMessages = "";

// Function to fetch all menu data
function fetchMenuData($conn) {
    $query = "SELECT * FROM menu";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
    return $data;
}

// Function to generate CSV for menu data
function generateCSV($menuData) {
    ob_start();
    $filename = "menu_report_" . date("YmdHis") . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=' . $filename);
    $output = fopen('php://output', 'w');

    // Add headers
    fputcsv($output, ['Item', 'Availability', 'Type']);
    
    // Add data
    foreach ($menuData as $row) {
        fputcsv($output, [$row['item'], $row['availability'] ? 'Yes' : 'No', $row['type']]);
    }

    fclose($output);
    ob_end_flush();
    exit;
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add menu item
    if (isset($_POST['addMenuItem'])) {
        $item = get_safe('item');
        $type = get_safe('type');
        $availability = isset($_POST['availability']) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO menu (item, availability, type) VALUES (?, ?, ?)");
        if (!$stmt) {
            $errorFlag = true;
            $errorMessages .= "Prepare failed: " . $conn->error . "<br>";
        } else {
            $stmt->bind_param("sis", $item, $availability, $type);
            if ($stmt->execute()) {
                $successFlag = true;
                $successMessages .= "Menu item added successfully!<br>";
            } else {
                $errorFlag = true;
                $errorMessages .= "Execute failed: " . $stmt->error . "<br>";
            }
            $stmt->close();
        }
    }

    // Update menu item
    if (isset($_POST['updateMenuItem'])) {
        $id = intval($_POST['id']);
        $item = get_safe('item');
        $type = get_safe('type');
        $availability = isset($_POST['availability']) ? 1 : 0;

        $stmt = $conn->prepare("UPDATE menu SET item = ?, availability = ?, type = ? WHERE id = ?");
        if (!$stmt) {
            $errorFlag = true;
            $errorMessages .= "Prepare failed: " . $conn->error . "<br>";
        } else {
            $stmt->bind_param("sisi", $item, $availability, $type, $id);
            if ($stmt->execute()) {
                $successFlag = true;
                $successMessages .= "Menu item updated successfully!<br>";
            } else {
                $errorFlag = true;
                $errorMessages .= "Execute failed: " . $stmt->error . "<br>";
            }
            $stmt->close();
        }
    }

    // Delete menu item
    if (isset($_POST['deleteMenuItem'])) {
        $id = intval($_POST['id']);

        $stmt = $conn->prepare("DELETE FROM menu WHERE id = ?");
        if (!$stmt) {
            $errorFlag = true;
            $errorMessages .= "Prepare failed: " . $conn->error . "<br>";
        } else {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $successFlag = true;
                $successMessages .= "Menu item deleted successfully!<br>";
            } else {
                $errorFlag = true;
                $errorMessages .= "Execute failed: " . $stmt->error . "<br>";
            }
            $stmt->close();
        }
    }

    // Export CSV
    if (isset($_POST['exportCSV'])) {
        $menuData = fetchMenuData($conn);
        generateCSV($menuData);
    }

    // Import CSV
    if (isset($_POST['importCSV']) && isset($_FILES["csvFile"]["tmp_name"])) {
        if (($handle = fopen($_FILES["csvFile"]["tmp_name"], 'r')) !== FALSE) {
            fgetcsv($handle); // Skip header row
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $stmt = $conn->prepare("INSERT INTO menu (item, availability, type) VALUES (?, ?, ?)");
                if (!$stmt) {
                    $errorFlag = true;
                    $errorMessages .= "Prepare failed: " . $conn->error . "<br>";
                } else {
                    $stmt->bind_param("sis", $data[0], $data[1], $data[2]);
                    if (!$stmt->execute()) {
                        $errorFlag = true;
                        $errorMessages .= "Execute failed: " . $stmt->error . "<br>";
                    }
                    $stmt->close();
                }
            }
            fclose($handle);
            if (!$errorFlag) {
                $successFlag = true;
                $successMessages .= "Menu items imported successfully!<br>";
            }
        } else {
            $errorFlag = true;
            $errorMessages .= "Error opening CSV file.<br>";
        }
    }
}

// Fetch all menu data
$menuData = fetchMenuData($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="inventory-background">
<?php include 'navbar.php'; ?>
    <br>
    <div class="page-content" id="dynamicContainer">
        <h1>Inventory Management</h1>

        <?php if ($errorFlag): ?>
            <div class="alert alert-danger"><?php echo $errorMessages; ?></div>
        <?php endif; ?>

        <?php if ($successFlag): ?>
            <div class="alert alert-success"><?php echo $successMessages; ?></div>
        <?php endif; ?>

        <form action="" method="POST" class="centerForm">
            <label for="item"><strong>Food Item:</strong></label><br>
            <input type="text" id="item" name="item" required><br><br>

            <label for="type"><strong>Type:</strong></label><br>
            <select id="type" name="type" required>
                <option value="perishable">Perishable</option>
                <option value="non-perishable">Non-Perishable</option>
                <option value="toiletries">Toiletries</option>
                <option value="oils">Oils</option>
            </select><br><br>

            <label for="availability"><strong>Available:</strong></label><br>
            <input type="checkbox" id="availability" name="availability"><br><br>

            <input type="submit" name="addMenuItem" value="Add" class="button">
        </form>

        <br>

        <h2>Menu Items</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Availability</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menuData as $menuItem): ?>
                    <tr>
                        <form action="" method="POST">
                            <td><input type="text" name="item" value="<?php echo htmlspecialchars($menuItem['item']); ?>" required></td>
                            <td><input type="checkbox" name="availability" <?php echo $menuItem['availability'] ? 'checked' : ''; ?>></td>
                            <td>
                                <select name="type" required>
                                    <option value="perishable" <?php echo $menuItem['type'] == 'perishable' ? 'selected' : ''; ?>>Perishable</option>
                                    <option value="non-perishable" <?php echo $menuItem['type'] == 'non-perishable' ? 'selected' : ''; ?>>Non-Perishable</option>
                                    <option value="toiletries" <?php echo $menuItem['type'] == 'toiletries' ? 'selected' : ''; ?>>Toiletries</option>
                                    <option value="oils" <?php echo $menuItem['type'] == 'oils' ? 'selected' : ''; ?>>Oils</option>
                                </select>
                            </td>
                            <td>
                                 <input type="hidden" name="id" value="<?php echo $menuItem['id']; ?>">
                                <input type="submit" name="updateMenuItem" value="Update" class="button button-edit">
                                <input type="submit" name="deleteMenuItem" value="Delete" class="button button-delete" onclick="return confirm('Are you sure you want to delete this item?');">
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <br>

        <button type="button" id="toggleCsvForm" class="toggleButton">
            <i class="fas fa-file-csv"></i> CSV Operations
        </button>
        <div id="csvOperations" style="display: none;">
            <form action="" method="POST" enctype="multipart/form-data" class="centerForm">
                <label for="csvFile">Import from CSV:</label><br>
                <input type="file" name="csvFile" id="csvFile" required><br><br>
                <input type="submit" name="importCSV" value="Import CSV" class="button">
            </form>

            <br>

            <form action="" method="POST" class="centerForm">
                <input type="submit" name="exportCSV" value="Export CSV" class="button">
            </form>
        </div>

    </div>
    <br>
    <footer class="footer">
        <div class="footer-content">
            <img src="misc/bmcclogo.png" alt="BMCC Logo" class="footer-logo left">
            <p>Borough of Manhattan Community College<br>
                The City University of New York<br>
                199 Chambers Street, New York, NY, 10007<br>
                Phone: (212) 220-8000</p>
            <img src="misc/cunylogo.png" alt="CUNY Logo" class="footer-logo right">
        </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
        // Toggle the CSV operations form
        document.getElementById("toggleCsvForm").addEventListener("click", function() {
            var csvOperations = document.getElementById("csvOperations");
            csvOperations.style.display = csvOperations.style.display === "none" || csvOperations.style.display === "" ? "block" : "none";
        });
    </script>
</body>
</html>