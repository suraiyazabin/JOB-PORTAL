<?php
function connect() {
    $conn = mysqli_connect("localhost", "root", "", "job_portal");
    if (!$conn) die("Connection failed: " . mysqli_connect_error());
    return $conn;
}