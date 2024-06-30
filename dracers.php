<?php
// Function to create a custom salt
function create_salt($length = 22) {
    return bin2hex(random_bytes($length));
}

// Generate a custom salt
$salt = create_salt();

// Create a hash using the salt and the password
$password = 'password123!';
$hash = hash('sha256', $salt . $password);

echo "Salt: $salt<br>";
echo "Hash: $hash";
?>
