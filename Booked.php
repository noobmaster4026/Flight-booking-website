<?php

require_once "config.php";
// 1. Ensure user is logged in
if (!isset($_SESSION["user-name"])){
    header("Location: index.php");
    exit();
}
$username = $_SESSION['user-name'];



// 3. Handle deletion request
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ticket'])) {
    $t_num = $_POST['delete_ticket'];

    // Start transaction
    $conn->begin_transaction();
    try {
        // 3a) Delete any payments first
        $delPay = $conn->prepare("DELETE FROM Payment WHERE T_num = ?");
        $delPay->bind_param('s', $t_num);
        $delPay->execute();
        $delPay->close();

        // 3b) Now delete the ticket
        $delTicket = $conn->prepare(
            "DELETE FROM Ticket 
             WHERE T_num = ? 
               AND User_name = ?"
        );
        $delTicket->bind_param('ss', $t_num, $username);
        $delTicket->execute();
        $delTicket->close();

        // Commit only if both succeed
        $conn->commit();
        $message = "Ticket <strong>{$t_num}</strong> (and its payment record) deleted successfully. The ticket price has been refunded.";
    } catch (\Exception $e) {
        $conn->rollback();
        $message = "Deletion failed: " . $e->getMessage();
    }
}

// 4. Fetch all tickets for this user
$stmt = $conn->prepare(
    "SELECT T_num, Date, Time, Class_type, Source, Destination, Seat_type, Ticket_Status 
     FROM Ticket 
     WHERE User_name = ?"
);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$tickets = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Booked Tickets</title>
  <link rel="stylesheet" href="booked.css">
</head>
<body>
  <div class="container">
    <h1>My Booked Tickets</h1>

    <?php if ($message): ?>
      <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (count($tickets) === 0): ?>
      <p>You have no booked tickets.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Ticket #</th>
            <th>Date</th>
            <th>Time</th>
            <th>Class</th>
            <th>From → To</th>
            <th>Seat Type</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tickets as $t): ?>
            <tr>
              <td><?php echo htmlspecialchars($t['T_num']); ?></td>
              <td><?php echo htmlspecialchars($t['Date']); ?></td>
              <td><?php echo htmlspecialchars($t['Time']); ?></td>
              <td><?php echo htmlspecialchars($t['Class_type']); ?></td>
              <td>
                <?php echo htmlspecialchars($t['Source']); ?> → 
                <?php echo htmlspecialchars($t['Destination']); ?>
              </td>
              <td><?php echo htmlspecialchars($t['Seat_type']); ?></td>
              <td><?php echo htmlspecialchars($t['Ticket_Status']); ?></td>
              <td>
                <form method="post" style="display:inline" onsubmit="return confirm('Delete ticket <?php echo $t['T_num']; ?>?');">
                  <input type="hidden" name="delete_ticket" value="<?php echo htmlspecialchars($t['T_num']); ?>">
                  <button type="submit" class="btn-delete">Delete</button>
                </form>
                <form method="get" action="generate_ticket_pdf.php" style="display:inline" target="_blank">
                  <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($t['T_num']); ?>">
                  <button type="submit" class="btn-print">Print</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <p><a href="Home.php">Back to Home Page</a></p>
  </div>
</body>
</html>
