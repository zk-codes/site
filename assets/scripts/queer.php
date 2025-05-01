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
$subject_prefix = "ZK: Queer Web Directory Submission";

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
    $pronouns = trim($_POST['pronouns'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $website  = trim($_POST['website'] ?? '');
    $captcha  = trim($_POST['captcha'] ?? '');

    // Validate required inputs
    if (empty($name) || empty($email) || empty($website) || empty($pronouns)) {
        $response['message'] = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Please provide a valid email address.";
    } elseif (strcasecmp($captcha, 'Queer Web Directory') !== 0) {
        $response['message'] = "Please enter the correct word to verify you're human.";
    } else {
        // Modified website validation to be more permissive
        if (!preg_match('/^https?:\/\/.+\..+/i', $website)) {
            $response['message'] = "Please provide a valid website URL starting with http:// or https://";
        } else {
            // Prepare email content as HTML
            $email_subject = $subject_prefix;
            $email_body  = "<html><body>";
            $email_body .= "<p><strong>New Queer Web Directory Submission</strong></p>";
            $email_body .= "<p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>";
            $email_body .= "<p><strong>Pronouns:</strong> " . htmlspecialchars($pronouns) . "</p>";
            $email_body .= "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
            $email_body .= "<p><strong>Website:</strong> " . htmlspecialchars($website) . "</p>";
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
                $mail->setFrom('site@zacharykai.net', 'ZK: Queer Web Directory');
                $mail->addReplyTo($email, $name);
                $mail->addAddress($to_email, 'Zachary Kai');

                // Email content
                $mail->isHTML(true);
                $mail->Subject = $email_subject;
                $mail->Body    = $email_body;
                // Optionally include a plain-text alternative body
                $mail->AltBody = "New Queer Web Directory Submission\nName: $name\nPronouns: $pronouns\nEmail: $email\nWebsite: $website";

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
} else {
    $response['message'] = "Invalid request method.";
}

// Output the response message if not redirected
echo $response['message'];
?>