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
$subject_prefix = "ZK: Guestbook Submission";

// Initialize Response
$response = [
    'success' => false,
    'message' => ''
];

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize inputs
    $name    = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email   = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $website = filter_input(INPUT_POST, 'website', FILTER_SANITIZE_URL);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    $captcha = filter_input(INPUT_POST, 'captcha', FILTER_SANITIZE_STRING);

    // Validate required inputs
    if (empty($name) || empty($email) || empty($message) || empty($captcha)) {
        $response['message'] = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Please provide a valid email address.";
    } elseif (strcasecmp($captcha, 'Guestbook') !== 0) {
        $response['message'] = "Please enter the correct word to verify you're human.";
    } else {
        // Prepare email content as HTML
        $email_subject = $subject_prefix;
        $email_body  = "<html><body>";
        $email_body .= "<p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>";
        $email_body .= "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
        if (!empty($website)) {
            $email_body .= "<p><strong>Website:</strong> " . htmlspecialchars($website) . "</p>";
        }
        $email_body .= "<p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";
        $email_body .= "</body></html>";

        // Retrieve SMTP credentials from environment variables
        $smtpUsername = getenv('FASTMAIL_SMTP_USERNAME'); // e.g., site@zacharykai.net
        $smtpPassword = getenv('FASTMAIL_SMTP_PASSWORD'); // your SMTP password

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
            $mail->setFrom('site@zacharykai.net', 'ZK: Guestbook Submission');
            $mail->addReplyTo($email, $name);
            $mail->addAddress($to_email, 'Zachary Kai');

            // Email content
            $mail->isHTML(true);
            $mail->Subject = $email_subject;
            $mail->Body    = $email_body;
            // Optionally include a plain-text alternative body
            $mail->AltBody = "Name: $name\nEmail: $email\n" . (!empty($website) ? "Website: $website\n" : "") . "\nMessage:\n$message";

            // Send email
            $mail->send();

            // Redirect to success page if email is sent
            header('Location: /successful');
            exit();
        } catch (Exception $e) {
            $response['message'] = "There was an error sending your message. Mailer Error: " . $mail->ErrorInfo;
        }
    }
} else {
    $response['message'] = "Invalid request method.";
}

// Optionally, output the response message if not redirected
echo $response['message'];
?>
