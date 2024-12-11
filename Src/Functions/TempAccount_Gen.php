<?php

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $Email = $_GET['Email'];
    $Password = $_GET['Password'];

    if (empty($_GET['CvSU_Mail'])) {
        $CvSU_Mail = "NONE";
    } else {

        header('Content-Type: application/json');
        date_default_timezone_set('Asia/Manila');
        
        function response($data)
        {
            echo json_encode($data);
            exit;
        }

        $CvSU_Mail = $_GET['CvSU_Mail'];
    }
} else {
    $Email = "No Data Found";
    $Password = "No Data Found";
    $CvSU_Mail = "NONE";
}

require '../../vendor/autoload.php';
require './env/HiddenKeys.php';
use Fpdf\Fpdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PDF extends Fpdf
{
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

if ($CvSU_Mail != "NONE") {
    $temppassword = $Password;
    $tempemail = $Email;

    $mail = new PHPMailer(true);
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = $SMTP_USER;
        $mail->Password = $SMTP_PASS;
        $mail->SMTPSecure = $SMTP_SECURE;
        $mail->Port = $SMTP_PORT;

        $mail->setFrom($SMTP_USER, 'CSG - System');
        $mail->addAddress($CvSU_Mail, 'Organization Member');

        $mail->isHTML(true);
        $mail->Subject = 'Central Student Government - Temporary Account';
        $mail->Body = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/><title>Security Alert</title><style>body{font-family:Segoe UI,Roboto,sans-serif;line-height:1.6;}.container{max-width:600px;margin:0 auto;padding:20px;}.header{color:white;padding:5px 0;text-align:center;border-radius:10px 10px 0 0;display:flex;align-items:center;justify-content:center;}.header h2{color:black;}.content{padding:10px 20px 20px 20px;margin-top:20px;}.footer{margin-top:20px;text-align:center;font-size:0.9em;color:#777;}table{width:100%;}td{padding:10px;}img{display:block;margin:0 auto;}h2{margin:0;color:#fff;}ul{list-style-type:none;padding:0;}li{margin-bottom:10px;}p{margin:0 0 10px;}</style></head><body><div class="container"><div class="header"><table><tr><td><img src="https://i.imgur.com/Xd06F5f.jpeg" alt="Company Logo" style="display:block;border-radius:50%;margin:0 auto" width="48"/></td><td><h2>Central Student Government</h2></td></tr></table></div><hr/><h3 style="text-align:start;text-transform:uppercase;color:#333;margin-bottom:-10px;">Security alert</h3><small style="color:#777">' . date('F j, Y') . '</small><div class="content"><p>Hello! Please activate the temporary email address and password provided below to create your account. This information is temporary and should not be shared with anyone.</p><p style="text-align:center;margin-top:10px"><b>Email Address:</b> ' . $tempemail . '<br/><b>Password:</b> ' . $temppassword . '</p><div style="text-align:end"><p>Thank you!</p><p><br/>Best regards,<br/>Central Student Government</p></div></div><div class="footer"><p>&copy; ' . date('Y') . ' Central Student Government. All rights reserved.</p></div><div><p style="text-align:center;color:#777;font-size:0.8em;margin-top:20px;">This email was sent to <b>' . $CvSU_Mail . '</b> because you were invited to create an account with Central Student Government. If you believe this email was sent by mistake, please ignore it.</p></div></div></body></html>';
        $mail->AltBody = 'This is a system generated email. Please do not reply to this email. If you have any questions, please contact the system administrator. | Central Student Government';

        if ($mail->send()) {
            $SMTPMessage = "Email sent successfully";
            response(['stat' => 'success', 'message' => 'Email sent successfully']);
        } else {
            $SMTPMessage = "Message could not be sent. Mailer Error: " . $mail->ErrorInfo;
            response(['stat' => 'error', 'message' => 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo]);
        }

    } catch (Exception $e) {
        $SMTPMessage = "Message could not be sent. Mailer Error: " . $mail->ErrorInfo;
        response(['stat' => 'error', 'message' => 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo]);
    }
} else {
    $pdf->Output('D', 'TemporaryAccount_' . rand(1000, 9999) . '.pdf');
}
