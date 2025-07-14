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
$feedDescription = 'Hi! Zachary Kai here, a space fantasy writer, offbeat queer, twenty-something, curiosity enthusiast, and traveler.';
$feedLanguage = 'en-us';

require_once __DIR__ . '/php-mf2/php-mf2-0.5.0/Mf2/Parser.php';

use Mf2\Parser;

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
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $relPath; // Assuming files are relative to document root
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
    exit; // Exit, no need to regenerate
}

// --- If we reach here, cache needs to be regenerated ---

$allEntries = [];

// --- Process Each Source File using php-mf2 ---
foreach ($sourceFiles as $filePath) {
    $htmlContent = file_get_contents($filePath);
    if ($htmlContent === false) {
        error_log("Could not read file: " . $filePath);
        continue; // Skip to the next file
    }

    // Determine the base URL for parsing, important for resolving relative URLs within the content
    $pathInfo = pathinfo($filePath);
    $relativeUrl = str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath);
    $documentBaseUrl = $siteBaseUrl . ltrim($relativeUrl, '/');


    $parser = new Parser($htmlContent, $documentBaseUrl);
    $mf2Data = $parser->parse();

    foreach ($mf2Data['items'] as $item) {
        if (in_array('h-entry', $item['type'])) {
            // Add original filePath to the entry for sorting based on filemtime if published date is missing
            $item['sourceFilePath'] = $filePath;
            $allEntries[] = $item;
        }
    }
}

// Sort all h-entry items by published date (newest first)
usort($allEntries, function($a, $b) {
    $pubDateA = $a['properties']['published'][0] ?? null;
    $pubDateB = $b['properties']['published'][0] ?? null;

    $timeA = $pubDateA ? strtotime($pubDateA) : filemtime($a['sourceFilePath']);
    $timeB = $pubDateB ? strtotime($pubDateB) : filemtime($b['sourceFilePath']);

    return $timeB <=> $timeA; // Newest first
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

// atom:link for self-reference (this is the public URL the feed will be accessed from)
$atomLink = $dom->createElement('atom:link');
$atomLink->setAttribute('href', $siteBaseUrl . 'assets/rss.xml');
$atomLink->setAttribute('rel', 'self');
$atomLink->setAttribute('type', 'application/rss+xml');
$channel->appendChild($atomLink);

// --- Add Each h-entry to the RSS Feed ---
foreach ($allEntries as $hEntry) {
    // Extract properties
    $title = $hEntry['properties']['name'][0] ?? 'Untitled Entry';
    $url = $hEntry['properties']['url'][0] ?? null;
    $contentHtml = $hEntry['properties']['content'][0]['html'] ?? $hEntry['properties']['content'][0] ?? '';
    $published = $hEntry['properties']['published'][0] ?? null;

    // Fallback URL if not explicitly set in h-entry (e.g., for jots/newsletter that might not have a dedicated permalink)
    if (!$url) {
        $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $hEntry['sourceFilePath']);
        $url = $siteBaseUrl . ltrim($relativePath, '/');
    }

    // Convert published date to RSS format, or use file modification time as fallback
    $pubDate = date(DATE_RSS, ($published ? strtotime($published) : filemtime($hEntry['sourceFilePath'])));

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

// --- Output and Cache RSS XML ---

$dom->formatOutput = true; // Makes the output readable (for debugging/inspection)
$rssOutput = $dom->saveXML();

// Save the generated RSS to the cache file
file_put_contents($cacheFile, $rssOutput);

// Output the RSS feed to the browser
echo $rssOutput;

?>