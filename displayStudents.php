<?php
include 'auth.php';
include 'dbconnect.php';
include 'accesibles.php';

function displayStudents() {
    global $conn;
    $sql = "SELECT cunyID, firstName, lastName, phoneNo, Email FROM students";
    $result = $conn->query($sql);

    if(!$result){
        die('Could not get data: ' . mysqli_error($conn));
    }

    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {    
            echo "<tr>
                    <td>{$row['cunyID']}</td><td>{$row['firstName']}</td><td>{$row['lastName']}</td><td>{$row['phoneNo']}</td><td>{$row['Email']}</td>
                    <td class='studentTableActions'>
                        <a href='studentInfo.php?cunyID={$row['cunyID']}' class='button button-info'><i class='fas fa-info-circle'></i>Info</a>
                        <a href='editStudents.php?cunyID={$row['cunyID']}' class='button button-edit'><i class='fas fa-edit'></i>Edit</a>
                        <a href='?deleteCunyID={$row['cunyID']}' class='button button-delete' onclick='return confirm(\"Are you sure you want to delete this student?\");'><i class='fas fa-trash-alt'></i>Delete</a>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No results found</td></tr>";
    }
}

function deleteStudent($cunyID) {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM students WHERE cunyID = ?");
    $stmt->bind_param("i", $cunyID);
    
    if ($stmt->execute()) {
        echo "Student deleted successfully.";
    } else {
        echo "Error deleting student: " . $conn->error;
    }
    
    $stmt->close();
}

if (isset($_GET['deleteCunyID'])) {
    deleteStudent($_GET['deleteCunyID']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Student</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="display-background">
    <?php include 'navbar.php'; ?>
    <br>
    <div class="page-content" id="dynamicContainer">

        <h1>Student List</h1>

        <form action="displayStudents.php" method="get">
            <label for="cunyID">Search Student:</label>
            <input type="text" id="searchBar" name="searchBar" required>
        </form>
        <br>

        <div class="table-container">
            <table id="studentTable" class="tablestyles">
                <thead>
                    <tr>
                        <th>cuny ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Phone Number</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="studentTableBody">
                    <?php displayStudents(); ?>
                </tbody>
            </table>
        </div>
    </div>
<footer class="footer">
    <div class="footer-content">
        <img src="misc/bmcclogo.png" alt="BMCC Logo" class="footer-logo left">
        <p>Borough of Manhattan Community College<br>
        The City University of New York<br>
        199 Chambers Street, New York, NY, 10007<br>
        Phone: (212) 220-8000</p>
        <img src="misc/cunylogo.png" alt="CUNY Logo" class="footer-logo right">
    </div>
        <br>
</footer>
    <script src="layouts/resize.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="searchBarFunctionality.js"></script>
</body>
</html>
