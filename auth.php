<?php
session_start();
include 'petrie.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$is_admin = $_SESSION['username'] === $admin_username;
?>
