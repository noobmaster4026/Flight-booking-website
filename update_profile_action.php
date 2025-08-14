<?php
session_start();
require_once "config.php";

// Ensure user is logged in
if (!isset($_SESSION['user-name'])) {
    header("Location: index.php");
    exit();
}

$oldUsername = $_SESSION['user-name'];

// ✅ If "Delete Profile" was clicked
if (isset($_POST['delete'])) {
    $stmt = $conn->prepare("DELETE FROM Passengers WHERE User_name = ?");
    $stmt->bind_param("s", $oldUsername);

    if ($stmt->execute()) {
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit();
    } else {
        echo "Deletion failed: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
    exit();
}

// ✅ If "Update Profile" was clicked
$firstName       = $_POST['first-name'];
$lastName        = $_POST['last-name'];
$houseNumber     = (int)$_POST['house-number'];
$city            = $_POST['city'];
$postCode        = (int)$_POST['post-code'];
$country         = $_POST['country'];
$passportNumber  = $_POST['passport-number'];
$email           = $_POST['email'];
$passwordHash    = password_hash($_POST['password'], PASSWORD_BCRYPT);
$dob             = $_POST['Date_of_birth'];
$gender          = $_POST['gender'];

$stmt = $conn->prepare("
    UPDATE Passengers
       SET First_name      = ?,
           Last_name       = ?,
           House_number    = ?,
           City            = ?,
           Post_code       = ?,
           Country         = ?,
           Passport_number = ?,
           Email           = ?,
           Password        = ?,
           Date_of_birth   = ?,
           Gender          = ?
     WHERE User_name       = ?
");

$stmt->bind_param(
    "ssississssss", 
    $firstName,
    $lastName,
    $houseNumber,
    $city,
    $postCode,
    $country,
    $passportNumber,
    $email,
    $passwordHash,
    $dob,
    $gender,
    $oldUsername
);

if ($stmt->execute()) {
    // Optional: Update session info
    $_SESSION['first-name'] = $firstName;
    $_SESSION['last-name']  = $lastName;
    $_SESSION['house-number'] = $houseNumber;
    $_SESSION['city'] = $city;
    $_SESSION['post-code'] = $postCode;
    $_SESSION['country'] = $country;
    $_SESSION['passport-number'] = $passportNumber;
    $_SESSION['email'] = $email;
    $_SESSION['Date_of_birth'] = $dob;
    $_SESSION['gender'] = $gender;

    header("Location: Home.php");
    exit();
} else {
    echo "Update failed: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
