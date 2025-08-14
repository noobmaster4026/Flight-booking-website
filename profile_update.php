<?php
require_once "config.php";

if (!isset($_SESSION["user-name"])){
    header("Location: index.php");
    exit();
}

$username = $_SESSION['user-name'];

// Fetch current user info
$stmt = $conn->prepare("SELECT * FROM Passengers WHERE User_name = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Update Profile</title>
    <link rel="stylesheet" href="profile_style.css">
</head>
<body>
<div class="container">
  <div class="form-box" id="update-form">
    <h2>Update Your Profile</h2>
    <form action="update_profile_action.php" method="POST">
      <div class="form-grid">
        <input type="text"   name="first-name"      placeholder="First Name"      value="<?= htmlspecialchars($user['First_name']) ?>"       required>
        <input type="text"   name="last-name"       placeholder="Last Name"       value="<?= htmlspecialchars($user['Last_name']) ?>"        required>
        <input type="number" name="house-number"    placeholder="House Number"    value="<?= htmlspecialchars($user['House_number']) ?>"     required>
        <input type="text"   name="city"            placeholder="City"            value="<?= htmlspecialchars($user['City']) ?>"             required>
        <input type="number" name="post-code"       placeholder="Post Code"       value="<?= htmlspecialchars($user['Post_code']) ?>"        required>
        <input type="text"   name="country"         placeholder="Country"         value="<?= htmlspecialchars($user['Country']) ?>"          required>
        <input type="number" name="passport-number" placeholder="Passport Number" value="<?= htmlspecialchars($user['Passport_number']) ?>"  required>
        <input type="email"  name="email"           placeholder="Email"           value="<?= htmlspecialchars($user['Email']) ?>"            required>
        <input type="password" name="password"      placeholder="Password"                                                                           required>
        <input type="date"   name="Date_of_birth"                                 value="<?= htmlspecialchars($user['Date_of_birth']) ?>"    required>
        <select name="gender" required>
          <option value="" disabled selected hidden <?= empty($user['Gender']) ? 'selected' : '' ?>>Gender</option>
          <option value="male" <?= $user['Gender'] == 'male' ? 'selected' : '' ?>>Male</option>
          <option value="female" <?= $user['Gender'] == 'female' ? 'selected' : '' ?>>Female</option>
          <option value="other" <?= $user['Gender'] == 'other' ? 'selected' : '' ?>>Other</option>
        </select>
      </div>
      <button type="submit" name="update">Update Profile</button>
      <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete your account?')">Delete Profile</button>
    </form>
  </div>
</div>

</body>
</html>
