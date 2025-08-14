<?php

session_start();
if (!isset($_SESSION["user-name"])){
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home & Navbar</title>
    <link rel="stylesheet" href="stylish.css">
</head>
<body>
    <div>
    <div class="logo">
        <img decoding="async" src="images\logo.png">
    </div>
    <nav>
        <ul class="sidebar">
            <li onclick=hidesidebar()><a href="#"><svg xmlns="http://www.w3.org/2000/svg" height="26px" viewBox="0 -960 960 960" width="26px" fill="#e3e3e3"><path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/></svg></a></li>
            <li onclick="window.location.href='merged.php'"><a href="#">Flights</a></li>
            <li onclick="window.location.href='payment.php'"><a href="#">Payment</a></li>
            <li onclick="window.location.href='Booked.php'"><a href="#">Booked</a></li>
            <li onclick="window.location.href='profile_update.php'"><a href="#">Profile</a></li>
            <li onclick="window.location.href='logout.php'"><a href="#">Logout</a></li>
        </ul>       
        <ul>
            <li><a href="#"></a></li>
            <li class = "hideOnMobile" onclick="window.location.href='merged.php'"><a href="#">Flights</a></li>
            <li class = "hideOnMobile" onclick="window.location.href='payment.php'"><a href="#">Payment</a></li>
            <li class = "hideOnMobile" onclick="window.location.href='Booked.php'"><a href="#">Booked</a></li>
            <li class = "hideOnMobile" onclick="window.location.href='profile_update.php'"><a href="#">Profile</a></li>
            <li class = "hideOnMobile" onclick="window.location.href='logout.php'"><a href="#">Logout</a></li>
            <li class = "menu-button" onclick=showsidebar()><a href="#"><svg xmlns="http://www.w3.org/2000/svg" height="26px" viewBox="0 -960 960 960" width="26px" fill="#e3e3e3"><path d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z"/></svg></a></li>
        </ul>
    </nav>
    <div class="home-text">
        <h1>
            Welcome
        </h1>
        <p>
        This is the Airline Ticket home page of 
        <span><?= $_SESSION["first-name"] ?> <?= $_SESSION["last-name"] ?></span>
        </p>
    </div>
    </div>
    <script>
        function showsidebar(){
            const sidebar = document.querySelector(".sidebar")
            sidebar.style.display = "flex"
        }
        function hidesidebar(){
            const sidebar = document.querySelector(".sidebar")
            sidebar.style.display = "none"
        }
    </script>
    
</body>
</html>