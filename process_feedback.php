<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Form</title>
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
    <h1>Feedback Form</h1>
    
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $experience = get_safe('experience');
        $changes = get_safe('changes');
        $recommend = get_safe('recommend');
        $favorite = get_safe('favorite');
        $improvements = get_safe('improvements');

        $stmt = $conn->prepare("INSERT INTO feedback (experience, changes, recommend, favorite, improvements) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $experience, $changes, $recommend, $favorite, $improvements);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success' role='alert'>Feedback submitted successfully!</div>";
        } else {
            echo "<div class='alert alert-danger' role='alert'>Error submitting feedback: " . $conn->error . "</div>";
        }

        $stmt->close();
        $conn->close();
    }
    ?>

    <form action="" method="POST">
        <label for="experience">How did you like your experience?</label><br>
        <textarea id="experience" name="experience" rows="3" cols="50" required></textarea><br><br>

        <label for="changes">Do you want anything to be added or changed?</label><br>
        <textarea id="changes" name="changes" rows="3" cols="50" required></textarea><br><br>

        <label for="recommend">Would you recommend us to others?</label><br>
        <textarea id="recommend" name="recommend" rows="3" cols="50" required></textarea><br><br>

        <label for="favorite">What was your favorite part?</label><br>
        <textarea id="favorite" name="favorite" rows="3" cols="50" required></textarea><br><br>

        <label for="improvements">Any suggestions for improvement?</label><br>
        <textarea id="improvements" name="improvements" rows="3" cols="50" required></textarea><br><br>

        <input type="submit" value="Submit Feedback">
    </form>

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
