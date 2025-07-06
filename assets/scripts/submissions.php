<?php

// Error Reporting

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check PHPMailer Files Exist

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

require $_SERVER['DOCUMENT_ROOT'] . '/assets/scripts/phpmailer/src/Exception.php';
require $_SERVER['DOCUMENT_ROOT'] . '/assets/scripts/phpmailer/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/assets/scripts/phpmailer/src/SMTP.php';

// Configuration

$to_email = "hi@zacharykai.net";

// Initialize Response

$response = [
    'success' => false,
    'message' => ''
];

// Check Form Was Submitted Via POST

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Determine Form Type Based On The Hidden Field

    $form_type = $_POST['form_type'] ?? '';
    
    $valid_form = true;
    $email_subject = "";
    $email_body = "";
    $sender_email = "";
    $sender_name = "";
    $expected_captcha = "";
    
    // Process Based On Form Type

    switch ($form_type) {

        // Fanfic Writers Collective

        case 'fanfic':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $website = trim($_POST['website'] ?? '');
            $fandoms = trim($_POST['fandoms'] ?? '');
            $captcha = trim($_POST['captcha'] ?? '');
            $expected_captcha = 'Fanfic Writers Collective';
            
            if (empty($name) || empty($email) || empty($website) || empty($fandoms)) {
                $response['message'] = "Please fill in all required fields.";
                $valid_form = false;
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response['message'] = "Please provide a valid email address.";
                $valid_form = false;
            } elseif (!preg_match('/^https?:\/\/.+\..+/i', $website)) {
                $response['message'] = "Please provide a valid website URL starting with http:// or https://";
                $valid_form = false;
            } else {
                $email_subject = "Fanfic Writers Collective Submission";
                $email_body .= "Name: $name\n";
                $email_body .= "Email: $email\n";
                $email_body .= "Website: $website\n";
                $email_body .= "Fandoms: $fandoms\n";
                $sender_email = $email;
                $sender_name = $name;
            }
            break;
        
        // Guestbook

        case 'guestbook':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $website = trim($_POST['website'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $captcha = trim($_POST['captcha'] ?? '');
            $expected_captcha = 'Guestbook';
            
            if (empty($name) || empty($email) || empty($message)) {
                $response['message'] = "Please fill in all required fields.";
                $valid_form = false;
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response['message'] = "Please provide a valid email address.";
                $valid_form = false;
            } else {
                $email_subject = "Guestbook Submission";
                $email_body .= "Name: $name\n";
                $email_body .= "Email: $email\n";
                if (!empty($website)) {
                    $email_body .= "Website: $website\n";
                }
                $email_body .= "Message:\n$message\n";
                $sender_email = $email;
                $sender_name = $name;
            }
            break;
        
        // Oceania Web Directory

        case 'oceania':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $website = trim($_POST['website'] ?? '');
            $location = trim($_POST['location'] ?? '');
            $captcha = trim($_POST['captcha'] ?? '');
            $expected_captcha = 'Oceania Web Atlas';
            
            if (empty($name) || empty($email) || empty($website) || empty($location)) {
                $response['message'] = "Please fill in all required fields.";
                $valid_form = false;
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response['message'] = "Please provide a valid email address.";
                $valid_form = false;
            } elseif (!filter_var($website, FILTER_VALIDATE_URL)) {
                $response['message'] = "Please provide a valid website URL.";
                $valid_form = false;
            } else {
                $email_subject = "Oceania Web Atlas Submission";
                $email_body .= "Name: $name\n";
                $email_body .= "Email: $email\n";
                $email_body .= "Website: $website\n";
                $email_body .= "Location: $location\n";
                $sender_email = $email;
                $sender_name = $name;
            }
            break;
        
        // Workshops

        case 'workshops':
            $name = trim($_POST['name'] ?? '');
            $group_name = trim($_POST['group_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $workshop_type = trim($_POST['workshop_type'] ?? '');
            $participants = trim($_POST['participants'] ?? '');
            $age_range = trim($_POST['age_range'] ?? '');
            $potential_dates = trim($_POST['potential_dates'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $captcha = trim($_POST['captcha'] ?? '');
            $expected_captcha = 'Workshops';
            
            if (empty($name) || empty($group_name) || empty($email) || empty($workshop_type) || 
                empty($participants) || empty($age_range) || empty($message)) {
                $response['message'] = "Please fill in all required fields.";
                $valid_form = false;
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response['message'] = "Please provide a valid email address.";
                $valid_form = false;
            } else {
                $email_subject = "Workshop Inquiry - $workshop_type";
                $email_body .= "Contact Name: $name\n";
                $email_body .= "Group/Organization: $group_name\n";
                $email_body .= "Email: $email\n";
                $email_body .= "Workshop Type: $workshop_type\n";
                $email_body .= "Number of Participants: $participants\n";
                $email_body .= "Age Range: $age_range\n";
                if (!empty($potential_dates)) {
                    $email_body .= "Potential Dates: $potential_dates\n";
                }
                $email_body .= "\nAdditional Information:\n$message\n";
                $sender_email = $email;
                $sender_name = $name;
            }
            break;
        
        // Unoffice Hours

        case 'unoffice':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $calltype = trim($_POST['calltype'] ?? '');
            $timezone = trim($_POST['timezone'] ?? '');
            $intro = trim($_POST['intro'] ?? '');
            $captcha = trim($_POST['captcha'] ?? '');
            $expected_captcha = 'UnOffice Hours';
            
            if (empty($name) || empty($email) || empty($calltype) || empty($timezone) || empty($intro)) {
                $response['message'] = "Please fill in all required fields.";
                $valid_form = false;
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response['message'] = "Please provide a valid email address.";
                $valid_form = false;
            } else {
                $email_subject = "UnOffice Hours Booking";
                $email_body .= "Name: $name\n";
                $email_body .= "Email: $email\n";
                $email_body .= "Call Type: $calltype\n";
                $email_body .= "Timezone: $timezone\n";
                $email_body .= "Intro: $intro\n";
                $sender_email = $email;
                $sender_name = $name;
            }
            break;
        
        // Queer Web Directory

        case 'queer':
            $name = trim($_POST['name'] ?? '');
            $pronouns = trim($_POST['pronouns'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $website = trim($_POST['website'] ?? '');
            $captcha = trim($_POST['captcha'] ?? '');
            $expected_captcha = 'Queer Web Directory';
            
            if (empty($name) || empty($email) || empty($website) || empty($pronouns)) {
                $response['message'] = "Please fill in all required fields.";
                $valid_form = false;
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response['message'] = "Please provide a valid email address.";
                $valid_form = false;
            } elseif (!preg_match('/^https?:\/\/.+\..+/i', $website)) {
                $response['message'] = "Please provide a valid website URL starting with http:// or https://";
                $valid_form = false;
            } else {
                $email_subject = "Queer Web Directory Submission";
                $email_body .= "Name: $name\n";
                $email_body .= "Pronouns: $pronouns\n";
                $email_body .= "Email: $email\n";
                $email_body .= "Website: $website\n";
                $sender_email = $email;
                $sender_name = $name;
            }
            break;
        
        // Star Wars Webring

        case 'starwars':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $site_name = trim($_POST['site_name'] ?? '');
            $site_url = trim($_POST['site_url'] ?? '');
            $favorite_character = trim($_POST['favorite_character'] ?? '');
            $captcha = trim($_POST['captcha'] ?? '');
            $expected_captcha = 'Star Wars Webring';
            
            if (empty($name) || empty($email) || empty($site_name) || empty($site_url)) {
                $response['message'] = "Please fill in all required fields.";
                $valid_form = false;
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response['message'] = "Please provide a valid email address.";
                $valid_form = false;
            } elseif (!preg_match('/^https?:\/\/.+\..+/i', $site_url)) {
                $response['message'] = "Please provide a valid website URL starting with http:// or https://";
                $valid_form = false;
            } else {
                $email_subject = "Star Wars Webring Submission";
                $email_body .= "Name: $name\n";
                $email_body .= "Email: $email\n";
                $email_body .= "Site Name: $site_name\n";
                $email_body .= "Site URL: $site_url\n";
                if (!empty($favorite_character)) {
                    $email_body .= "Favorite Star Wars Character: $favorite_character\n";
                }
                $sender_email = $email;
                $sender_name = $name;
            }
            break;
            
        default:
            $response['message'] = "Invalid form type.";
            $valid_form = false;
    }
    
    // Check Captcha

    if ($valid_form && !empty($expected_captcha)) {
        $captcha = trim($_POST['captcha'] ?? '');
        if (strcasecmp($captcha, $expected_captcha) !== 0) {
            $response['message'] = "Please enter the page's title.";
            $valid_form = false;
        }
    }
    
    // Send Email If Form Is Valid

    if ($valid_form) {

        // Retrieve SMTP Credentials

        $smtpUsername = getenv('FASTMAIL_SMTP_USERNAME');
        $smtpPassword = getenv('FASTMAIL_SMTP_PASSWORD');
        
        // Create New PHPMailer Instance

        $mail = new PHPMailer(true);
        try {

            // Server settings

            $mail->isSMTP();
            $mail->Host       = 'smtp.fastmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUsername;
            $mail->Password   = $smtpPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            
            // Sender & Recipient settings

            $mail->setFrom('site@zacharykai.net', 'ZK Site');
            if (!empty($sender_email) && !empty($sender_name)) {
                $mail->addReplyTo($sender_email, $sender_name);
            }
            $mail->addAddress($to_email, 'Zachary Kai');
            
            $mail->isHTML(false);
            $mail->Subject = $email_subject;
            $mail->Body    = $email_body;
            
            $mail->send();
            
            // Redirect To Success Page If Email Sent

            header('Location: /successful');
            exit();

        } catch (Exception $e) {
            $response['message'] = "There was an error sending your submission. Mailer Error: " . $mail->ErrorInfo;
        }
    }

} else {
    $response['message'] = "Invalid request method.";
}

// Output Response Message If Not Redirected

echo $response['message'];
?>