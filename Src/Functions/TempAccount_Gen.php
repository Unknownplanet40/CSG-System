<?php

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $Email = $_GET['Email'];
    $Password = $_GET['Password'];
} else {
    $Email = "No Data Found";
    $Password = "No Data Found";
}

require '../../vendor/autoload.php';

use Fpdf\Fpdf;

class PDF extends Fpdf
{ // Extend from FPDF
    public function CustomHeader()
    {
        $this->Image('../../Assets/Images/pdf-Resource/L_Logo.png', 10, 8, 40);
        $this->Image('../../Assets/Images/pdf-Resource/R_Logo.png', 160, 8, 40);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(30, 15, "CENTRAL STUDENT GOVERMENT", 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(187, 0, "Acount Credential", 0, 0, 'C');
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'TEMPORARY ACCOUNT', 0, 1, 'C');
    }

    public function Content($email, $password)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Ln(5);
        $this->Cell(30, 10, 'Date: ', 0, 0);
        $this->Cell(50, 10, date('F d, Y'), 0, 1);
        $this->Cell(30, 10, 'Email: ', 0, 0);
        $this->SetFont('Arial', 'B', 16);
        $this->MultiCell(0, 10, $email, 0, 1);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(30, 10, 'Password: ', 0, 0);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(50, 10, $password, 0, 1);
        $this->SetFont('Arial', 'B', 12);
        $this->Ln(10);
        $x = $this->GetX();
        $y = $this->GetY();
        $this->Line($x, $y, 200, $y);
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->CustomHeader();
$pdf->Content($Email, $Password);
$pdf->Output('D', 'TemporaryAccount_' . rand(1000, 9999) . '.pdf');
