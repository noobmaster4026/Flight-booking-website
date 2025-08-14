<?php
require('FPDF\fpdf.php'); // Adjust the path if needed
require_once "config.php";

if (!isset($_GET['ticket_id']) || empty($_GET['ticket_id'])) {
    die("No ticket ID provided.");
}

$t_num = $_GET['ticket_id'];

// Fetch ticket details from database
$stmt = $conn->prepare("SELECT * FROM Ticket WHERE T_num = ?");
$stmt->bind_param("s", $t_num);
$stmt->execute();
$result = $stmt->get_result();
$ticket = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$ticket) {
    die("Ticket not found.");
}

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

$pdf->Cell(0, 10, 'Aviatrix Flight Ticket', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 10, 'Ticket Number:', 0, 0);
$pdf->Cell(100, 10, $ticket['T_num'], 0, 1);

$pdf->Cell(50, 10, 'Passenger Name:', 0, 0);
$pdf->Cell(100, 10, $ticket['Passenger_name'], 0, 1);

$pdf->Cell(50, 10, 'Date:', 0, 0);
$pdf->Cell(100, 10, $ticket['Date'], 0, 1);

$pdf->Cell(50, 10, 'Time:', 0, 0);
$pdf->Cell(100, 10, $ticket['Time'], 0, 1);

$pdf->Cell(50, 10, 'From:', 0, 0);
$pdf->Cell(100, 10, $ticket['Source'], 0, 1);

$pdf->Cell(50, 10, 'To:', 0, 0);
$pdf->Cell(100, 10, $ticket['Destination'], 0, 1);

$pdf->Cell(50, 10, 'Class:', 0, 0);
$pdf->Cell(100, 10, $ticket['Class_type'], 0, 1);

$pdf->Cell(50, 10, 'Seat Type:', 0, 0);
$pdf->Cell(100, 10, $ticket['Seat_type'], 0, 1);


// Output as download
$pdf->Output('D', 'ticket_' . $t_num . '.pdf');
?>
