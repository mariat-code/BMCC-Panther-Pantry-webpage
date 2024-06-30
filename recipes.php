<?php
include 'dbconnect.php'; // Assuming you want to connect to a database
include 'accesibles.php';

session_start();

$errorFlag = false; // Initialize error flag
$errorMessages = ""; // Initialize error messages variable
$successFlag = false; // Initialize success flag
$successMessages = ""; // Initialize success messages variable

// Fetch recipes from the Edamam API based on search query
function fetchRecipes($query = '') {
    $appID = '0102423b'; // Replace with your actual App ID
    $appKey = 'b7e632d934f52588cd7000b54ef09cc0'; // Replace with your actual App Key
    $url = "https://api.edamam.com/api/recipes/v2?type=public&q=" . urlencode($query) . "&app_id=$appID&app_key=$appKey";

    $response = @file_get_contents($url);
    if ($response === FALSE) {
        return [];
    }

    $data = json_decode($response, true);
    return $data['hits'] ?? [];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $searchQuery = get_safe('searchQuery');
    $recipes = fetchRecipes($searchQuery);
} else {
    $recipes = fetchRecipes();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panther Pantry Recipes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body class="recipes-background">
    <?php include 'navbar.php'; ?>
    <div class="container main-content">
        <div class="page-content" id="dynamicContainer">
            <h1 class="no-print">Search Recipes</h1>

            <!-- Error Container -->
            <?php if ($errorFlag): ?>
                <div id="error-container" class="error-container">
                    <?php echo $errorMessages; ?>
                </div>
            <?php endif; ?>

            <!-- Success Container -->
            <?php if ($successFlag): ?>
                <div id="success-container" class="success-message">
                    <?php echo $successMessages; ?>
                </div>
            <?php endif; ?>

            <div class="formContainer no-print">
                <div class="mainSection">
                    <form action="recipes.php" method="post" class="centerForm no-Print mainForm">
                        <label for="searchQuery">Search for Recipes:</label>
                        <input type="text" id="searchQuery" name="searchQuery" class="form-control mb-2 mr-sm-2" placeholder="Enter recipe name or ingredient" required>
                        <div class="buttonContainer">
                            <button type="submit" name="search" class="btn btn-primary mb-2">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (!empty($recipes)): ?>
                <h2>Recipe Results</h2>
                <div class="recipe-container">
                    <table class="tablestyles">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Source</th>
                                <th>Ingredients</th>
                                <th>Image</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recipes as $hit): ?>
                                <?php $recipe = $hit['recipe']; ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($recipe['label']); ?></td>
                                    <td><?php echo htmlspecialchars($recipe['source']); ?></td>
                                    <td>
                                        <ul>
                                            <?php foreach ($recipe['ingredientLines'] as $ingredient): ?>
                                                <li><?php echo htmlspecialchars($ingredient); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>
                                    <td>
                                        <?php if (!empty($recipe['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($recipe['image']); ?>" alt="<?php echo htmlspecialchars($recipe['label']); ?>" style="max-width: 100px;">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

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
