<?php
// Admin credentials
$admin_username = 'admin';
$admin_salt = '72d03171cb0eeeb63cf05870415764e45cbe0b2df3f5';
$admin_password_hash = '4303b4c2fba88c9fb42311f1837d18dcace58e09c24e28e665edd9efa858250d';

// Function to check admin credentials
function check_admin_credentials($username, $password) {
    global $admin_username, $admin_salt, $admin_password_hash;
    $password_hash = hash('sha256', $admin_salt . $password);
    return $username === $admin_username && $password_hash === $admin_password_hash;
}
?>
