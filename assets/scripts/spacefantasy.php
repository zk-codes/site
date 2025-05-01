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
$subject_prefix = "ZK: Space Fantasy Survey Submission";

// Initialize Response
$response = [
    'success' => false,
    'message' => ''
];

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize inputs
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $read_frequency = filter_input(INPUT_POST, 'read-frequency', FILTER_SANITIZE_STRING);
    $books_per_year = filter_input(INPUT_POST, 'number', FILTER_SANITIZE_NUMBER_INT);
    $format = filter_input(INPUT_POST, 'format', FILTER_SANITIZE_STRING);
    $discovery = filter_input(INPUT_POST, 'discovery', FILTER_SANITIZE_STRING);
    
    // Get checkbox arrays
    $fantasy_subgenres = isset($_POST['fantasy']) ? $_POST['fantasy'] : [];
    $scifi_subgenres = isset($_POST['scifi']) ? $_POST['scifi'] : [];
    $themes = isset($_POST['themes']) ? $_POST['themes'] : [];
    
    // Get dropdown selections
    $series_preference = filter_input(INPUT_POST, 'series-preference', FILTER_SANITIZE_STRING);
    $character_dynamics = filter_input(INPUT_POST, 'character-dynamics', FILTER_SANITIZE_STRING);
    $pov_preference = filter_input(INPUT_POST, 'pov-preference', FILTER_SANITIZE_STRING);
    $perspective = filter_input(INPUT_POST, 'perspective', FILTER_SANITIZE_STRING);
    $setting_memorable = filter_input(INPUT_POST, 'setting-memorable', FILTER_SANITIZE_STRING);
    $world_preference = filter_input(INPUT_POST, 'world-preference', FILTER_SANITIZE_STRING);
    $length_preference = filter_input(INPUT_POST, 'length-preference', FILTER_SANITIZE_STRING);
    $space_fantasy_draw = filter_input(INPUT_POST, 'space-fantasy-draw', FILTER_SANITIZE_STRING);

    // Validate required inputs
    if (empty($name) || empty($email)) {
        $response['message'] = "Please fill in your name and email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Please provide a valid email address.";
    } else {
        // Prepare email content as HTML
        $email_subject = $subject_prefix;
        $email_body  = "<html><body>";
        $email_body .= "<h2>Space Fantasy Reader Survey Submission</h2>";
        $email_body .= "<p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>";
        $email_body .= "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
        $email_body .= "<p><strong>Reading Frequency:</strong> " . htmlspecialchars($read_frequency) . "</p>";
        $email_body .= "<p><strong>Books per Year:</strong> " . htmlspecialchars($books_per_year) . "</p>";
        $email_body .= "<p><strong>Preferred Format:</strong> " . htmlspecialchars($format) . "</p>";
        $email_body .= "<p><strong>Book Discovery Method:</strong> " . htmlspecialchars($discovery) . "</p>";
        
        // Fantasy subgenres
        $email_body .= "<p><strong>Fantasy Subgenres:</strong> ";
        if (!empty($fantasy_subgenres)) {
            $email_body .= htmlspecialchars(implode(", ", $fantasy_subgenres));
        } else {
            $email_body .= "None selected";
        }
        $email_body .= "</p>";
        
        // Sci-Fi subgenres
        $email_body .= "<p><strong>Sci-Fi Subgenres:</strong> ";
        if (!empty($scifi_subgenres)) {
            $email_body .= htmlspecialchars(implode(", ", $scifi_subgenres));
        } else {
            $email_body .= "None selected";
        }
        $email_body .= "</p>";
        
        // Themes
        $email_body .= "<p><strong>Preferred Themes:</strong> ";
        if (!empty($themes)) {
            $email_body .= htmlspecialchars(implode(", ", $themes));
        } else {
            $email_body .= "None selected";
        }
        $email_body .= "</p>";
        
        // Dropdown selections
        $email_body .= "<p><strong>Series Preference:</strong> " . htmlspecialchars($series_preference) . "</p>";
        $email_body .= "<p><strong>Character Dynamics:</strong> " . nl2br(htmlspecialchars($character_dynamics)) . "</p>";
        $email_body .= "<p><strong>POV Preference:</strong> " . htmlspecialchars($pov_preference) . "</p>";
        $email_body .= "<p><strong>Perspective Preference:</strong> " . htmlspecialchars($perspective) . "</p>";
        $email_body .= "<p><strong>What Makes a Setting Memorable:</strong> " . nl2br(htmlspecialchars($setting_memorable)) . "</p>";
        $email_body .= "<p><strong>World Preference:</strong> " . htmlspecialchars($world_preference) . "</p>";
        $email_body .= "<p><strong>Length Preference:</strong> " . htmlspecialchars($length_preference) . "</p>";
        $email_body .= "<p><strong>Space Fantasy Draw:</strong> " . nl2br(htmlspecialchars($space_fantasy_draw)) . "</p>";
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
            $mail->setFrom('site@zacharykai.net', 'ZK: Space Fantasy Survey');
            $mail->addReplyTo($email, $name);
            $mail->addAddress($to_email, 'Zachary Kai');

            // Email content
            $mail->isHTML(true);
            $mail->Subject = $email_subject;
            $mail->Body    = $email_body;
            
            // Create plain text version
            $mail->AltBody = "Space Fantasy Reader Survey Submission\n\n"
                . "Name: $name\n"
                . "Email: $email\n"
                . "Reading Frequency: $read_frequency\n"
                . "Books per Year: $books_per_year\n"
                . "Preferred Format: $format\n"
                . "Book Discovery Method: $discovery\n"
                . "Fantasy Subgenres: " . (!empty($fantasy_subgenres) ? implode(", ", $fantasy_subgenres) : "None selected") . "\n"
                . "Sci-Fi Subgenres: " . (!empty($scifi_subgenres) ? implode(", ", $scifi_subgenres) : "None selected") . "\n"
                . "Preferred Themes: " . (!empty($themes) ? implode(", ", $themes) : "None selected") . "\n"
                . "Series Preference: $series_preference\n"
                . "Character Dynamics: $character_dynamics\n"
                . "POV Preference: $pov_preference\n"
                . "Perspective Preference: $perspective\n"
                . "What Makes a Setting Memorable: $setting_memorable\n"
                . "World Preference: $world_preference\n"
                . "Length Preference: $length_preference\n"
                . "Space Fantasy Draw: $space_fantasy_draw";

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