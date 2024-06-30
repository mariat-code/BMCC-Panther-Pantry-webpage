<?php
session_start();
?>

<?php
include 'dbconnect.php';
include 'accesibles.php';

// Function to fetch available menu items
function fetchAvailableMenuData($conn) {
    $query = "SELECT * FROM menu WHERE availability = 1";
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

// Fetch available menu data
$menuData = fetchAvailableMenuData($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body class="menu-background">
    <?php include 'navbar.php'; ?>
    <br>
    <div class="page-content" id="dynamicContainer">
        <h1>Food Menu</h1>
        <h3>Available Items</h3>
        <table class="table table-bordered tablestyles page-container ">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Availability</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menuData as $menuItem): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($menuItem['item']); ?></td>
                        <td><?php echo $menuItem['availability'] ? 'Available' : 'Unavailable'; ?></td>
                        <td><?php echo htmlspecialchars($menuItem['type']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
</body>
</html>