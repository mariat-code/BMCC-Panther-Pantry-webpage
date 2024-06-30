<?php
include 'auth.php';
include 'dbconnect.php';
include 'accesibles.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body class="editstudent-background">
<?php include 'navbar.php'; ?>
<br>
    <div class="page-content" id="dynamicContainer">
<h1>Edit Student Information</h1>
<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update student information
    $cunyID = get_safe('cunyID');
    $firstName = get_safe('firstName');
    $lastName = get_safe('lastName');
    $phoneNo = get_safe('phoneNo');
    $Email = get_safe('Email');

    $stmt = $conn->prepare("UPDATE students SET firstName = ?, lastName = ?, phoneNo = ?, Email = ? WHERE cunyID = ?");
    $stmt->bind_param("ssssi", $firstName, $lastName, $phoneNo, $Email, $cunyID);

    if ($stmt->execute()) {
        echo "Student information updated successfully.";
    } else {
        echo "Error updating student information: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
    echo "<br><br><a href='displayStudents.php' class='buttonBack'>Back to student list</a>";
} else {
    // Display the edit form
    if (isset($_GET['cunyID'])) {
        $cunyID = get_safe('cunyID');

        $stmt = $conn->prepare("SELECT firstName, lastName, phoneNo, Email FROM students WHERE cunyID = ?");
        $stmt->bind_param("i", $cunyID);
        $stmt->execute();
        $stmt->bind_result($firstName, $lastName, $phoneNo, $Email);
        $stmt->fetch();
        $stmt->close();

        if ($firstName && $lastName) {
?>
            <form action="editStudents.php" method="post">
                <input type="hidden" name="cunyID" value="<?php echo htmlspecialchars($cunyID); ?>">
                <table>
                    <tr>
                        <th>CUNY ID</th>
                        <td><?php echo htmlspecialchars($cunyID); ?></td>
                    </tr>
                    <tr>
                        <th>First Name</th>
                        <td><input type="text" name="firstName" value="<?php echo htmlspecialchars($firstName); ?>" required></td>
                    </tr>
                    <tr>
                        <th>Last Name</th>
                        <td><input type="text" name="lastName" value="<?php echo htmlspecialchars($lastName); ?>" required></td>
                    </tr>
                    <tr>
                        <th>Phone Number</th>
                        <td><input type="text" name="phoneNo" value="<?php echo htmlspecialchars($phoneNo); ?>" required></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><input type="email" name="Email" value="<?php echo htmlspecialchars($Email); ?>" required></td>
                    </tr>
                </table>
                <br>
                <input type="submit" value="Update" class="button">
            </form>
<?php
        } else {
            echo "No student found with the given CUNY ID.";
            echo "<br><br><a href='displayStudents.php' class='button'>Back to student list</a>";
        }
    } else {
        echo "No CUNY ID provided.";
        echo "<br><br><a href='displayStudents.php' class='button'>Back to student list</a>";
    }

    $conn->close();
}
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