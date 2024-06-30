<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Order History</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
<?php
include 'dbconnect.php';
include 'auth.php';
include 'navbar.php';
include 'accesibles.php';
?>
<br>
    <div class="page-content" id="dynamicContainer">
<h1>Order History for Student</h1>
<?php
if (isset($_GET['cunyID'])) {
    $cunyID = get_safe('cunyID');

    // Retrieve student information
    $stmt = $conn->prepare("SELECT firstName, lastName FROM students WHERE cunyID = ?");
    $stmt->bind_param("i", $cunyID);
    $stmt->execute();
    $stmt->bind_result($firstName, $lastName);
    $stmt->fetch();
    $stmt->close();

    if ($firstName && $lastName) {
        echo "<h3>{$firstName} {$lastName} (CUNY ID: {$cunyID})</h3>";

        // Retrieve order history for the student
        $stmt = $conn->prepare("SELECT orderNo, orderDate, semester, nonPerishable, perishable, toiletry, oil, prePackedBag, adults18_64, childrenUnder18, adults65Plus, staff FROM orders WHERE cunyID = ?");
        $stmt->bind_param("i", $cunyID);
        $stmt->execute();
        $stmt->bind_result($orderNo, $orderDate, $semester, $nonPerishable, $perishable, $toiletry, $oil, $prePackedBag, $adults18_64, $childrenUnder18, $adults65Plus, $staff);

        echo "<table class='tablestyles'><tr><th>Order No</th><th>Order Date</th><th>Semester</th><th>Non-Perishable</th><th>Perishable</th><th>Toiletry</th><th>Oil</th><th>Pre-Packed Bag</th><th>Adults 18-64</th><th>Children Under 18</th><th>Adults 65+</th><th>Staff</th></tr>";
        while ($stmt->fetch()) {
            echo "<tr>
                    <td>{$orderNo}</td>
                    <td>{$orderDate}</td>
                    <td>{$semester}</td>
                    <td>{$nonPerishable}</td>
                    <td>{$perishable}</td>
                    <td>{$toiletry}</td>
                    <td>{$oil}</td>
                    <td>{$prePackedBag}</td>
                    <td>{$adults18_64}</td>
                    <td>{$childrenUnder18}</td>
                    <td>{$adults65Plus}</td>
                    <td>{$staff}</td>
                  </tr>";
        }
        echo "</table>";
        echo "<br><br><a href='displayStudents.php' class='buttonBack'>Back to student list</a>";
        $stmt->close();
    } else {
        echo "No student found with the given CUNY ID.";
    }
} else {
    echo "No CUNY ID provided.";
}

$conn->close();
?>
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
    <script src="layouts/resize.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
