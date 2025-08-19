<?php

header('Content-Type: application/xml; charset=utf-8');

$notesDirectory = $_SERVER['DOCUMENT_ROOT'] . '/notes/';
$additionalFiles = ['/newsletter.html', '/jots.html'];

$siteBaseUrl = 'https://zacharykai.net/';
$feedTitle = 'Zachary Kai';
$feedLink = $siteBaseUrl;
$feedDescription = 'Hi! I\'m Zachary Kai, a space fantasy writer, offbeat queer, twenty-something, curiosity enthusiast, and traveler.';
$feedLanguage = 'en-us';
$feedImageUrl = $siteBaseUrl . 'assets/icon.png';
$feedImageTitle = 'Zachary Kai';
$feedImageLink = $siteBaseUrl;

$sourceFiles = [];

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

foreach ($additionalFiles as $relPath) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $relPath;
    if (file_exists($fullPath)) {
        $sourceFiles[] = $fullPath;
    } else {
        error_log("Additional file not found: " . $fullPath);
    }
}

function extractHtmlContent($htmlContent, $filePath, $siteBaseUrl) {
    $dom = new DOMDocument();
    
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8">' . $htmlContent);
    libxml_clear_errors();
    
    $entry = [];
    $xpath = new DOMXPath($dom);
    
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
    
    $contentNode = null;
    
    $eContentNodes = $xpath->query('//section[contains(concat(" ", normalize-space(@class), " "), " e-content ")]');
    if ($eContentNodes->length > 0) {
        $contentNode = $eContentNodes->item(0);
    }
    
    $entry['content'] = $contentNode ? $dom->saveHTML($contentNode) : '';
    
    $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath);
    $pathInfo = pathinfo($relativePath);
    $filenameWithoutExtension = $pathInfo['filename'];
    $directory = ltrim($pathInfo['dirname'], '/');
    
    if ($directory && $directory !== '.') {
        $entry['url'] = $siteBaseUrl . $directory . '/' . $filenameWithoutExtension;
    } else {
        $entry['url'] = $siteBaseUrl . $filenameWithoutExtension;
    }
    
    $entry['sourceFilePath'] = $filePath;
    
    return $entry;
}

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

usort($allEntries, function($a, $b) {
    $timeA = $a['published'] ? strtotime($a['published']) : filemtime($a['sourceFilePath']);
    $timeB = $b['published'] ? strtotime($b['published']) : filemtime($b['sourceFilePath']);
    
    return $timeB <=> $timeA;
});

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
    $pubDate = date(DATE_RSS, ($entry['published'] ? strtotime($entry['published']) : filemtime($entry['sourceFilePath'])));
    
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