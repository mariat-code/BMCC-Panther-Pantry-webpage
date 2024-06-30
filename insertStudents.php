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
    <title>Insert Students</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body class="insert-background">
    <?php include 'navbar.php'; ?>
    <br>
        <div class="page-content" id="dynamicContainer">
    <h1>Insert Students</h1>
<form action="insertStudents.php" method="post">
<table>
    <tr><td><label for="cunyID">CUNY ID:</label></td><td> <input type="text" id="cunyID" name="cunyID" required></td></tr>
    <tr><td><label for="firstName">First Name:</label></td><td> <input type="text" id="firstName" name="firstName" required></td></tr>
    <tr><td><label for="lastName">Last Name:</label></td><td> <input type="text" id="lastName" name="lastName" required></td></tr>    
    <tr><td><label for="phoneNo">Phone Number:</label></td><td> <input type="text" id="phoneNo" name="phoneNo"></td></tr>
    <tr><td><label for="Email">E-mail:</label></td><td> <input type="email" id="Email" name="Email"></td></tr>
    <tr><td># of Adults in Household Aged 18-64:</td><td> <input type="number" name="adults18_64" value="0" required></td></tr>
    <tr><td># of Children in Household Under Age 18:</td><td> <input type="number" name="childrenUnder18" value="0" required></td></tr>
    <tr><td># of Adults in Household Aged 65+:</td><td> <input type="number" name="adults65Plus" value="0" required></td></tr>
</table><br>
    <input type="submit" value="Submit">
</form>

<br><br>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // collect value of input field
  $cunyID = get_safe('cunyID');
  $firstName = get_safe('firstName');
  $lastName = get_safe('lastName');
  $phoneNo = get_safe('phoneNo');
  $Email = get_safe('Email');
  $adults18_64 = get_safe('adults18_64');
  $childrenUnder18 = get_safe('childrenUnder18');
  $adults65Plus = get_safe('adults65Plus');
  $registrationDate = date('Y-m-d'); // Get the current date

  if (empty($cunyID) || empty($firstName) || empty($lastName)) {
    echo "Please fill out all required fields";
  } else {
    // Check connection
    if (!$conn) {
      die('Could not connect: ' . mysqli_error($conn));
    }

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO students (cunyID, firstName, lastName, phoneNo, Email, adults18_64, childrenUnder18, adults65Plus, registrationDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issssiii", $cunyID, $firstName, $lastName, $phoneNo, $Email, $adults18_64, $childrenUnder18, $adults65Plus,);

    // Execute the statement
    if ($stmt->execute()) {
      echo "New record inserted successfully";
    } else {
      echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
  }
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