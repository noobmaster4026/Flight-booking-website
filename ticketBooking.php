<?php
session_start();
if (!isset($_SESSION["user-name"])) {
    header("Location: index.php");
    exit();
}

// Get data from URL
$flightNum = $_GET['flight_num'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

// Fetch weather data before proceeding
$shouldCancel = false;
$reasons = [];
$weatherStatusMessage = '';

// Check if weather conditions indicate a problem
if ($flightNum && $from && $to) {
    $conn = new mysqli("localhost", "root", "", "FlightBookingDB");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $dateToday = date("Y-m-d");

    // Fetch weather data for this flight, today, and from the source location
    $stmt = $conn->prepare(
        "SELECT Wind_speed, Temperature, Visibility, Precipitation 
         FROM Weather 
         WHERE Location = ? AND Date = ? AND Flight_num = ?"
    );
    $stmt->bind_param("sss", $from, $dateToday, $flightNum);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $windSpeed = floatval($row["Wind_speed"]);
        $temperature = floatval($row["Temperature"]);
        $visibility = floatval($row["Visibility"]);
        $precipitation = strtolower($row["Precipitation"]); // Normalize casing

        // Evaluate weather conditions
        if ($windSpeed > 120) {
            $shouldCancel = true;
            $reasons[] = "Wind speed is too high ({$windSpeed} km/h)";
        }

        if ($visibility < 10) {
            $shouldCancel = true;
            $reasons[] = "Visibility is too low ({$visibility})";
        }

        if ($precipitation === 'high') {
            $shouldCancel = true;
            $reasons[] = "Heavy precipitation detected";
        }

        if ($precipitation === 'moderate' && $temperature > 30) {
            $shouldCancel = true;
            $reasons[] = "Moderate precipitation with high temperature ({$temperature}°C)";
        }
    } else {
        $shouldCancel = true;
        $reasons[] = "Weather data not available for this flight";
    }
    $stmt->close();

    if ($shouldCancel) {
        $weatherStatusMessage = "<div style='text-align: center; margin-top: 20px;'><h2 style='color:red;'>❌ Flight Cancelled Due to Bad Weather</h2>
                                  <p style='color:red;'><strong>Reason(s):</strong></p><ul></div>";
        foreach ($reasons as $reason) {
            $weatherStatusMessage .= "<div style='text-align: center; margin-top: 20px;'><li style='color:red;'>{$reason}</li></div>";
        }
        $weatherStatusMessage .= "<div style='text-align: center; margin-top: 20px;'></ul>
                                  <p style='color:red;'>Tickets have been cancelled and cannot proceed to payment.</p></div>";
    } else {
        $weatherStatusMessage = "<div style='text-align: center; margin-top: 20px;'><h2 style='color:green;'>✅ Weather Conditions Are Clear for Your Flight</h2>
                                  <p style='color:green;'>You may now proceed with your payment.</p></div>";
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ticket Booking</title>
    <link rel="stylesheet" href="ticketBooking.css">
</head>
<body>

<div class="logo">
    <img decoding="async" src="images/logo.png">
</div>

<nav>
    <ul class="sidebar">
        <li onclick=hidesidebar()><a href="#"><svg xmlns="http://www.w3.org/2000/svg" height="26px" viewBox="0 -960 960 960" width="26px" fill="#e3e3e3"><path d="..."/></svg></a></li>
        <li onclick="window.location.href='Home.php'"><a href="#">Home</a></li>
        <li><a href="#">Contact Us</a></li>
        <li onclick="window.location.href='logout.php'"><a href="#">Logout</a></li>
    </ul>
    <ul>
        <li class="hideOnMobile"><a href="#"></a></li>
        <li class="hideOnMobile" onclick="window.location.href='Home.php'"><a href="#">Home</a></li>
        <li class="hideOnMobile dropdown">
            <a href="#">Contact Us ▼</a>
            <ul class="dropdown-menu">
                <li><a href="">Email Us</a></li>
                <li><a href="">Call Support</a></li>
            </ul>
        </li>
        <li class="hideOnMobile" onclick="window.location.href='logout.php'"><a href="#">Logout</a></li>
        <li class="menu-button" onclick=showsidebar()><a href="#"><svg xmlns="http://www.w3.org/2000/svg" height="26px" viewBox="0 -960 960 960" width="26px" fill="#e3e3e3"><path d="..."/></svg></a></li>
    </ul>
</nav>

<div class="home-text">
    <h1 class="head" style="text-align:center">Welcome</h1>
    <p>Ready to book your seat(s), <span><?= $_SESSION["first-name"] ?> <?= $_SESSION["last-name"] ?></span>?</p>
</div>

<h1>Book Your Flight</h1>

<?php if ($flightNum && $from && $to): ?>
    <div class="form-container">
        <form method="post">
            <label>Number of Seats:</label><br>
            <input type="number" name="seat_count" min="1" required><br><br>

            <label>Class:</label><br>
            <input type="radio" name="class_type" value="Business" checked>Business
            <input type="radio" name="class_type" value="Economy">Economy<br><br>

            <label>
                Seat Type:
            </label><br>
            <input type="radio" name="seat_type" value="Aile" checked>Aile
            <input type="radio" name="seat_type" value="Window" checked>Window
            <input type="radio" name="seat_type" value="Middle">Middle<br><br>

            <input type="hidden" name="flight_num" value="<?= htmlspecialchars($flightNum) ?>">
            <input type="hidden" name="from" value="<?= htmlspecialchars($from) ?>">
            <input type="hidden" name="to" value="<?= htmlspecialchars($to) ?>">

            <button type="submit">Book Ticket</button>
        </form>
    </div>
<?php else: ?>
    <p style="text-align:center; color:red;">Missing flight details.</p>
<?php endif; ?>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["seat_count"], $_POST["class_type"], $_POST["flight_num"], $_POST["from"], $_POST["to"])) {
    if (!$shouldCancel) {
        // Proceed with ticket booking as before...
        $seatCount = intval($_POST["seat_count"]);
        $classType = $_POST["class_type"];
        $flightNum = $_POST["flight_num"];
        $from = $_POST["from"];
        $to = $_POST["to"];
        $userName = $_SESSION["user-name"];
        $seatType = $_POST["seat_type"] ?? "Aile";

        if ($seatCount > 0 && in_array($classType, ["Business", "Economy"])) {
            $conn = new mysqli("localhost", "root", "", "FlightBookingDB");
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Get user full name
            $stmt = $conn->prepare("SELECT First_name, Last_name FROM Passengers WHERE User_name = ?");
            $stmt->bind_param("s", $userName);
            $stmt->execute();
            $res = $stmt->get_result();
            $fullName = "Unknown";
            if ($row = $res->fetch_assoc()) {
                $fullName = $row["First_name"] . " " . $row["Last_name"];
            }
            $stmt->close();

            // Get flight name
            $stmt = $conn->prepare("SELECT Flight_name FROM Flights WHERE Flight_num = ?");
            $stmt->bind_param("s", $flightNum);
            $stmt->execute();
            $res = $stmt->get_result();
            $flightName = "Unknown";
            if ($row = $res->fetch_assoc()) {
                $flightName = $row["Flight_name"];
            }
            $stmt->close();

            // Get base fare
            $fareQuery = $classType === "Economy"
                ? "SELECT Base_fair FROM Economy WHERE Flight_num = ?"
                : "SELECT Base_fair FROM Business WHERE Flight_num = ?";
            $fareStmt = $conn->prepare($fareQuery);
            $fareStmt->bind_param("s", $flightNum);
            $fareStmt->execute();
            $fareResult = $fareStmt->get_result();
            $baseFare = 0;
            if ($fareRow = $fareResult->fetch_assoc()) {
                $baseFare = intval($fareRow["Base_fair"]);
            }
            $fareStmt->close();

            $totalPrice = $baseFare * $seatCount;
            $date = date("Y-m-d");
            $time = date("H:i:s");

            $all_ticket_ids = [];

            $insert = $conn->prepare("INSERT INTO Ticket (
                T_num, Passenger_name, Date, Time, Class_type,
                Source, Destination, Seat_type, User_name, Flight_num
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            for ($i = 0; $i < $seatCount; $i++) {
                $ticketNum = strtoupper(uniqid("TK"));
                $all_ticket_ids[] = $ticketNum;

                $insert->bind_param(
                    "ssssssssss",
                    $ticketNum, $fullName, $date, $time, $classType,
                    $from, $to, $seatType, $userName, $flightNum
                );

                if (!$insert->execute()) {
                    echo "<p style='text-align:center; color:red;'>Error inserting ticket: " . $insert->error . "</p>";
                }
            }

            $insert->close();
            $conn->close();

            $_SESSION['pending_tickets'] = $all_ticket_ids;

            echo "<h2>Booking Summary</h2>";
            echo "<table class='booking-summary'>
                    <tr>
                        <th>Ticket Number(s)</th>
                        <th>Passenger Name</th>
                        <th>Flight Number</th>
                        <th>Flight Name</th>
                        <th>Class</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Seats</th>
                        <th>Total Price (৳)</th>
                    </tr>
                    <tr>
                        <td>" . implode(", ", $all_ticket_ids) . "</td>
                        <td>{$fullName}</td>
                        <td>{$flightNum}</td>
                        <td>{$flightName}</td>
                        <td>{$classType}</td>
                        <td>{$from}</td>
                        <td>{$to}</td>
                        <td>{$seatCount}</td>
                        <td>৳{$totalPrice}</td>
                    </tr>
                  </table>";

            echo "<div style='text-align: center; margin-top: 20px;'>
                    <button class='pay' onclick=\"window.location.href='payment.php'\">Proceed to Payment</button>
                  </div>";
        }
    } else {
        echo $weatherStatusMessage; // Display the weather issue message if the flight is canceled
        exit();
    }
}
?>

</body>
</html>

