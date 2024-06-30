<?php
$is_admin = false;

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['username'])) {
    if ($_SESSION['username'] === 'admin') {
        $is_admin = true;
    }
}
?>
<div class="header">
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <img src="misc/logo.png" alt="Logo" class="logo">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="foodOrder.php">Food Request</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="menu.php">Menu</a>
                    </li>          
                    <li class="nav-item">
                        <a class="nav-link" href="recipes.php">Recipes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="process_feedback.php">Provide Feedback</a>
                    </li>
                    <?php if ($is_admin): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Students
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="displayStudents.php">Display All</a>
                            <a class="dropdown-item" href="insertStudents.php">Add New</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="feedbackreview.php">Review Feedback</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="report.php">Generate Report</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="inventory.php">Inventory Stock</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <?php if ($is_admin): ?>
                        <a class="nav-link" href="logout.php">Logout</a>
                        <?php else: ?>
                        <a class="nav-link" href="login.php">Login</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>


<script>
  window.watsonAssistantChatOptions = {
    integrationID: "907e4754-0d59-43b7-8b80-1be65fb5d73d", // The ID of this integration.
    region: "us-east", // The region your integration is hosted in.
    serviceInstanceID: "a9ae61df-aa31-4fce-b706-e7bb83af0c31", // The ID of your service instance.
    onLoad: async (instance) => { await instance.render(); }
  };
  setTimeout(function(){
    const t=document.createElement('script');
    t.src="https://web-chat.global.assistant.watson.appdomain.cloud/versions/" + (window.watsonAssistantChatOptions.clientVersion || 'latest') + "/WatsonAssistantChatEntry.js";
    document.head.appendChild(t);
  });
</script>