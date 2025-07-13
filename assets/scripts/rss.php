<?php

// Set content type header to XML
header('Content-Type: application/xml; charset=utf-8');

// --- Configuration ---

$notesDirectory = '/notes/';

$cacheDirectory = './cache/'; // Path to store the cached RSS file, relative to assets/scripts/
$cacheFile = $cacheDirectory . 'rss.xml';

$siteBaseUrl = 'https://zacharykai.net/';
$feedTitle = 'Zachary Kai';
$feedLink = $siteBaseUrl . 'notes/'; // Link to your notes page
$feedDescription = 'Latest updates from Zachary Kai\'s notes.';
$feedLanguage = 'en-au';

require_once __DIR__ . '/php-mf2/Mf2/Parser.php';

use Mf2\Parser;

if (!is_dir($cacheDirectory)) {
    mkdir($cacheDirectory, 0755, true);
}

// --- Caching Logic: Regenerate only if notes are newer than cached RSS ---

$latestNoteModificationTime = 0;
$noteFiles = [];

// Get all HTML files and find the latest modification time
if (is_dir($notesDirectory)) {
    $iterator = new DirectoryIterator($notesDirectory);
    foreach ($iterator as $fileinfo) {
        if ($fileinfo->isFile() && $fileinfo->getExtension() === 'html') {
            $filePath = $fileinfo->getPathname();
            $noteFiles[] = $filePath;
            $latestNoteModificationTime = max($latestNoteModificationTime, $fileinfo->getMTime());
        }
    }
} else {
    // Handle error: notes directory does not exist
    error_log("Notes directory not found: " . $notesDirectory);
    // You might want to output an empty or error RSS feed here
    echo '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>Error</title><description>Notes directory not found.</description></channel></rss>';
    exit;
}

$cacheFileModificationTime = file_exists($cacheFile) ? filemtime($cacheFile) : 0;

// If the cache file is newer than or equal to the latest note, serve it
if ($cacheFileModificationTime >= $latestNoteModificationTime && file_exists($cacheFile)) {
    readfile($cacheFile);
    exit; // Exit, no need to regenerate
}

// --- If we reach here, cache needs to be regenerated ---

// Sort notes by modification time, newest first, for RSS order
usort($noteFiles, function($a, $b) {
    return filemtime($b) <=> filemtime($a);
});

// --- Initialize RSS XML ---
$dom = new DOMDocument('1.0', 'UTF-8');
$rss = $dom->createElement('rss');
$rss->setAttribute('version', '2.0');
$rss->setAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom'); // For atom:link self-reference
$dom->appendChild($rss);

$channel = $dom->createElement('channel');
$rss->appendChild($channel);

// Add basic channel elements
$channel->appendChild($dom->createElement('title', $feedTitle));
$channel->appendChild($dom->createElement('link', $feedLink));
$channel->appendChild($dom->createElement('description', $feedDescription));
$channel->appendChild($dom->createElement('language', $feedLanguage));
$channel->appendChild($dom->createElement('lastBuildDate', date(DATE_RSS))); // Current time of feed generation

// Add atom:link for self-reference (good practice for discoverability)
$atomLink = $dom->createElement('atom:link');
$atomLink->setAttribute('href', $siteBaseUrl . 'rss.xml'); // Assuming this script will be accessed as rss.xml
$atomLink->setAttribute('rel', 'self');
$atomLink->setAttribute('type', 'application/rss+xml');
$channel->appendChild($atomLink);

// --- Process Each Note File using php-mf2 ---
foreach ($noteFiles as $filePath) {
    $htmlContent = file_get_contents($filePath);
    if ($htmlContent === false) {
        error_log("Could not read file: " . $filePath);
        continue; // Skip to the next file
    }

    // Parse the HTML content for microformats
    $parser = new Parser($htmlContent, $siteBaseUrl . basename($filePath)); // Base URL helps resolve relative URLs
    $mf2Data = $parser->parse();

    // Look for h-entry items. A note file might contain multiple, but typically one primary.
    // We'll just take the first h-entry found for simplicity for this feed.
    $hEntry = null;
    foreach ($mf2Data['items'] as $item) {
        if (in_array('h-entry', $item['type'])) {
            $hEntry = $item;
            break; // Found our h-entry, break out
        }
    }

    if ($hEntry) {

        // Extract properties (adjust property names based on your h-entry structure)
        $title = $hEntry['properties']['name'][0] ?? 'Untitled Note from ' . basename($filePath);
        $url = $hEntry['properties']['url'][0] ?? $siteBaseUrl . 'notes/' . basename($filePath);
        $contentHtml = $hEntry['properties']['content'][0]['html'] ?? $hEntry['properties']['content'][0] ?? '';
        $published = $hEntry['properties']['published'][0] ?? null;

        // Convert published date to RSS format
        $pubDate = date(DATE_RSS, ($published ? strtotime($published) : filemtime($filePath)));

        // Create RSS item
        $item = $dom->createElement('item');
        $channel->appendChild($item);

        $item->appendChild($dom->createElement('title', htmlspecialchars($title)));
        $item->appendChild($dom->createElement('link', htmlspecialchars($url)));
        $item->appendChild($dom->createElement('description', '<![CDATA[' . $contentHtml . ']]>')); // Use CDATA for HTML content
        $item->appendChild($dom->createElement('pubDate', $pubDate));

        // GUID should be a unique identifier for the item. URL is often used.
        $guid = $dom->createElement('guid', htmlspecialchars($url));
        $guid->setAttribute('isPermaLink', 'true');
        $item->appendChild($guid);
    }
}

// --- Output and Cache RSS XML ---

$dom->formatOutput = true; // Makes the output readable (for debugging/inspection)
$rssOutput = $dom->saveXML();

// Save the generated RSS to the cache file
file_put_contents($cacheFile, $rssOutput);

// Output the RSS feed to the browser
echo $rssOutput;

?>