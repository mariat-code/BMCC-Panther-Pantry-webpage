<?php
// Start session
session_start();

// Include necessary files for database connection and helper functions
include 'dbconnect.php';
include 'accesibles.php';

// Initialize variables for form prefill and logic control
$cunyID = '';
$firstName = '';
$lastName = '';
$adults18_64 = 0;
$childrenUnder18 = 0;
$adults65Plus = 0;
$showOrderForm = false;

// Check if the form is submitted to fetch student data based on CUNY ID
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['submitOrder'])) {
    $cunyID = get_safe('cunyID');

    // Prepare and execute the statement to fetch student data
    $stmt = $conn->prepare("SELECT firstName, lastName, adults18_64, childrenUnder18, adults65Plus FROM students WHERE cunyID = ?");
    $stmt->bind_param("i", $cunyID);
    $stmt->execute();
    $stmt->bind_result($firstName, $lastName, $adults18_64, $childrenUnder18, $adults65Plus);
    $stmt->fetch();
    $stmt->close();

    // Check if student data is found
    if ($firstName && $lastName) {
        $showOrderForm = true;
    } else {
        $error_message = "No student found with the given CUNY ID. Please try again.";
    }
}

// Check if the order form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitOrder'])) {
    $cunyID = get_safe('cunyID');
    $firstName = get_safe('firstName');
    $lastName = get_safe('lastName');
    $orderDate = get_safe('orderDate');
    $semester = get_safe('semester');
    $newAdults18_64 = get_safe('adults18_64');
    $newChildrenUnder18 = get_safe('childrenUnder18');
    $newAdults65Plus = get_safe('adults65Plus');
    $staff = get_safe('staff');
    $itemsReceived = get_safe('itemsReceived') ?? [];

    // Determine items received based on checkbox values
    $nonPerishable = in_array('nonPerishable', $itemsReceived) ? 1 : 0;
    $perishable = in_array('perishable', $itemsReceived) ? 1 : 0;
    $toiletry = in_array('toiletry', $itemsReceived) ? 1 : 0;
    $oil = in_array('oil', $itemsReceived) ? 1 : 0;
    $prePackedBag = in_array('prePackedBag', $itemsReceived) ? 1 : 0;

    // Fetch current household information
    $stmt = $conn->prepare("SELECT adults18_64, childrenUnder18, adults65Plus FROM students WHERE cunyID = ?");
    $stmt->bind_param("i", $cunyID);
    $stmt->execute();
    $stmt->bind_result($currentAdults18_64, $currentChildrenUnder18, $currentAdults65Plus);
    $stmt->fetch();
    $stmt->close();

    // Check if any household information has changed and update if necessary
    if ($currentAdults18_64 != $newAdults18_64 || $currentChildrenUnder18 != $newChildrenUnder18 || $currentAdults65Plus != $newAdults65Plus) {
        $updateStmt = $conn->prepare("UPDATE students SET adults18_64 = ?, childrenUnder18 = ?, adults65Plus = ? WHERE cunyID = ?");
        $updateStmt->bind_param("iiii", $newAdults18_64, $newChildrenUnder18, $newAdults65Plus, $cunyID);

        if ($updateStmt->execute()) {
            $update_message = "Student household information updated successfully.";
        } else {
            $update_error = "Error updating student information: " . $updateStmt->error;
        }

        $updateStmt->close();
    }

    // Insert the order into the orders table
    $stmt = $conn->prepare("INSERT INTO orders (cunyID, firstName, lastName, orderDate, semester, nonPerishable, perishable, toiletry, oil, prePackedBag, adults18_64, childrenUnder18, adults65Plus, staff) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssiiiiiiiis", $cunyID, $firstName, $lastName, $orderDate, $semester, $nonPerishable, $perishable, $toiletry, $oil, $prePackedBag, $newAdults18_64, $newChildrenUnder18, $newAdults65Plus, $staff);

    if ($stmt->execute()) {
        $order_message = "Order submitted successfully.";
    } else {
        $order_error = "Error submitting order: " . $stmt->error;
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Order</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        .page-content {
            background-color: rgba(211, 211, 211, 0.75); /* Light grey background with 75% opacity */
            padding: 10px; /* Padding for better visual appearance */
            border-radius: 10px; /* Rounded corners */
            width: fit-content;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container.main-content {
            display: flex;
            justify-content: center;
        }

        .header-content {
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body class="order-background">
    <?php include 'navbar.php'; ?>
    <br>
    <div class="container main-content">
        <div class="page-content" id="dynamicContainer">
            <div class="header-content">
                <h1>Assistance Request</h1>
                <?php if ($showOrderForm) { ?>
                    <h3>Student: <?php echo $firstName . ' ' . $lastName; ?> (CUNY ID: <?php echo $cunyID; ?>)</h3>
                <?php } ?>
            </div>

            <?php
            // Display error message if no student is found
            if (isset($error_message)) {
                echo "<p>$error_message</p>";
            }

            // Display the order form if student data is found
            if ($showOrderForm) {
            ?>

            <!-- Order form -->
            <form action="foodOrder.php" method="post">
                <input type="hidden" name="cunyID" value="<?php echo htmlspecialchars($cunyID); ?>">
                <table>
                    <tr><th>Date:</th><td colspan="2"><input type="date" name="orderDate" required></td></tr>
                    <tr>
                        <th>Semester:</th>
                        <td>
                            <select name="semester" class="semester-dropdown" required>
                                <option value="Summer 2024">Summer 2024</option>
                                <option value="Fall 2024">Fall 2024</option>
                                <option value="Winter 2025">Winter 2025</option>
                                <option value="Spring 2025">Spring 2025</option>
                            </select>
                        </td>
                    </tr>
                    <tr><th>First Name:</th><td> <input type="text" name="firstName" value="<?php echo htmlspecialchars($firstName); ?>" readonly></td></tr>
                    <tr><th>Last Name:</th><td> <input type="text" name="lastName" value="<?php echo htmlspecialchars($lastName); ?>" readonly></td></tr>
                    <tr><th>Adults between 18 & 64:</th><td> <input type="number" value="<?php echo htmlspecialchars($adults18_64); ?>" name="adults18_64" required></td></tr>
                    <tr><th>Children Under 18:</th><td> <input type="number" value="<?php echo htmlspecialchars($childrenUnder18); ?>" name="childrenUnder18" required></td></tr>
                    <tr><th>Adults over 65+:</th><td> <input type="number" value="<?php echo htmlspecialchars($adults65Plus); ?>" name="adults65Plus" required></td></tr>
                    <tr><th>Staff:</th><td> <input type="text" name="staff" placeholder="Initials" required></td></tr>
                    <tr><th>Items Received (FDOT):</th><td>
                        <input type="checkbox" id="perishable" name="itemsReceived[]" value="perishable">
                        <label for="perishable"> Perishable</label><br>
                        <input type="checkbox" id="nonPerishable" name="itemsReceived[]" value="nonPerishable">
                        <label for="nonPerishable"> Non Perishable</label><br>
                        <input type="checkbox" id="oil" name="itemsReceived[]" value="oil">
                        <label for="oil"> Oils</label><br>
                        <input type="checkbox" id="toiletry" name="itemsReceived[]" value="toiletry">
                        <label for="toiletry"> Toiletries</label><br>
                        <input type="checkbox" id="prePackedBag" name="itemsReceived[]" value="prePackedBag">
                        <label for="prePackedBag"> Pre-Packed Bag</label><br>
                    </td></tr>
                </table><br>
                <input type="submit" name="submitOrder" value="Submit Order">
            </form>

            <?php
            } else {
            ?>
            <!-- Form to get CUNY ID -->
            <form action="foodOrder.php" method="post">
                <label for="cunyID">CUNY ID:</label>
                <input type="text" id="cunyID" name="cunyID" required>
                <input type="submit" value="Next">
            </form>
            <?php
            }

            // Display messages based on the update and order submission status
            if (isset($update_message)) {
                echo "<p>$update_message</p>";
            }

            if (isset($order_message)) {
                echo "<p>$order_message</p>";
            }

            if (isset($update_error)) {
                echo "<p>$update_error</p>";
            }

            if (isset($order_error)) {
                echo "<p>$order_error</p>";
            }
            ?>
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
    <script src="resize.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
