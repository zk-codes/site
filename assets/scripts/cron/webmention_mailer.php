<?php

/**
 * Webmentions Email Notifications: gets new webmentions from webmentions.io and emails them to site owner using PHPMailer.
 * 
 * Required environment variables:
 * - WEBMENTION_API_TOKEN: Your webmention.io API token
 * - FASTMAIL_SMTP_USERNAME: Your Fastmail SMTP username
 * - FASTMAIL_SMTP_PASSWORD: Your Fastmail SMTP password
 */

// Include PHPMailer files directly since we're not using Composer's autoloader
require __DIR__ . '/../phpmailer/src/Exception.php';
require __DIR__ . '/../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configuration
$config = [
    // Your webmention.io API token from environment variable
    'token' => getenv('WEBMENTION_API_TOKEN'),
    
    // Your domain (that receives webmentions)
    'domain' => 'zacharykai.net',
    
    // Email configuration
    'email' => [
        'from' => 'site@zachary.net',
        'from_name' => 'ZK Site',
        'to' => 'hi@zacharykai.net',
        'subject' => 'ZK: Webmention Submission',
    ],
    
    // SMTP configuration
    'smtp' => [
        'host' => 'smtp.fastmail.com',
        'port' => 587,
        'username' => getenv('FASTMAIL_SMTP_USERNAME'),
        'password' => getenv('FASTMAIL_SMTP_PASSWORD'),
        'security' => 'tls',
    ],
    
    // Path to store the timestamp of the last check
    'last_check_file' => __DIR__ . '/last_webmention_check.txt',
];

// Get the timestamp of the last check
$lastCheck = file_exists($config['last_check_file']) 
    ? trim(file_get_contents($config['last_check_file'])) 
    : (time() - 86400); // Default to 24 hours ago if file doesn't exist

// Fetch new mentions from webmention.io
$newMentions = fetchNewWebmentions($config['token'], $config['domain'], $lastCheck);

// If we have new mentions, send an email
if (!empty($newMentions)) {
    sendEmailNotification($newMentions, $config);
}

// Update the last check timestamp
file_put_contents($config['last_check_file'], time());

/**
 * Fetch new webmentions from webmention.io API
 * 
 * @param string $token API token for webmention.io
 * @param string $domain Your domain
 * @param int $since Unix timestamp to fetch mentions after
 * @return array Array of new webmentions
 */
function fetchNewWebmentions($token, $domain, $since) {
    $url = "https://webmention.io/api/mentions.jf2?domain={$domain}&token={$token}&since=" . date('c', $since);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode != 200) {
        error_log("Error fetching webmentions: HTTP {$httpCode}");
        return [];
    }
    
    $data = json_decode($response, true);
    
    if (!isset($data['children'])) {
        error_log("No 'children' field in webmention.io response");
        return [];
    }
    
    return $data['children'];
}

/**
 * Send email notification of new webmentions
 * 
 * @param array $mentions Array of webmention data
 * @param array $config Script configuration
 * @return bool Success status
 */
function sendEmailNotification($mentions, $config) {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['smtp']['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp']['username'];
        $mail->Password = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['security'];
        $mail->Port = $config['smtp']['port'];
        
        // Recipients
        $mail->setFrom($config['email']['from'], $config['email']['from_name']);
        $mail->addAddress($config['email']['to']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $config['email']['subject'] . ' (' . count($mentions) . ')';
        
        // Build HTML email content
        $htmlBody = '<h1>New Webmentions for ' . htmlspecialchars($config['domain']) . '</h1>';
        $htmlBody .= '<p>You have received ' . count($mentions) . ' new webmention(s):</p>';
        
        $textBody = "New Webmentions for {$config['domain']}\n\n";
        $textBody .= "You have received " . count($mentions) . " new webmention(s):\n\n";
        
        foreach ($mentions as $mention) {
            $htmlBody .= formatMentionHtml($mention);
            $textBody .= formatMentionText($mention);
        }
        
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error sending email: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Format a webmention for HTML email
 * 
 * @param array $mention Webmention data
 * @return string HTML for this mention
 */
function formatMentionHtml($mention) {
    $html = '<div style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">';
    
    // Type of mention
    $type = isset($mention['wm-property']) ? $mention['wm-property'] : 'mention';
    $html .= '<p><strong>Type:</strong> ' . htmlspecialchars(ucfirst($type)) . '</p>';
    
    // Source of the mention
    $html .= '<p><strong>Source:</strong> <a href="' . htmlspecialchars($mention['url']) . '">' . 
             htmlspecialchars($mention['url']) . '</a></p>';
    
    // Target on your site
    if (isset($mention['wm-target'])) {
        $html .= '<p><strong>Target:</strong> <a href="' . htmlspecialchars($mention['wm-target']) . '">' . 
                 htmlspecialchars($mention['wm-target']) . '</a></p>';
    }
    
    // Author
    if (isset($mention['author'])) {
        $html .= '<p><strong>Author:</strong> ';
        if (isset($mention['author']['url'])) {
            $html .= '<a href="' . htmlspecialchars($mention['author']['url']) . '">';
        }
        $html .= htmlspecialchars($mention['author']['name'] ?? 'Unknown');
        if (isset($mention['author']['url'])) {
            $html .= '</a>';
        }
        $html .= '</p>';
    }
    
    // Content
    if (isset($mention['content'])) {
        $html .= '<p><strong>Content:</strong></p>';
        $html .= '<blockquote style="margin-left: 20px; padding-left: 10px; border-left: 3px solid #ddd;">';
        $html .= htmlspecialchars($mention['content']['text'] ?? $mention['content']['html'] ?? '');
        $html .= '</blockquote>';
    }
    
    // Published time
    if (isset($mention['published'])) {
        $html .= '<p><strong>Published:</strong> ' . htmlspecialchars($mention['published']) . '</p>';
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Format a webmention for text email
 * 
 * @param array $mention Webmention data
 * @return string Text for this mention
 */
function formatMentionText($mention) {
    $text = "----------------------\n";
    
    // Type of mention
    $type = isset($mention['wm-property']) ? $mention['wm-property'] : 'mention';
    $text .= "Type: " . ucfirst($type) . "\n";
    
    // Source of the mention
    $text .= "Source: " . ($mention['url'] ?? 'Unknown') . "\n";
    
    // Target on your site
    if (isset($mention['wm-target'])) {
        $text .= "Target: " . $mention['wm-target'] . "\n";
    }
    
    // Author
    if (isset($mention['author'])) {
        $text .= "Author: " . ($mention['author']['name'] ?? 'Unknown');
        if (isset($mention['author']['url'])) {
            $text .= " (" . $mention['author']['url'] . ")";
        }
        $text .= "\n";
    }
    
    // Content
    if (isset($mention['content'])) {
        $text .= "Content:\n";
        $text .= "> " . str_replace("\n", "\n> ", $mention['content']['text'] ?? '') . "\n";
    }
    
    // Published time
    if (isset($mention['published'])) {
        $text .= "Published: " . $mention['published'] . "\n";
    }
    
    $text .= "\n";
    return $text;
}