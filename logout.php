<?php
session_start();

// Only unset the user session variable
unset($_SESSION['user']);

// Redirect to login page
header("Location: index.php");
exit();
?>