<?php
session_start();
require('fpdf/fpdf.php'); // make sure fpdf is installed in your project
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) { header("Location: login.php"); exit; }

// Fetch subscriptions
$stmt = $pdo->prepare("SELECT name, price, start_date, end_date FROM subscriptions WHERE user_id = ?");
$stmt->execute([$user_id]);
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

$pdf->Cell(0, 10, 'My Subscriptions', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, 'Name', 1);
$pdf->Cell(30, 10, 'Price', 1);
$pdf->Cell(40, 10, 'Start Date', 1);
$pdf->Cell(40, 10, 'End Date', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
foreach ($subscriptions as $sub) {
    $pdf->Cell(50, 10, $sub['name'], 1);
    $pdf->Cell(30, 10, $sub['price'], 1);
    $pdf->Cell(40, 10, $sub['start_date'], 1);
    $pdf->Cell(40, 10, $sub['end_date'], 1);
    $pdf->Ln();
}

$pdf->Output('D', 'subscriptions.pdf'); // D = download
exit;
?>
