<?php
require_once "config.php";

if (!isset($_SESSION['user-name'])) {
  header('Location: index.php');
  exit;
}

$user = $_SESSION['user-name'];
$tickets = $_POST['t_num'] ?? [];
$method  = $_POST['payment-method'] ?? '';

$paymentSuccess = false;
$errorMsg = "";
$paymentIds = [];
$totalAmount = 0;

if (empty($tickets) || !$method) {
  $errorMsg = "No tickets selected or invalid method.";
} else {
  $date = date('Y-m-d');
  $time = date('H:i:s');

  foreach ($tickets as $t_num) {
    // Get actual price based on ticket class
    $stmt = $conn->prepare("
      SELECT t.Class_type,
             CASE 
               WHEN t.Class_type = 'Economy' THEN e.Base_fair
               WHEN t.Class_type = 'Business' THEN b.Base_fair
               ELSE 0
             END AS price
      FROM Ticket t
      JOIN Flights f ON t.Flight_num = f.Flight_num
      LEFT JOIN Economy e ON f.Flight_num = e.Flight_num
      LEFT JOIN Business b ON f.Flight_num = b.Flight_num
      WHERE t.T_num = ?
    ");
    $stmt->bind_param("s", $t_num);
    $stmt->execute();
    $result = $stmt->get_result();
    $price = 0;
    if ($row = $result->fetch_assoc()) {
      $price = $row['price'];
    }
    $stmt->close();

    if ($price > 0) {
      $totalAmount += $price;

      $stmt = $conn->prepare("
        INSERT INTO Payment (T_num, Amount, Payment_date, Payment_time, Payment_method, Payment_status)
        VALUES (?, ?, ?, ?, ?, 'Completed')
      ");
      $stmt->bind_param("sdsss", $t_num, $price, $date, $time, $method);

      if ($stmt->execute()) {
        $paymentIds[] = $stmt->insert_id;

        // Update ticket status
        $update = $conn->prepare("UPDATE Ticket SET Ticket_Status = 'Approved' WHERE T_num = ?");
        $update->bind_param("s", $t_num);
        $update->execute();
        $update->close();
      }

      $stmt->close();
    }
  }

  $paymentSuccess = count($paymentIds) === count($tickets);
}

unset($_SESSION['pending_tickets']);
$conn->close();
?>


<!-- Write from here -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment Status</title>
  <link rel="stylesheet" href="payment_style.css">
</head>
<body>
  <div class="container">
  <?php if ($paymentSuccess): ?>
    <h2>✅ Payment Successful!</h2>
    <p>Paid for <?= count($paymentIds) ?> ticket(s).</p>
    <p><strong>Total Paid:</strong> <?= number_format($totalAmount, 2) ?> BDT</p>
    <ul>
      <?php foreach ($paymentIds as $id): ?>
        <li>Payment ID: <?= $id ?></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <h2>❌ Payment Failed</h2>
    <p><?= htmlspecialchars($errorMsg) ?></p>
  <?php endif; ?>
    <a href="Home.php">Return to Home Page</a>
  </div>
</body>
</html>