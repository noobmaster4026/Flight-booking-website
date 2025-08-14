<?php
session_start();

$errors = [
    "login" => $_SESSION['login_error'] ?? '',
    "register" => $_SESSION['register_error'] ?? ''
];
$activeForm = $_SESSION["active_form"] ?? "login";

unset($_SESSION['login_error'], $_SESSION['register_error'], $_SESSION['active_form']);

function showError($error) {
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

function isActiveForm($formName, $activeForm) {
    return $formName === $activeForm ? 'active' : '';
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Air ticket register and Login</title>
    <link rel="stylesheet" href="Style.css">
</head>
<body>
    <div class="container">
        <div class="form-box <?= isActiveForm("login", $activeForm); ?>" id="login-form">
            <form action="login_register.php" method="post">
                <h2>Login</h2>
                <?= showError($errors['login']);?>
                <input type="username" name="user-name" placeholder="User Name" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="Submit" name="login">Login</button>
                <p>Don't have an account? <a href="#" onclick="showForm('register-form')">Register</a></p>
            </form>
        </div>
        <div class="form-box <?= isActiveForm("register", $activeForm); ?>" id="register-form">
            <form action="login_register.php" method="post">
                <h2>Register</h2>
                <?= showError($errors['register']);?>
                <div class="form-grid">
                    <input type="text" name="first-name" placeholder="First Name" required>
                    <input type="text" name="last-name" placeholder="Last Name" required>
                    <input type="text" name="user-name" placeholder="Username" required>
                    <input type="number" name="house-number" placeholder="House number" required>
                    <input type="text" name="city" placeholder="City" required>
                    <input type="number" name="post-code" placeholder="Post code" required >
                    <input type="text" name="country" placeholder="Country" required>
                    <input type="number" name="passport-number" placeholder="Passport number" required >
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <input type="date" name="Date_of_birth" required>
                    <select name="gender" required>
                        <option value="" disabled selected hidden>Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <button type="Submit" name="register">Register</button>
                <p>Already have an account? <a href="#" onclick="showForm('login-form')">Login</a></p>
            </form>
        </div>                 
    </div>
    <script>
        function showForm(formID) {
            document.querySelectorAll(".form-box").forEach(form => form.classList.remove("active"));
            document.getElementById(formID).classList.add("active");
            }
    </script>
</body>



</html>