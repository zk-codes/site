<?php

header('Content-Type: application/xml; charset=utf-8');

$notesDirectory = $_SERVER['DOCUMENT_ROOT'] . '/notes/';
$additionalFiles = ['/jots']; // '/jots' will be treated as an h-feed source

$siteBaseUrl = 'https://zacharykai.net/';
$feedTitle = 'Zachary Kai';
$feedLink = $siteBaseUrl;
$feedDescription = 'Hi! I\'m Zachary Kai, a space fantasy writer, offbeat queer, twenty-something, curiosity enthusiast, and traveler.';
$feedLanguage = 'en-us';
$feedImageUrl = $siteBaseUrl . 'assets/icon.png';
$feedImageTitle = 'Zachary Kai';
$feedImageLink = $siteBaseUrl;

$sourceFiles = [];

// Add files from the notes directory
if (is_dir($notesDirectory)) {
    $iterator = new DirectoryIterator($notesDirectory);
    foreach ($iterator as $fileinfo) {
        if ($fileinfo->isFile() && $fileinfo->getExtension() === 'html') {
            $filePath = $fileinfo->getPathname();
            $sourceFiles[] = $filePath;
        }
    }
} else {
    error_log("Notes directory not found: " . $notesDirectory);
    echo '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>Error</title><description>Notes directory not found.</description></channel></rss>';
    exit;
}

// Add specified additional files (like /jots)
foreach ($additionalFiles as $relPath) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $relPath;
    if (file_exists($fullPath)) {
        $sourceFiles[] = $fullPath;
    } else {
        error_log("Additional file not found: " . $fullPath);
    }
}

/**
 * Extracts content from a standard HTML file (e.g., from the /notes directory) for RSS.
 */
function extractHtmlContent($htmlContent, $filePath, $siteBaseUrl) {
    $dom = new DOMDocument();
    
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8">' . $htmlContent);
    libxml_clear_errors();
    
    $entry = [];
    $xpath = new DOMXPath($dom);
    
    // --- Extract Title ---
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
    
    // --- Extract Published Date ---
    $entry['published'] = null;
    $metaNodes = $xpath->query('//meta[@name="date" or @name="published" or @property="article:published_time"]');
    if ($metaNodes->length > 0) {
        $entry['published'] = $metaNodes->item(0)->getAttribute('content');
    } else {
        $timeNodes = $xpath->query('//time[@datetime]');
        if ($timeNodes->length > 0) {
            $entry['published'] = $timeNodes->item(0)->getAttribute('datetime');
        }
    }
    
    // --- Extract Content (e-content) ---
    $contentNode = null;
    $eContentNodes = $xpath->query('//section[contains(concat(" ", normalize-space(@class), " "), " e-content ")]');
    if ($eContentNodes->length > 0) {
        $contentNode = $eContentNodes->item(0);
    }
    $entry['content'] = $contentNode ? $dom->saveHTML($contentNode) : '';
    
    // --- Construct URL ---
    $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath);
    $entry['url'] = $siteBaseUrl . ltrim($relativePath, '/');
    // Remove .html from the URL if present for clean URLs
    if (substr($entry['url'], -5) === '.html') {
         $entry['url'] = substr($entry['url'], 0, -5);
    }
    
    $entry['sourceFilePath'] = $filePath;
    
    return $entry;
}

/**
 * Extracts h-entry microblog posts from an h-feed HTML file (like /jots).
 */
function extractHFeedEntries($htmlContent, $filePath, $siteBaseUrl) {
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8">' . $htmlContent);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $hFeedEntries = [];
    $baseJotsUrl = rtrim($siteBaseUrl, '/') . '/' . ltrim(str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath), '/');

    // Find all h-entry elements
    $hEntryNodes = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " h-entry ")]');
    $entryCounter = 0;

    foreach ($hEntryNodes as $hEntryNode) {
        $entry = [];
        $entry['sourceFilePath'] = $filePath;
        
        // --- Extract Content (e-content) first ---
        $eContentNodeQuery = $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " e-content ")]', $hEntryNode);
        $entry['content'] = ($eContentNodeQuery->length > 0) ? $dom->saveHTML($eContentNodeQuery->item(0)) : '';

        // --- Extract Title (p-summary, then generate from e-content snippet) ---
        $entry['title'] = '';

        // Try to find a p-summary element within the h-entry
        $pSummaryNode = $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " p-summary ")]', $hEntryNode);
        if ($pSummaryNode->length > 0) {
            $entry['title'] = trim($pSummaryNode->item(0)->textContent);
        }

        // Fallback: Generate a title from a snippet of the e-content
        if (empty($entry['title'])) {
            $snippet = strip_tags($entry['content']);
            // Limit snippet to 70 characters for a concise title
            $entry['title'] = 'Microblog Entry: ' . substr($snippet, 0, 70) . (strlen($snippet) > 70 ? '...' : ''); 
            if (empty(trim(str_replace('...', '', $entry['title'])))) { // If snippet is also empty or just whitespace
                $entry['title'] = 'Untitled Microblog Entry';
            }
        }
        
        // --- Extract Published Date (dt-published), or fallback to h-entry ID/filemtime ---
        $entry['published'] = null;
        $dtPublishedNode = $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " dt-published ")]', $hEntryNode);
        if ($dtPublishedNode->length > 0) {
            $datetimeAttr = $dtPublishedNode->item(0)->getAttribute('datetime');
            $entry['published'] = !empty($datetimeAttr) ? $datetimeAttr : trim($dtPublishedNode->item(0)->textContent);
        }
        
        // Fallback 1: Try to extract date from h-entry ID if dt-published is missing or empty
        if (empty($entry['published'])) {
            $hEntryId = $hEntryNode->getAttribute('id');
            if (!empty($hEntryId)) {
                // Common date formats in IDs like DDmonYY (e.g., 16jun25)
                if (preg_match('/(\d{2}[a-zA-Z]{3}\d{2})/', $hEntryId, $matches)) {
                    $dateStr = $matches[1];
                    // Convert to a format strtotime can reliably parse (e.g., 16 Jun 2025)
                    $day = substr($dateStr, 0, 2);
                    $month = substr($dateStr, 2, 3);
                    $year = '20' . substr($dateStr, 5, 2); // Assuming 20XX for the year
                    $parsedDate = strtotime("$day $month $year");
                    if ($parsedDate !== false) {
                        $entry['published'] = date('c', $parsedDate);
                    }
                }
            }
        }

        // Fallback 2: Use file modification time of the jots file
        if (empty($entry['published'])) {
            $entry['published'] = date('c', filemtime($filePath));
        }

        // --- Extract URL (u-url) or generate a unique one using h-entry ID ---
        $uUrlNode = $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " u-url ")]', $hEntryNode);
        if ($uUrlNode->length > 0) {
            $entry['url'] = $uUrlNode->item(0)->getAttribute('href');
            // Ensure the URL is absolute if it's relative
            if (!filter_var($entry['url'], FILTER_VALIDATE_URL)) {
                $entry['url'] = rtrim($siteBaseUrl, '/') . '/' . ltrim($entry['url'], '/');
            }
        } else {
            // Generate a unique URL using the h-entry ID if available
            $hEntryId = $hEntryNode->getAttribute('id');
            if (!empty($hEntryId)) {
                $entry['url'] = $baseJotsUrl . '#' . $hEntryId;
            } else {
                // Fallback to date and counter if no ID is present
                $dateForId = $entry['published'] ? date('YmdHis', strtotime($entry['published'])) : date('YmdHis', filemtime($filePath));
                $entry['url'] = $baseJotsUrl . '#' . $dateForId . '-' . $entryCounter;
            }
        }
        $entryCounter++; // Increment counter for unique URLs

        $hFeedEntries[] = $entry;
    }
    
    return $hFeedEntries;
}

$allEntries = [];

foreach ($sourceFiles as $filePath) {
    $htmlContent = file_get_contents($filePath);
    if ($htmlContent === false) {
        error_log("Could not read file: " . $filePath);
        continue;
    }
    
    // Check if the current file is the /jots file to apply h-feed parsing
    // We're checking if the full path contains '/jots'
    if (strpos($filePath, $_SERVER['DOCUMENT_ROOT'] . '/jots') === 0) { 
        $jotEntries = extractHFeedEntries($htmlContent, $filePath, $siteBaseUrl);
        $allEntries = array_merge($allEntries, $jotEntries);
    } else {
        // Otherwise, process as a standard HTML content file (e.g., from /notes)
        $entry = extractHtmlContent($htmlContent, $filePath, $siteBaseUrl);
        $allEntries[] = $entry;
    }
}

// Sort all entries by date, newest first
usort($allEntries, function($a, $b) {
    // Determine published time, falling back to file modification time if 'published' isn't available or valid
    $timeA = !empty($a['published']) ? strtotime($a['published']) : false;
    if ($timeA === false) $timeA = filemtime($a['sourceFilePath']);

    $timeB = !empty($b['published']) ? strtotime($b['published']) : false;
    if ($timeB === false) $timeB = filemtime($b['sourceFilePath']);
    
    return $timeB <=> $timeA; // Sort in descending order (newest first)
});

// --- RSS Feed Generation ---
$dom = new DOMDocument('1.0', 'UTF-8');
$rss = $dom->createElement('rss');
$rss->setAttribute('version', '2.0');
$rss->setAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
$rss->setAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
$dom->appendChild($rss);

$channel = $dom->createElement('channel');
$rss->appendChild($channel);

$channel->appendChild($dom->createElement('title', $feedTitle));
$channel->appendChild($dom->createElement('link', $feedLink));
$channel->appendChild($dom->createElement('description', $feedDescription));
$channel->appendChild($dom->createElement('language', $feedLanguage));
$channel->appendChild($dom->createElement('lastBuildDate', date(DATE_RSS)));

$atomLink = $dom->createElement('atom:link');
$atomLink->setAttribute('href', $siteBaseUrl . 'assets/rss.xml');
$atomLink->setAttribute('rel', 'self');
$atomLink->setAttribute('type', 'application/rss+xml');
$channel->appendChild($atomLink);

$image = $dom->createElement('image');
$image->appendChild($dom->createElement('url', $feedImageUrl));
$image->appendChild($dom->createElement('title', $feedImageTitle));
$image->appendChild($dom->createElement('link', $feedImageLink));
$channel->appendChild($image);

foreach ($allEntries as $entry) {
    // Use the entry's published date, or fallback to file modification time
    $pubTimestamp = !empty($entry['published']) ? strtotime($entry['published']) : false;
    if ($pubTimestamp === false) {
        $pubTimestamp = filemtime($entry['sourceFilePath']);
    }
    $pubDate = date(DATE_RSS, $pubTimestamp);
    
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

$dom->formatOutput = true;
$rssOutput = $dom->saveXML();

echo $rssOutput;

?>