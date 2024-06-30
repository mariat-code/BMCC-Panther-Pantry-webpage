<?php
include 'auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Table</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body class="feedback-background">
    <?php 
    include 'navbar.php';
    include 'dbconnect.php';
    include 'accesibles.php';
    ?>

    <br>
    <div class="page-content" id="dynamicContainer">
        <h1>Feedback Table</h1>
        <div class="feedback-table-container">
            <table class="table table-bordered tablestyles">
                <thead>
                    <tr>
                        <th>Experience</th>
                        <th>Changes</th>
                        <th>Recommend</th>
                        <th>Favorite</th>
                        <th>Improvements</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT experience, changes, recommend, favorite, improvements FROM feedback";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td><textarea rows='3' readonly>{$row['experience']}</textarea></td>
                                    <td><textarea rows='3' readonly>{$row['changes']}</textarea></td>
                                    <td><textarea rows='3' readonly>{$row['recommend']}</textarea></td>
                                    <td><textarea rows='3' readonly>{$row['favorite']}</textarea></td>
                                    <td><textarea rows='3' readonly>{$row['improvements']}</textarea></td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No feedback available</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
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
</body>
</html>
