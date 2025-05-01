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
$subject_prefix = "ZK: Oceania Web Atlas Submission";

// Initialize Response
$response = [
    'success' => false,
    'message' => ''
];

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize inputs
    $name     = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $website  = filter_input(INPUT_POST, 'website', FILTER_SANITIZE_URL);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $captcha = filter_input(INPUT_POST, 'captcha', FILTER_SANITIZE_STRING);

    // Validate required inputs
    if (empty($name) || empty($email) || empty($website) || empty($location)) {
        $response['message'] = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Please provide a valid email address.";
    } elseif (!filter_var($website, FILTER_VALIDATE_URL)) {
        $response['message'] = "Please provide a valid website URL.";
    } elseif (strcasecmp($captcha, 'Oceania Web Atlas') !== 0) {
        $response['message'] = "Please enter the correct word to verify you're human.";
    } else {

        // Prepare email content as HTML
        $email_subject = $subject_prefix;
        $email_body  = "<html><body>";
        $email_body .= "<p><strong>New Oceania Web Atlas Submission</strong></p>";
        $email_body .= "<p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>";
        $email_body .= "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
        $email_body .= "<p><strong>Website:</strong> " . htmlspecialchars($website) . "</p>";
        $email_body .= "<p><strong>Location:</strong> " . htmlspecialchars($location) . "</p>";
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
            $mail->setFrom('site@zacharykai.net', 'ZK: Oceania Web Atlas');
            $mail->addReplyTo($email, $name);
            $mail->addAddress($to_email, 'Zachary Kai');

            // Email content
            $mail->isHTML(true);
            $mail->Subject = $email_subject;
            $mail->Body    = $email_body;
            // Optionally include a plain-text alternative body
            $mail->AltBody = "New Oceania Web Atlas Submission\nName: $name\nEmail: $email\nWebsite: $website\nLocation: $location";

            // Send email
            $mail->send();

            // Redirect to success page if email is sent
            header('Location: /successful');
            exit();
        } catch (Exception $e) {
            $response['message'] = "There was an error sending your submission. Mailer Error: " . $mail->ErrorInfo;
        }
    }
} else {
    $response['message'] = "Invalid request method.";
}

// Output the response message if not redirected
echo $response['message'];
?>