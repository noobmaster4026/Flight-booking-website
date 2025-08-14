<?php
require_once "config.php";

if (!isset($_SESSION['user-name'])) {
    header('Location: index.php');
    exit;
}

$user = $_SESSION['user-name'];

// Get tickets and price based on class (Economy/Business)
$stmt = $conn->prepare("
  SELECT t.T_num, t.Class_type,
         CASE 
           WHEN t.Class_type = 'Economy' THEN e.Base_fair
           WHEN t.Class_type = 'Business' THEN b.Base_fair
           ELSE 0
         END AS price
  FROM Ticket t
  JOIN Flights f ON t.Flight_num = f.Flight_num
  LEFT JOIN Economy e ON f.Flight_num = e.Flight_num
  LEFT JOIN Business b ON f.Flight_num = b.Flight_num
  WHERE t.User_name = ? AND t.T_num NOT IN (
    SELECT T_num FROM Payment
  )
");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

$tickets = [];
$totalAmount = 0;
while ($row = $result->fetch_assoc()) {
    $tickets[] = [
        'T_num' => $row['T_num'],
        'price' => $row['price']
    ];
    $totalAmount += $row['price'];
}
$stmt->close();

if (empty($tickets)) {
  echo '<p style="font-size: 35px; color: #555; text-align: center; margin-top: 30px;
      background: #f9f9f9; padding: 12px 20px; border-radius: 6px;">
    You have no unpaid tickets. Please book a ticket first.
  </p>';
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment Gateway</title>
  <link rel="stylesheet" href="payment_styles.css">
</head>
<body>
  <div class="payment-container">
    <h2>Payment Information</h2>

    <form method="post" action="process_payment.php" id="payment-form">
      <label for="t_num">Select Ticket(s):</label>
      <select name="t_num[]" id="t_num" multiple required>
        <?php foreach ($tickets as $ticket): ?>
          <option value="<?= htmlspecialchars($ticket['T_num']) ?>">
            <?= htmlspecialchars($ticket['T_num']) ?> - <?= $ticket['price'] ?> BDT
          </option>
        <?php endforeach; ?>
      </select>
      <br><br>
      <input type="hidden" name="amount" id="amount" value="<?= $totalAmount ?>">

      <div class="payment-methods">
        <div class="payment-method-option">
          <label>
            <input type="radio" name="payment-method" value="Visa Card" checked>
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/2560px-Visa_Inc._logo.svg.png" alt="Visa">
            Visa Card
          </label>
        </div>
        
        <div class="payment-method-option">
          <label>
            <input type="radio" name="payment-method" value="Master Card">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/1280px-Mastercard-logo.svg.png" alt="Master Card">
            Master Card
          </label>
        </div>
        
        <div class="payment-method-option">
          <label>
            <input type="radio" name="payment-method" value="Bkash">
            <img src="https://images.seeklogo.com/logo-png/27/1/bkash-logo-png_seeklogo-273684.png" alt="bKash">
            bKash
          </label>
        </div>
        
        <div class="payment-method-option">
          <label>
            <input type="radio" name="payment-method" value="Paypal">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/PayPal.svg/1200px-PayPal.svg.png" alt="PayPal">
            PayPal
          </label>
        </div>
      </div>

      <!-- Visa/Master Card Fields -->
      <div id="card-fields" class="payment-method-fields active">
        <div class="form-group">
          <label for="card-number">Card Number</label>
          <input type="text" id="card-number" name="card-number" placeholder="1234 5678 9012 3456" maxlength="19">
        </div>
        <div class="form-group">
          <label for="card-name">Name on Card</label>
          <input type="text" id="card-name" name="card-name" placeholder="John Doe">
        </div>
        <div class="form-group">
          <label for="card-expiry">Expiry Date</label>
          <input type="text" id="card-expiry" name="card-expiry" placeholder="MM/YY">
        </div>
        <div class="form-group">
          <label for="card-cvv">CVV</label>
          <input type="text" id="card-cvv" name="card-cvv" placeholder="123" maxlength="3">
        </div>
      </div>

      <!-- bKash Fields -->
      <div id="bkash-fields" class="payment-method-fields">
        <div class="instructions">
          <p><strong>How to pay with bKash:</strong></p>
          <ol>
            <li>Dial *247#</li>
            <li>Select "Payment"</li>
            <li>Enter Merchant bKash Number: 01234567890</li>
            <li>Enter amount: <?= $base_price ?> BDT</li>
            <li>Enter reference: Your Ticket Number</li>
            <li>Enter your bKash PIN to confirm</li>
          </ol>
        </div>
        <div class="form-group">
          <label for="bkash-number">Your bKash Number</label>
          <input type="text" id="bkash-number" name="bkash-number" placeholder="01XXXXXXXXX">
        </div>
        <div class="form-group">
          <label for="bkash-transaction">Transaction ID</label>
          <input type="text" id="bkash-transaction" name="bkash-transaction" placeholder="TRX12345678">
        </div>
      </div>

      <!-- PayPal Fields -->
      <div id="paypal-fields" class="payment-method-fields">
        <div class="form-group">
          <label for="paypal-email">PayPal Email</label>
          <input type="email" id="paypal-email" name="paypal-email" placeholder="your@email.com">
        </div>
        <div class="form-group">
          <label for="paypal-transaction">Transaction ID</label>
          <input type="text" id="paypal-transaction" name="paypal-transaction" placeholder="PAYID-XXXXXX">
        </div>
      </div>

      <button type="submit">Pay Now</button>
    </form>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const cardFields = document.getElementById('card-fields');
    const bkashFields = document.getElementById('bkash-fields');
    const paypalFields = document.getElementById('paypal-fields');
    const paymentMethods = document.querySelectorAll('input[name="payment-method"]');

    function setRequiredFields(method) {
      // Clear all required attributes
      cardFields.querySelectorAll('input').forEach(input => input.required = false);
      bkashFields.querySelectorAll('input').forEach(input => input.required = false);
      paypalFields.querySelectorAll('input').forEach(input => input.required = false);

      // Hide all sections
      cardFields.classList.remove('active');
      bkashFields.classList.remove('active');
      paypalFields.classList.remove('active');

      // Show and set required fields
      if (method === 'Visa Card' || method === 'Master Card') {
        cardFields.classList.add('active');
        cardFields.querySelectorAll('input').forEach(input => input.required = true);
      } else if (method === 'Bkash') {
        bkashFields.classList.add('active');
        bkashFields.querySelectorAll('input').forEach(input => input.required = true);
      } else if (method === 'Paypal') {
        paypalFields.classList.add('active');
        paypalFields.querySelectorAll('input').forEach(input => input.required = true);
      }
    }

    // Attach change event listeners
    paymentMethods.forEach(method => {
      method.addEventListener('change', function () {
        setRequiredFields(this.value);
      });
    });

    // Initial state setup
    const initialMethod = document.querySelector('input[name="payment-method"]:checked').value;
    setRequiredFields(initialMethod);

    // Card formatting
    document.getElementById('card-number').addEventListener('input', function (e) {
      let value = e.target.value.replace(/\s+/g, '');
      if (value.length > 0) {
        value = value.match(/.{1,4}/g)?.join(' ') || value;
      }
      e.target.value = value;
    });

    document.getElementById('card-expiry').addEventListener('input', function (e) {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length > 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
      }
      e.target.value = value;
    });
  });
</script>
</body>
</html>