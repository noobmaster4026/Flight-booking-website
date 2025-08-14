<?php
session_start();
require_once "config.php";

if (isset($_POST['register'])) {
    $f_name = $_POST['first-name'];
    $l_name = $_POST['last-name'];
    $user_name = $_POST['user-name'];
    $house_number = $_POST['house-number'];
    $city = $_POST['city'];
    $post_code = $_POST['post-code'];
    $country = $_POST['country'];
    $passport_number = $_POST['passport-number'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $Date_of_birth = $_POST['Date_of_birth'];
    $gender = $_POST['gender'];

    $stmt = $conn->prepare("SELECT User_name FROM passengers WHERE User_name = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION["register_error"] = "Username already registered";
        $_SESSION["active_form"] = "register";
    } else {
        $stmt = $conn->prepare("INSERT INTO passengers 
            (User_name, First_name, Last_name, House_number, City, Post_code, Country, Passport_number, Email, Password, Date_of_birth, Gender) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssissssssss", 
            $user_name, $f_name, $l_name, $house_number, $city, $post_code, $country, $passport_number, $email, $password, $Date_of_birth, $gender
        );

        if ($stmt->execute()) {
            $_SESSION["register_success"] = "Registration successful! Please log in.";
        } else {
            $_SESSION["register_error"] = "Registration failed: " . $stmt->error;
        }
    }

    $stmt->close();
    header("Location: index.php");
    exit();
}

if (isset($_POST['login'])) {
    $user_name = $_POST['user-name'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM passengers WHERE user_name = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['Password'])) {
            $_SESSION['first-name'] = $user['First_name'];
            $_SESSION['last-name'] = $user['Last_name'];
            $_SESSION['user-name'] = $user['User_name'];
            $_SESSION['house-number'] = $user['House_number'];
            $_SESSION['city'] = $user['City'];
            $_SESSION['post-code'] = $user['Post_code'];
            $_SESSION['country'] = $user['Country'];
            $_SESSION['passport-number'] = $user['Passport_number'];
            $_SESSION['email'] = $user['Email'];
            $_SESSION['Date_of_birth'] = $user['Date_of_birth'];
            $_SESSION['gender'] = $user['Gender'];

            header('Location: Home.php');
            exit();
        } else {
            $_SESSION["login_error"] = 'Incorrect username or password';
            $_SESSION['active_form'] = 'login';
        }
    } else {
        $_SESSION["login_error"] = 'Incorrect username or password';
        $_SESSION['active_form'] = 'login';
    }

    $stmt->close();
    header('Location: index.php');
    exit();
}
?>
