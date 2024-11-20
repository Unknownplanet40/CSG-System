<?php

require '../../vendor/autoload.php';

use Fpdf\Fpdf;

$Organization = strtoupper('Central Student Government');
$Email = 'Example@domain.com';

$rand = rand(1000, 9999);
$control_no = 'ORGNAME - ' . $rand;
$puprose = 'For the payment of the following: Lorem ipsum dolor sit amet, consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
$amount = 'PHP 1,000.00';
$Release_Date = date('F d, Y');
$Witnessed_By = 'Ryan James V. Capadocia';

class PDF extends Fpdf
{
    // custom Method to Justify Text
    public function JustifiedCell($w, $h, $txt, $border = 0, $ln = 0, $align = '', $fill = false)
    {
    $cw = &$this->CurrentFont['cw'];
    if ($w == 0) {
        $w = $this->w - $this->rMargin - $this->x;
    }
    $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
    $s = str_replace("\r", '', $txt);
    $nb = strlen($s);
    if ($nb > 0 && $s[$nb - 1] == "\n") {
        $nb--;
    }
    $b = 0;
    if ($border) {
        if ($border == 1) {
            $border = 'LTRB';
            $b = 'LRT';
            $b2 = 'LR';
        } else {
            $b2 = '';
            if (is_int(strpos($border, 'L'))) {
                $b2 .= 'L';
            }
            if (is_int(strpos($border, 'R'))) {
                $b2 .= 'R';
            }
            $b = is_int(strpos($border, 'T')) ? $b2 . 'T' : $b2;
        }
    }
    $sep = -1;
    $i = 0;
    $j = 0;
    $l = 0;
    $ns = 0;
    $nl = 1;
    while ($i < $nb) {
        $c = $s[$i];
        if ($c == "\n") {
            if ($this->ws > 0) {
                $this->ws = 0;
                $this->_out('0 Tw');
            }
            $this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill);
            $i++;
            $sep = -1;
            $j = $i;
            $l = 0;
            $ns = 0;
            $nl++;
            if ($border && $nl == 2) {
                $b = $b2;
            }
            continue;
        }
        if ($c == ' ') {
            $sep = $i;
            $ls = $l;
            $ns++;
        }
        $l += $cw[$c];
        if ($l > $wmax) {
            if ($sep == -1) {
                if ($i == $j) {
                    $i++;
                }
                if ($this->ws > 0) {
                    $this->ws = 0;
                    $this->_out('0 Tw');
                }
                $this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill);
            } else {
                if ($align == 'J') {
                    $this->ws = ($ns > 1) ? ($wmax - $ls) / 1000 * $this->FontSize / ($ns - 1) : 0;
                    $this->_out(sprintf('%.3F Tw', $this->ws * $this->k));
                }
                $this->Cell($w, $h, substr($s, $j, $sep - $j), $b, 2, $align, $fill);
                $i = $sep + 1;
            }
            $sep = -1;
            $j = $i;
            $l = 0;
            $ns = 0;
            $nl++;
            if ($border && $nl == 2) {
                $b = $b2;
            }
        } else {
            $i++;
        }
    }
    if ($this->ws > 0) {
        $this->ws = 0;
        $this->_out('0 Tw');
    }
    if ($border && is_int(strpos($border, 'B'))) {
        $b .= 'B';
    }
    $this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill);
    $this->x = $this->lMargin;
}

    public function CustomHeader()
    {
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'Cover Letter and Activity Proposal | Title | Page ' . $this->PageNo() . ' of {nb}', 0, 1, 'R'); // Right aligned
        $this->Ln(5);

        $this->Image('../../Assets/Images/pdf-Resource/L_Logo.png', 45, 22, 40);
        $this->Image('../../Assets/Images/pdf-Resource/R_Logo.png', 160, 22, 40);

        $this->setX(38.1);
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'Republic of the Philippines', 0, 1, 'C');

        $this->setX(38.1);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 6, 'CAVITE STATE UNIVERSITY', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 10);

        $this->setX(38.1);
        $this->Cell(0, 5, 'Imus, Cavite', 0, 1, 'C');

        $this->setX(38.1);
        $this->Cell(0, 5, 'Student Development Services', 0, 1, 'C');

        $this->SetFont('Arial', 'B', 12);
        $this->setX(38.1);
        $this->Cell(0, 5, 'CENTRAL STUDENT GOVERMENT', 0, 1, 'C');
        $this->setX(38.1);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 5, 'csg.system@cvsu.edu.ph', 0, 1, 'C');
    }

    public function Content()
    {
        $this->Ln(15);
        $this->setX(38.1);
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 5, 'Date: ' . date('F d, Y'), 0, 1);
        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 5, 'The Dean', 0, 1);
        $this->setX(38.1);
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 5, 'College of Engineering and Information Technology', 0, 1);
        $this->setX(38.1);
        $this->Cell(0, 5, 'Cavite State University', 0, 1);

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 5, 'Dear Sir/Madam:', 0, 1);

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('Arial', '', 11);
        $this->JustifiedCell(156, 5, 'The Central Student Government of Cavite State University, a duly recognized student organization of the University, would like to request for the approval of the following activity:', 0, 1, 'J');

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('Arial', '', 11);
        $this->JustifiedCell(156, 5, 'The activity aims to provide a platform for the students to showcase their talents and skills in the field of music, dance, and other performing arts. It also aims to promote camaraderie and unity among the students of the University.', 0, 1, 'J');

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('Arial', 'B', 11);
        $this->JustifiedCell(156, 5, 'In this regard, we would like to request for the approval of the said activity and seek your support in making it a success.', 0, 1, 'J');

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 5, 'Thank you and we look forward to your favorable response.', 0, 1);

        $this->Ln(8);
        $this->setX(38.1);

        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 5, 'Sincerely,', 0, 1);

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 5, 'Ryan James V. Capadocia', 0, 1);
        $this->setX(38.1);
        $this->Cell(0, 5, 'President', 0, 1);

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 5, 'Noted by:', 0, 1);

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 5, 'Dr. Hernando D. Robles', 0, 1);
        $this->setX(38.1);
        $this->Cell(0, 5, 'University President', 0, 1);

    }
}
$pdf = new PDF('P', 'mm', 'Letter');
$pdf->AddPage();
$pdf->AliasNbPages();
$pdf->CustomHeader();
$pdf->Content();
$pdf->Output('I', 's_' . $rand . '.pdf');
// D - Download
// I - Display
// F - Save to a local file
