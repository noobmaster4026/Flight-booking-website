<?php
require_once "config.php";

if (!isset($_SESSION["user-name"])) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['pending_tickets']) || empty($_SESSION['pending_tickets'])) {
    echo "<p>No pending tickets found. You may have already processed the payment.</p>";
    exit();
}

$flightNum = $_GET['flight_num'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$ticketIds = $_SESSION['pending_tickets'];

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

$shouldCancel = false;
$reasons = [];

if ($row = $res->fetch_assoc()) {
    $windSpeed = floatval($row["Wind_speed"]);
    $temperature = floatval($row["Temperature"]);
    $visibility = floatval($row["Visibility"]);
    $precipitation = strtolower($row["Precipitation"]); // Normalize casing

    // Evaluate conditions
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
    // Cancel tickets in DB
    $cancelledTicketIds = [];
    $cancelStmt = $conn->prepare("UPDATE Ticket SET Ticket_Status = 'Cancelled' WHERE T_num = ?");
    foreach ($ticketIds as $ticketId) {
        $cancelStmt->bind_param("s", $ticketId);
        if ($cancelStmt->execute()) {
            $cancelledTicketIds[] = $ticketId;
        }
    }
    $cancelStmt->close();
    $conn->close();

    $_SESSION['cancelled_tickets'] = $cancelledTicketIds;

    echo "<h2 style='color:red;'>❌ Flight Cancelled Due to Bad Weather</h2>";
    echo "<p><strong>Reason(s):</strong></p><ul>";
    foreach ($reasons as $reason) {
        echo "<li>{$reason}</li>";
    }
    echo "</ul>";
    echo "<p>Tickets have been cancelled and cannot proceed to payment.</p>";
    echo "<p><a href='Home.php'>Return to Home Page</a></p>";
    exit();
} else {
    echo "<h2 style='color:green;'>✅ Weather Conditions Are Clear for Your Flight</h2>";
    echo "<p>You may now proceed with your payment.</p>";
    echo "<p><a href='payment.php'>Proceed to Payment</a></p>";
}

$conn->close();
?>
