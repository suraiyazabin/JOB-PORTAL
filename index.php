<?php

// Define base URL so all controllers can build correct paths.
// Change this if your XAMPP folder name differs.
define('BASE_URL', '/job-portal/');

header('Location: ' . BASE_URL . 'controller/AuthController.php');
exit;