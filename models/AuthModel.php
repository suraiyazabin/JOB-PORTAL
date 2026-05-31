<?php
function getUserByEmail($conn, $email) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email=? AND is_active=1");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

function registerUser($conn, $name, $email, $password, $phone, $role) {
    $chk = mysqli_prepare($conn, "SELECT id FROM users WHERE email=?");
    mysqli_stmt_bind_param($chk, 's', $email);
    mysqli_stmt_execute($chk);
    if (mysqli_fetch_assoc(mysqli_stmt_get_result($chk))) return 'exists';
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password_hash, phone, role) VALUES (?,?,?,?,?)");
    mysqli_stmt_bind_param($stmt, 'sssss', $name, $email, $hash, $phone, $role);
    if (mysqli_stmt_execute($stmt)) {
        $id = mysqli_insert_id($conn);
        if ($role === 'seeker') {
            $p = mysqli_prepare($conn, "INSERT INTO seeker_profiles (user_id) VALUES (?)");
            mysqli_stmt_bind_param($p, 'i', $id);
            mysqli_stmt_execute($p);
        }
        return $id;
    }
    return false;
}
