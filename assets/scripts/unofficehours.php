<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check that PHPMailer files exist
if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/assets/scripts/phpmailer/src/Exception.php')) {
    die('PHPMailer Exception.php not found');
}
if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/assets/scripts/phpmailer/src/PHPMailer.php')) {
    die('PHPMailer PHPMailer.php not found');
}
if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/assets/scripts/phpmailer/src/SMTP.php')) {
    die('PHPMailer SMTP.php not found');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer library files using absolute paths
require $_SERVER['DOCUMENT_ROOT'] . '/assets/scripts/phpmailer/src/Exception.php';
require $_SERVER['DOCUMENT_ROOT'] . '/assets/scripts/phpmailer/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/assets/scripts/phpmailer/src/SMTP.php';

// Configuration
$to_email = "hi@zacharykai.net";
$subject_prefix = "ZK: UnOffice Hours Booking";

// Initialize Response
$response = [
    'success' => false,
    'message' => ''
];

// Debug information to help troubleshoot
error_log('Request method: ' . $_SERVER["REQUEST_METHOD"]);
error_log('POST data: ' . print_r($_POST, true));

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data with minimal sanitization
    $name     = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $calltype    = trim($_POST['calltype'] ?? '');
    $timezone  = trim($_POST['timezone'] ?? '');
    $intro  = trim($_POST['intro'] ?? '');
    $captcha  = trim($_POST['captcha'] ?? '');

    // Validate required inputs
    if (empty($name) || empty($email) || empty($calltype) || empty($timezone) || empty($intro)) {
        $response['message'] = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Please provide a valid email address.";
    } elseif (strcasecmp($captcha, 'UnOffice Hours') !== 0) {
        $response['message'] = "Please enter the correct word to verify you're human.";
    } else {
        // Prepare email content as HTML
        $email_subject = $subject_prefix;
        $email_body  = "<html><body>";
            $email_body .= "<p><strong>New UnOffice Hours Booking</strong></p>";
            $email_body .= "<p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>";
            $email_body .= "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
            $email_body .= "<p><strong>Call Type:</strong> " . htmlspecialchars($calltype) . "</p>";
            $email_body .= "<p><strong>Timezone:</strong> " . htmlspecialchars($timezone) . "</p>";
            $email_body .= "<p><strong>Intro:</strong> " . htmlspecialchars($intro) . "</p>";
            $email_body .= "</body></html>";

            // Retrieve SMTP credentials from environment variables
            $smtpUsername = getenv('FASTMAIL_SMTP_USERNAME');
            $smtpPassword = getenv('FASTMAIL_SMTP_PASSWORD');

            // Create a new PHPMailer instance
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.fastmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtpUsername;
                $mail->Password   = $smtpPassword;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465; // Or 587 for TLS

                // Sender and recipient settings
                $mail->setFrom('site@zacharykai.net', 'ZK: UnOffice Hours');
                $mail->addReplyTo($email, $name);
                $mail->addAddress($to_email, 'Zachary Kai');

                // Email content
                $mail->isHTML(true);
                $mail->Subject = $email_subject;
                $mail->Body    = $email_body;
                // Optionally include a plain-text alternative body
                $mail->AltBody = "New UnOffice Hours Booking\nName: $name\nEmail: $email\nCall Type: $calltype\nTimezone: $timezone\nIntro: $intro";

                // Send email
                $mail->send();

                // Redirect to success page if email is sent
                header('Location: /successful');
                exit();
            } catch (Exception $e) {
                $response['message'] = "There was an error sending your submission. Mailer Error: " . $mail->ErrorInfo;
            }
        }
    }
 else {
    $response['message'] = "Invalid request method.";
}

// Output the response message if not redirected
echo $response['message'];
?>