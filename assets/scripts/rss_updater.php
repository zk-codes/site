<?php
/**
 * RSS Feed Entry Adder
 * 
 * This script adds a new entry to an existing RSS feed file.
 * It can also automatically extract content from existing HTML pages.
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include simple HTML DOM parser library if you don't have it
// You'll need to download it from https://sourceforge.net/projects/simplehtmldom/ 
// or install via composer: composer require simplehtmldom/simplehtmldom
// Uncomment the line below if you've installed it
// require_once 'simple_html_dom.php';

// Configuration
$rssFile = 'feed.xml';  // Path to your RSS feed file
$siteUrl = 'https://yourdomain.com';  // Your website URL
$contentSelector = 'article, .content, #content, .post-content, main';  // CSS selector for content
$titleSelector = 'h1, .title, #title, .post-title';  // CSS selector for title
$excludeSelectors = '.comments, .sidebar, .navigation, .footer, nav, header, .ads';  // Elements to exclude

// Function to add a new entry to the RSS feed
function addRssEntry($title, $link, $description, $fullContent = null, $pubDate = null) {
    global $rssFile, $siteUrl;
    
    // If no publication date is provided, use current time
    if ($pubDate === null) {
        $pubDate = date('r'); // RFC 2822 formatted date
    }
    
    // Make sure the link is absolute
    if (strpos($link, 'http') !== 0) {
        $link = rtrim($siteUrl, '/') . '/' . ltrim($link, '/');
    }
    
    // Load the existing RSS feed
    if (file_exists($rssFile)) {
        $xml = @simplexml_load_file($rssFile);
        
        // Check if this is a valid RSS feed
        if (!$xml || !isset($xml->channel)) {
            // If the file exists but is invalid, create a new one
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/"><channel><title>Your Website</title><link>' . $siteUrl . '</link><description>Your website description</description><language>en-us</language></channel></rss>');
        }
    } else {
        // Create a new RSS feed if it doesn't exist
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/"><channel><title>Your Website</title><link>' . $siteUrl . '</link><description>Your website description</description><language>en-us</language></channel></rss>');
    }
    
    // Create a new item node
    $item = $xml->channel->addChild('item');
    $item->addChild('title', htmlspecialchars($title));
    $item->addChild('link', $link);
    $item->addChild('description', htmlspecialchars($description));
    $item->addChild('pubDate', $pubDate);
    $item->addChild('guid', $link)->addAttribute('isPermaLink', 'true');
    
    // Add full content if provided
    if ($fullContent !== null) {
        try {
            // Use the content:encoded element for full content
            $content = $item->addChild('content:encoded', '', 'http://purl.org/rss/1.0/modules/content/');
            $dom = dom_import_simplexml($content);
            $owner = $dom->ownerDocument;
            $dom->appendChild($owner->createCDATASection($fullContent));
        } catch (Exception $e) {
            // If there's an error, try with a simpler approach
            $content = $item->addChild('content:encoded', htmlspecialchars($fullContent), 'http://purl.org/rss/1.0/modules/content/');
        }
    }
    
    // Format the XML with proper indentation
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
    
    // Save the updated RSS feed
    if (file_put_contents($rssFile, $dom->saveXML())) {
        return true;
    } else {
        return false;
    }
}

/**
 * Extract content from an HTML page
 * 
 * @param string $url URL or file path of the HTML page
 * @return array Associative array with title, description, and content
 */
function extractContentFromHtml($url) {
    global $siteUrl, $contentSelector, $titleSelector, $excludeSelectors;
    
    try {
        // Determine if this is a local file or a URL
        $isLocalFile = !preg_match('~^https?://~i', $url);
        
        // Make sure we have the full URL for remote files
        if (!$isLocalFile && strpos($url, 'http') !== 0) {
            $url = rtrim($siteUrl, '/') . '/' . ltrim($url, '/');
        }
        
        // For local files, make sure we have the full path
        if ($isLocalFile) {
            // If it's a relative path, convert to absolute path
            if (strpos($url, '/') !== 0 && !preg_match('~^[A-Za-z]:[\\/]~', $url)) {
                $url = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($url, '/');
            }
        }
        
        // Try to get the HTML content
        $html = file_get_contents($url);
        if (!$html) {
            return ['error' => 'Could not fetch the HTML content'];
        }
        
        // For simple extraction without the HTML DOM parser
        $result = [];
        
        // Extract title
        preg_match('/<title>(.*?)<\/title>/is', $html, $titleMatch);
        $result['title'] = $titleMatch[1] ?? '';
        
        // Try to extract better title from h1 if available
        preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $h1Match);
        if (!empty($h1Match[1])) {
            $result['title'] = strip_tags($h1Match[1]);
        }
        
        // Extract meta description
        preg_match('/<meta\s+name=["\']description["\']\s+content=["\']([^"\']+)["\'].*?>/is', $html, $descMatch);
        $result['description'] = $descMatch[1] ?? substr(strip_tags($html), 0, 200) . '...';
        
        // For content extraction, we're doing a simple approach
        // For better results, use the HTML DOM parser library
        
        // Remove script, style, and other non-content elements
        $cleanHtml = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $cleanHtml = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $cleanHtml);
        
        // Try to extract content from main content area
        preg_match('/<article[^>]*>(.*?)<\/article>/is', $cleanHtml, $articleMatch);
        
        if (!empty($articleMatch[1])) {
            $result['content'] = $articleMatch[1];
        } else {
            preg_match('/<div\s+class=["\']content["\'][^>]*>(.*?)<\/div>/is', $cleanHtml, $contentMatch);
            $result['content'] = $contentMatch[1] ?? '';
            
            if (empty($result['content'])) {
                preg_match('/<div\s+id=["\']content["\'][^>]*>(.*?)<\/div>/is', $cleanHtml, $contentMatch);
                $result['content'] = $contentMatch[1] ?? '';
            }
            
            // If still empty, use the whole body content
            if (empty($result['content'])) {
                preg_match('/<body[^>]*>(.*?)<\/body>/is', $cleanHtml, $bodyMatch);
                $result['content'] = $bodyMatch[1] ?? '';
            }
        }
        
        // Clean up the content
        $result['content'] = preg_replace('/<header[^>]*>.*?<\/header>/is', '', $result['content']);
        $result['content'] = preg_replace('/<footer[^>]*>.*?<\/footer>/is', '', $result['content']);
        $result['content'] = preg_replace('/<nav[^>]*>.*?<\/nav>/is', '', $result['content']);
        
        // If HTML DOM parser is available, use it for better extraction
        // This part is commented out by default to avoid errors if the library isn't installed
        /*
        if (function_exists('str_get_html')) {
            $dom = str_get_html($html);
            if ($dom) {
                // Extract title
                if ($dom->find($titleSelector, 0)) {
                    $result['title'] = trim($dom->find($titleSelector, 0)->plaintext);
                }
                
                // Extract content
                $content = '';
                foreach ($dom->find($contentSelector) as $element) {
                    // Remove excluded elements
                    foreach ($element->find($excludeSelectors) as $exclude) {
                        $exclude->outertext = '';
                    }
                    $content .= $element->innertext;
                }
                
                if (!empty($content)) {
                    $result['content'] = $content;
                }
                
                // Clean up
                $dom->clear();
                unset($dom);
            }
        }
        */
        
        return $result;
    } catch (Exception $e) {
        return [
            'error' => 'Error extracting content: ' . $e->getMessage(),
            'title' => '',
            'description' => '',
            'content' => ''
        ];
    }
}

// Example usage:
// Uncomment and modify the following line to add an entry manually
/*
if (addRssEntry(
    'New Blog Post Title', 
    'blog/new-post.html', 
    'This is a summary of my awesome new blog post.',
    '<p>This is the full content of my awesome new blog post. It can include <strong>HTML</strong> formatting.</p><p>Multiple paragraphs are supported too.</p>',
    date('r') // Current date in RFC 2822 format
)) {
    echo "Entry added successfully!";
} else {
    echo "Failed to add entry.";
}
*/

// To use this script via form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_rss'])) {
    $title = $_POST['title'] ?? '';
    $link = $_POST['link'] ?? '';
    $description = $_POST['description'] ?? '';
    $fullContent = $_POST['full_content'] ?? null;
    $autoExtract = isset($_POST['auto_extract']) && $_POST['auto_extract'] == '1';
    
    // Auto-extract content if requested
    if ($autoExtract && !empty($link)) {
        $extracted = extractContentFromHtml($link);
        
        // Use extracted title if none provided
        if (empty($title) && !empty($extracted['title'])) {
            $title = $extracted['title'];
        }
        
        // Use extracted description if none provided
        if (empty($description) && !empty($extracted['description'])) {
            $description = $extracted['description'];
        }
        
        // Use extracted content
        if (!empty($extracted['content'])) {
            $fullContent = $extracted['content'];
        }
    }
    
    if (!empty($title) && !empty($link)) {
        $result = addRssEntry($title, $link, $description, $fullContent);
        $message = $result ? "RSS entry added successfully!" : "Failed to add RSS entry.";
    } else {
        $message = "Title and link are required!";
    }
}
?>

<?php if ($_SERVER['REQUEST_METHOD'] === 'GET' || isset($message)): ?>
<!DOCTYPE html>
<html>
<head>
<title>Add RSS Entry</title>
<link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <h1>Add New RSS Entry</h1>
    
    <?php if (isset($message)): ?>
    <div class="message <?php echo $result ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>
    
    <form method="post" action="">
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
        </div>
        
        <div class="form-group">
            <label for="link">Link (relative or absolute URL):</label>
            <input type="text" id="link" name="link" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description (Summary):</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>
        
        <div class="form-group">
            <label for="full_content">Full Content (HTML supported):</label>
            <textarea id="full_content" name="full_content" rows="10"></textarea>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="auto_extract" value="1"> 
                Auto-extract content from the linked page
            </label>
            <p class="help-text" style="font-size: 0.8em; color: #666;">
                If checked, the script will attempt to extract the title, description, and full content from the linked page. 
                This will override any manually entered values.
            </p>
        </div>
        
        <button type="submit" name="add_rss">Add RSS Entry</button>
    </form>
</body>
</html>
<?php endif; ?>