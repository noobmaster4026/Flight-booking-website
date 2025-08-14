<?php
require_once "config.php";
if (!isset($_SESSION["user-name"])){
    header("Location: index.php");
    exit();
}



$from = mysqli_real_escape_string($conn, $_GET['from'] ?? '');
$to = mysqli_real_escape_string($conn, $_GET['to'] ?? '');
$date = $_GET['date'] ?? '';

$result = null;

if (!empty($from) && !empty($to)) {
    $sql = "SELECT * FROM Flights WHERE Starting_location LIKE '%$from%' AND Ending_location LIKE '%$to%'";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airline Ticket</title>
    <link rel="stylesheet" href="styles2.css">
</head>
<body>
<div class="logo">
        <img decoding="async" src="images\logo.png">
    </div>
    <nav>
        <ul class="sidebar">
            <li onclick=hidesidebar()><a href="#"><svg xmlns="http://www.w3.org/2000/svg" height="26px" viewBox="0 -960 960 960" width="26px" fill="#e3e3e3"><path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/></svg></a></li>
            <li onclick="window.location.href='profile_update.php'"><a href="#">Profile</a></li>
            <li onclick="window.location.href='payment.php'"><a href="#">Payment</a></li>
            <li><a href="#">Contact Us</a></li>
            <li onclick="window.location.href='logout.php'"><a href="#">Logout</a></li>
        </ul>       
        <ul>
            <li><a href="#"></a></li>
            <li class = "hideOnMobile" onclick="window.location.href='profile_update.php'"><a href="#">Profile</a></li>
            <li class = "hideOnMobile" onclick="window.location.href='payment.php'"><a href="#">Payment</a></li>
            <li class="hideOnMobile dropdown">
                <a href="#">Contact Us ▼</a>
                <ul class="dropdown-menu">
                    <li><a href="">Email Us</a></li>
                    <li><a href="">Call Support</a></li>
                    
                </ul>
            </li>

            <li class = "hideOnMobile" onclick="window.location.href='logout.php'"><a href="#">Logout</a></li>
            <li class = "menu-button" onclick=showsidebar()><a href="#"><svg xmlns="http://www.w3.org/2000/svg" height="26px" viewBox="0 -960 960 960" width="26px" fill="#e3e3e3"><path d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z"/></svg></a></li>
        </ul>
    </nav>

    <div class="background-slideshow">
        <div class="bg-slide bg1"></div>
        <div class="bg-slide bg2"></div>
        <div class="bg-slide bg3"></div>
        <div class="bg-slide bg4"></div>
        <div class="bg-slide bg5"></div>
    </div>

    <div class="booking-section" id="flight-sections">
        <form method="GET">
            <label for="from">Fly From*<img src="images\download (1).png" height="30" alt=""></label>
            <input type="text" name="from" id="from" placeholder="From" required><br><br>

            <label for="to">Fly To*<img src="images\download (1).png" height="30" alt=""></label>
            <input type="text" name="to" id="to" placeholder="Destination" required><br><br>

            <label for="date">Departing*<img src="images\download.png" height="30" alt=""></label>
            <input type="date" name="date" id="date" required><br><br>

            <label for="return">Return*<img src="images\download.png" height="30" alt=""></label>
            <input type="date" name="return" id="return"><br><br>
            <button type="submit">Search Flights</button>
        </form>
    </div>

    <?php if (!empty($from) && !empty($to)): ?>
        <h2 style="text-align: center;">Available Flights from <?= htmlspecialchars($from) ?> to <?= htmlspecialchars($to) ?></h2>
        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Flight No</th>
                    <th>Flight Name</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Arrival Time</th>
                    <th>Departure Time</th>
                    <th>Seats Available</th>
                    <th>Airline</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['Flight_num']) ?></td>
                        <td><?= htmlspecialchars($row['Flight_name']) ?></td>
                        <td><?= htmlspecialchars($row['Starting_location']) ?></td>
                        <td><?= htmlspecialchars($row['Ending_location']) ?></td>
                        <td><?= htmlspecialchars($row['Arrival_time']) ?></td>
                        <td><?= htmlspecialchars($row['Departure_time']) ?></td>
                        <td><?= htmlspecialchars($row['Seat_availability']) ?></td>
                        <td><?= htmlspecialchars($row['Airline_name']) ?></td>
                        <td>
                            <div class="toggle-group">
                                <a class="class-button" href="ticketBooking.php?flight_num=<?= $row['Flight_num'] ?>&from=<?= urlencode($row['Starting_location']) ?>&to=<?= urlencode($row['Ending_location']) ?>">Book</a>

                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p style="text-align: center;">No flights found matching your search.</p>
        <?php endif; ?>
    <?php endif; ?>

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
