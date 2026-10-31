<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

// ---------------------------------------------------------------------------
// SMTP credentials are NOT stored in this file any more.
// The previous version hard-coded the mailbox password in this public
// repository; that password is compromised and MUST be rotated.
// Configure credentials on the server only: environment variables
// SMTP_USER / SMTP_PASS, or a git-ignored mail-credentials.php in this
// folder (see mail-credentials.example.php).
// ---------------------------------------------------------------------------
$credFile = __DIR__ . '/mail-credentials.php';
if (file_exists($credFile)) {
    require $credFile;
}
$smtpUser = getenv('SMTP_USER') ?: (defined('SMTP_USER') ? SMTP_USER : '');
$smtpPass = getenv('SMTP_PASS') ?: (defined('SMTP_PASS') ? SMTP_PASS : '');
// TLS verification stays ON. Only set MAIL_TLS_VERIFY=false temporarily
// while the mail server's certificate hostname mismatch is being fixed
// (see seo/SERVER_FIXES.md) - an unverified connection can be intercepted.
$tlsVerify = defined('MAIL_TLS_VERIFY') ? (bool) MAIL_TLS_VERIFY : true;

if ($smtpUser === '' || $smtpPass === '') {
    error_log('mailhandler: SMTP credentials not configured on the server.');
    http_response_code(500);
    echo 'error';
    exit;
}

// --- Collect and validate input (strip CR/LF to block header injection) ---
$clean = function ($v) {
    return trim(str_replace(["\r", "\n"], '', (string) $v));
};
$name     = isset($_POST['name']) ? $clean($_POST['name']) : 'Anonymous';
$email    = isset($_POST['email']) ? $clean($_POST['email']) : '';
$phone    = isset($_POST['tel']) ? $clean($_POST['tel']) : 'Not provided';
$city     = isset($_POST['city']) ? $clean($_POST['city']) : 'Not provided';
$pageName = isset($_POST['pageName']) ? $clean($_POST['pageName']) : 'Unknown page';
$subject  = isset($_POST['pageSubject']) ? $clean($_POST['pageSubject']) : 'New Form Submission';

// Honeypot: real forms never send this field; bots that fill it get a
// silent "success" and nothing is mailed.
if (!empty($_POST['website'])) {
    echo 'success';
    exit;
}

// Simple per-IP throttle: max 5 submissions per 10 minutes.
$throttleFile = sys_get_temp_dir() . '/mail_throttle_' . md5($_SERVER['REMOTE_ADDR'] ?? '') . '.json';
$now = time();
$hits = [];
if (file_exists($throttleFile)) {
    $hits = array_filter(json_decode((string) file_get_contents($throttleFile), true) ?: [],
        function ($t) use ($now) { return $t > $now - 600; });
}
if (count($hits) >= 5) {
    http_response_code(429);
    echo 'error';
    exit;
}
$hits[] = $now;
file_put_contents($throttleFile, json_encode($hits), LOCK_EX);

$mail = new PHPMailer(true);

if (!$tlsVerify) {
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ];
}

try {
    $mail->isSMTP();
    $mail->Host = 'mail.madridsoftwaretrainings.com';
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUser;
    $mail->Password = $smtpPass;
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('madrid@madridsoftwaretrainings.com', 'Madrid Software');
    $mail->addAddress('info@madridsoftwaretrainings.com');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo($email, $name);
    }

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body =
        '<strong>Name:</strong> ' . htmlspecialchars($name) . '<br>' .
        '<strong>Email:</strong> ' . htmlspecialchars($email) . '<br>' .
        '<strong>Phone:</strong> ' . htmlspecialchars($phone) . '<br>' .
        '<strong>City:</strong> ' . htmlspecialchars($city) . '<br>' .
        '<strong>Page:</strong> ' . htmlspecialchars($pageName);

    $mail->send();
    echo 'success';
} catch (Exception $e) {
    // Never leak mail-server internals to the public.
    error_log('mailhandler: ' . $e->getMessage());
    http_response_code(500);
    echo 'error';
}
