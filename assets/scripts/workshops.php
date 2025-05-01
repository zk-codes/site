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
$subject_prefix = "ZK: Workshop Inquiry";

// Initialize Response
$response = [
    'success' => false,
    'message' => ''
];

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize inputs
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $group_name = filter_input(INPUT_POST, 'group_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $workshop_type = filter_input(INPUT_POST, 'workshop_type', FILTER_SANITIZE_STRING);
    $participants = filter_input(INPUT_POST, 'participants', FILTER_SANITIZE_NUMBER_INT);
    $age_range = filter_input(INPUT_POST, 'age_range', FILTER_SANITIZE_STRING);
    $potential_dates = filter_input(INPUT_POST, 'potential_dates', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    $captcha = filter_input(INPUT_POST, 'captcha', FILTER_SANITIZE_STRING);

    // Validate required inputs
    if (empty($name) || empty($group_name) || empty($email) || empty($workshop_type) || 
        empty($participants) || empty($age_range) || empty($message) || empty($captcha)) {
        $response['message'] = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Please provide a valid email address.";
    } elseif (strcasecmp($captcha, 'Workshops') !== 0) {
        // Check if the captcha text matches "Workshops" (case-insensitive)
        $response['message'] = "Please enter the correct word to verify you're human.";
    } else {
        // Prepare email content as HTML
        $email_subject = "$subject_prefix - $workshop_type";
        $email_body  = "<html><body>";
        $email_body .= "<h2>Workshop Inquiry - $workshop_type</h2>";
        $email_body .= "<p><strong>Contact Name:</strong> " . htmlspecialchars($name) . "</p>";
        $email_body .= "<p><strong>Group/Organization:</strong> " . htmlspecialchars($group_name) . "</p>";
        $email_body .= "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
        $email_body .= "<p><strong>Workshop Type:</strong> " . htmlspecialchars($workshop_type) . "</p>";
        $email_body .= "<p><strong>Number of Participants:</strong> " . htmlspecialchars($participants) . "</p>";
        $email_body .= "<p><strong>Age Range:</strong> " . htmlspecialchars($age_range) . "</p>";
        if (!empty($potential_dates)) {
            $email_body .= "<p><strong>Potential Dates:</strong> " . htmlspecialchars($potential_dates) . "</p>";
        }
        $email_body .= "<p><strong>Additional Information:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";
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
            $mail->setFrom('site@zacharykai.net', 'ZK: Workshop Inquiry');
            $mail->addReplyTo($email, $name);
            $mail->addAddress($to_email, 'Zachary Kai');

            // Email content
            $mail->isHTML(true);
            $mail->Subject = $email_subject;
            $mail->Body    = $email_body;
            
            // Optionally include a plain-text alternative body
            $plainText = "Workshop Inquiry - $workshop_type\n\n";
            $plainText .= "Contact Name: $name\n";
            $plainText .= "Group/Organization: $group_name\n";
            $plainText .= "Email: $email\n";
            $plainText .= "Workshop Type: $workshop_type\n";
            $plainText .= "Number of Participants: $participants\n";
            $plainText .= "Age Range: $age_range\n";
            if (!empty($potential_dates)) {
                $plainText .= "Potential Dates: $potential_dates\n";
            }
            $plainText .= "\nAdditional Information:\n$message";
            $mail->AltBody = $plainText;

            // Send email
            $mail->send();

            // Redirect to success page if email is sent
            header('Location: /successful');
            exit();
        } catch (Exception $e) {
            $response['message'] = "There was an error sending your workshop inquiry. Mailer Error: " . $mail->ErrorInfo;
        }
    }
} else {
    $response['message'] = "Invalid request method.";
}

// Optionally, output the response message if not redirected
echo $response['message'];
?>