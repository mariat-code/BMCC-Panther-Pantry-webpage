<?php
session_start();
include 'petrie.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (check_admin_credentials($username, $password)) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit();
    } else {
        echo "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body class="login-background">
    <?php include 'navbar.php'; ?>
    <br>
        <div class="container main-content">
            <div class="page-content" id="dynamicContainer">
            <h1>Admin Login</h1>
            <form action="login.php" method="post">
                <label for="username"><strong>Username:</strong></label><br>
                <input type="text" id="username" name="username" required><br><br>
                <label for="password"><strong>Password:</strong></label><br>
                <input type="password" id="password" name="password" required><br><br>
                <input type="submit" value="Login">
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
        <script src="layouts/resize.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
