<?php

// Set Content Header To XML

header('Content-Type: application/xml; charset=utf-8');

// --- Configuration ---

$notesDirectory = $_SERVER['DOCUMENT_ROOT'] . '/notes/';
$additionalFiles = ['/newsletter.html', '/jots.html'];

$cacheDirectory = $_SERVER['DOCUMENT_ROOT'] . '/assets/cache/';
$cacheFile = $cacheDirectory . 'rss.xml';

$siteBaseUrl = 'https://zacharykai.net/';
$feedTitle = 'Zachary Kai';
$feedLink = $siteBaseUrl;
$feedDescription = 'Hi! I\'m Zachary Kai, a space fantasy writer, offbeat queer, twenty-something, curiosity enthusiast, and traveler.';
$feedLanguage = 'en-us';

// Create Cache Directory
if (!is_dir($cacheDirectory)) {
    mkdir($cacheDirectory, 0755, true);
}

// --- Caching Logic: Regenerate only if source files are newer than cached RSS ---
$latestSourceModificationTime = 0;
$sourceFiles = [];

// Get all HTML files from notes directory
if (is_dir($notesDirectory)) {
    $iterator = new DirectoryIterator($notesDirectory);
    foreach ($iterator as $fileinfo) {
        if ($fileinfo->isFile() && $fileinfo->getExtension() === 'html') {
            $filePath = $fileinfo->getPathname();
            $sourceFiles[] = $filePath;
            $latestSourceModificationTime = max($latestSourceModificationTime, $fileinfo->getMTime());
        }
    }
} else {
    error_log("Notes directory not found: " . $notesDirectory);
    echo '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>Error</title><description>Notes directory not found.</description></channel></rss>';
    exit;
}

// Add modification times for additional files
foreach ($additionalFiles as $relPath) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $relPath;
    if (file_exists($fullPath)) {
        $sourceFiles[] = $fullPath;
        $latestSourceModificationTime = max($latestSourceModificationTime, filemtime($fullPath));
    } else {
        error_log("Additional file not found: " . $fullPath);
    }
}

$cacheFileModificationTime = file_exists($cacheFile) ? filemtime($cacheFile) : 0;

// If the cache file is newer than or equal to the latest source file, serve it
if ($cacheFileModificationTime >= $latestSourceModificationTime && file_exists($cacheFile)) {
    readfile($cacheFile);
    exit;
}

// --- Helper function to extract content from HTML ---
function extractHtmlContent($htmlContent, $filePath, $siteBaseUrl) {
    $dom = new DOMDocument();
    
    // Suppress warnings for malformed HTML
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8">' . $htmlContent);
    libxml_clear_errors();
    
    $entry = [];
    
    // Try to extract title from <title> tag or <h1>
    $titleNodes = $dom->getElementsByTagName('title');
    if ($titleNodes->length > 0) {
        $entry['title'] = trim($titleNodes->item(0)->textContent);
    } else {
        $h1Nodes = $dom->getElementsByTagName('h1');
        if ($h1Nodes->length > 0) {
            $entry['title'] = trim($h1Nodes->item(0)->textContent);
        } else {
            $entry['title'] = 'Untitled Entry';
        }
    }
    
    // Try to extract published date from meta tags or time elements
    $entry['published'] = null;
    $xpath = new DOMXPath($dom);
    
    // Look for meta tags with date information
    $metaNodes = $xpath->query('//meta[@name="date" or @name="published" or @property="article:published_time"]');
    if ($metaNodes->length > 0) {
        $entry['published'] = $metaNodes->item(0)->getAttribute('content');
    } else {
        // Look for time elements with datetime attribute
        $timeNodes = $xpath->query('//time[@datetime]');
        if ($timeNodes->length > 0) {
            $entry['published'] = $timeNodes->item(0)->getAttribute('datetime');
        }
    }
    
    // Extract main content
    // Try to find main content area (adjust selectors based on your HTML structure)
    $contentNodes = $xpath->query('//main | //article | //div[@class*="content"] | //div[@id="content"]');
    
    if ($contentNodes->length > 0) {
        $contentNode = $contentNodes->item(0);
        $entry['content'] = $dom->saveHTML($contentNode);
    } else {
        // Fallback: get body content but exclude nav, header, footer
        $bodyNodes = $dom->getElementsByTagName('body');
        if ($bodyNodes->length > 0) {
            $body = $bodyNodes->item(0);
            
            // Remove navigation, header, footer elements
            $elementsToRemove = $xpath->query('.//nav | .//header | .//footer | .//script | .//style', $body);
            foreach ($elementsToRemove as $element) {
                $element->parentNode->removeChild($element);
            }
            
            $entry['content'] = $dom->saveHTML($body);
        } else {
            $entry['content'] = $htmlContent; // Ultimate fallback
        }
    }
    
    // Generate URL
    $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath);
    $entry['url'] = $siteBaseUrl . ltrim($relativePath, '/');
    
    // Add file path for sorting fallback
    $entry['sourceFilePath'] = $filePath;
    
    return $entry;
}

// --- Process Each Source File ---

$allEntries = [];

foreach ($sourceFiles as $filePath) {
    $htmlContent = file_get_contents($filePath);
    if ($htmlContent === false) {
        error_log("Could not read file: " . $filePath);
        continue;
    }
    
    $entry = extractHtmlContent($htmlContent, $filePath, $siteBaseUrl);
    $allEntries[] = $entry;
}

// Sort all entries by published date (newest first)
usort($allEntries, function($a, $b) {
    $timeA = $a['published'] ? strtotime($a['published']) : filemtime($a['sourceFilePath']);
    $timeB = $b['published'] ? strtotime($b['published']) : filemtime($b['sourceFilePath']);
    
    return $timeB <=> $timeA; // Newest first
});

// --- Initialize RSS XML ---

$dom = new DOMDocument('1.0', 'UTF-8');
$rss = $dom->createElement('rss');
$rss->setAttribute('version', '2.0');
$rss->setAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
$dom->appendChild($rss);

$channel = $dom->createElement('channel');
$rss->appendChild($channel);

// Add basic channel elements
$channel->appendChild($dom->createElement('title', $feedTitle));
$channel->appendChild($dom->createElement('link', $feedLink));
$channel->appendChild($dom->createElement('description', $feedDescription));
$channel->appendChild($dom->createElement('language', $feedLanguage));
$channel->appendChild($dom->createElement('lastBuildDate', date(DATE_RSS)));

// atom:link for self-reference
$atomLink = $dom->createElement('atom:link');
$atomLink->setAttribute('href', $siteBaseUrl . 'assets/rss.xml');
$atomLink->setAttribute('rel', 'self');
$atomLink->setAttribute('type', 'application/rss+xml');
$channel->appendChild($atomLink);

// --- Add Each Entry to the RSS Feed ---
foreach ($allEntries as $entry) {
    // Convert published date to RSS format, or use file modification time as fallback
    $pubDate = date(DATE_RSS, ($entry['published'] ? strtotime($entry['published']) : filemtime($entry['sourceFilePath'])));
    
    // Create RSS item
    $item = $dom->createElement('item');
    $channel->appendChild($item);
    
    $item->appendChild($dom->createElement('title', htmlspecialchars($entry['title'])));
    $item->appendChild($dom->createElement('link', htmlspecialchars($entry['url'])));
    
    $contentEncoded = $dom->createElement('content:encoded');
    $contentEncoded->appendChild($dom->createCDATASection($entry['content']));
    $item->appendChild($contentEncoded);
    $item->appendChild($dom->createElement('pubDate', $pubDate));
    
    $guid = $dom->createElement('guid', htmlspecialchars($entry['url']));
    $guid->setAttribute('isPermaLink', 'true');
    $item->appendChild($guid);
}

// --- Output and Cache RSS XML ---
$dom->formatOutput = true;
$rssOutput = $dom->saveXML();

// Save the generated RSS to the cache file
file_put_contents($cacheFile, $rssOutput);

// Output the RSS feed to the browser
echo $rssOutput;

?>