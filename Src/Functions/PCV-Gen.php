<?php

require '../../vendor/autoload.php';

// Use the correct FPDF namespace
use Fpdf\Fpdf;  // Use the correct namespace for FPDF

$Organization = strtoupper('Central Student Government');
$Email = 'Example@domain.com';

$rand = rand(1000, 9999);
$control_no = 'ORGNAME - ' . $rand;
$puprose = 'For the payment of the following: Lorem ipsum dolor sit amet, consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
$amount = 'PHP 1,000.00';
$Release_Date = date('F d, Y');
$Witnessed_By = 'Ryan James V. Capadocia';

class PDF extends Fpdf
{ // Extend from FPDF
    public function CustomHeader($Organization, $Email)
    {
        $this->Image('../../Assets/Images/pdf-Resource/L_Logo.png', 10, 8, 40);
        $this->Image('../../Assets/Images/pdf-Resource/R_Logo.png', 160, 8, 40);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(30, 15, $Organization, 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(187, 0, $Email, 0, 0, 'C');
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'PETTY CASH VOUCHER', 0, 1, 'C');
    }

    public function Content($rand, $puprose, $amount, $Release_Date, $Witnessed_By, $control_no)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(30, 10, 'Control No.: ', 0, 0);
        $this->setFont('Arial', 'BU', 12,);
        $this->Cell(50, 10, $control_no, 0, 1);

        $this->SetFont('Arial', 'B', 12);
        $this->Ln(5);
        $this->Cell(30, 10, 'Date: ', 0, 0);
        $this->SetFont('Arial', 'U', 12);
        $this->Cell(50, 10, date('F d, Y'), 0, 1);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(30, 10, 'Purpose: ', 0, 0);
        $this->SetFont('Arial', 'U', 12);
        $this->MultiCell(0, 10, $puprose, 0, 1);
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(30, 10, 'Amount: ', 0, 0);
        $this->SetFont('Arial', 'U', 12);
        $this->Cell(50, 10, $amount, 0, 1);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(30, 10, 'Release Date: ', 0, 0);
        $this->SetFont('Arial', 'U', 12);
        $this->Cell(60, 10, $Release_Date, 0, 0);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(35, 10, 'Witnessed By: ', 0, 0);
        $this->SetFont('Arial', 'U', 12);
        $this->Cell(65, 10, $Witnessed_By, 0, 1);
        $this->Ln(10);
        $x = $this->GetX();
        $y = $this->GetY();
        $this->Line($x, $y, 200, $y);


    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->CustomHeader($Organization, $Email);
$pdf->Content($rand, $puprose, $amount, $Release_Date, $Witnessed_By, $control_no);
$pdf->Output('D', 'PettyCashVoucher_' . $control_no . '.pdf');
