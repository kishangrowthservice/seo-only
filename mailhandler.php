<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

$mail = new PHPMailer(true);

$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);

try {
    // SMTP Configuration from screenshot
    $mail->isSMTP();
    $mail->Host = 'mail.madridsoftwaretrainings.com'; // From screenshot
    $mail->SMTPAuth = true;
    $mail->Username = 'madrid@madridsoftwaretrainings.com';
    $mail->Password = 'Kataria07#'; // Use your actual password
    $mail->SMTPSecure = 'ssl'; // Use SSL, not TLS
    $mail->Port = 465; // From screenshot
    $mail->CharSet = 'UTF-8';

    // Optional: Enable debug output during testing
    // $mail->SMTPDebug = 2;
    // $mail->Debugoutput = 'html';

    // Collect and sanitize input
    $name = isset($_POST['name']) ? trim($_POST['name']) : 'Anonymous';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['tel']) ? trim($_POST['tel']) : 'Not provided';
    $city = isset($_POST['city']) ? trim($_POST['city']) : 'Not provided';
    $pageName = isset($_POST['pageName']) ? trim($_POST['pageName']) : 'Unknown page';

    // Email Headers
    $mail->setFrom('madrid@madridsoftwaretrainings.com', 'Madrid Software');
    $mail->addAddress('info@madridsoftwaretrainings.com');

    // // Reply-To Header
    // if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //     $mail->addReplyTo($email, $name);
    // } else {
    //     $mail->addReplyTo('no-reply@madridsoftwaretrainings.com', 'Website Visitor');
    // }

    // Email Content
    $mail->isHTML(true);
    $subject = isset($_POST['pageSubject']) ? $_POST['pageSubject'] : 'New Form Submission';
    $mail->Subject = htmlspecialchars($subject);
    $mail->Body =
        '<strong>Name:</strong> ' . htmlspecialchars($name) . '<br>' .
        '<strong>Email:</strong> ' . htmlspecialchars($email) . '<br>' .
        '<strong>Phone:</strong> ' . htmlspecialchars($phone) . '<br>' .
        '<strong>City:</strong> ' . htmlspecialchars($city) . '<br>' .
        '<strong>Page:</strong> ' . htmlspecialchars($pageName);

    // Send Email
    $mail->send();
    echo 'success';
}catch (Exception $e) {
    http_response_code(500); // Set appropriate error code
    echo 'Mailer Error: ' . $mail->ErrorInfo . '<br>';
    echo 'Exception Message: ' . $e->getMessage();
}
