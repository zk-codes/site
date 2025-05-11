<?php

// RSS Feed Generator for Static HTML Files

// Configuration

$config = [
    'site_title' => 'Zachary Kai',
    'site_description' => 'Avowed generalist, space fantasy writer, zinester, offbeat queer, and world traveler.',
    'site_url' => 'https://zacharykai.net',
    'html_dir' => '../../notes/', // Directory containing your HTML files (relative to script location)
    'rss_file' => '../../assets/rss.xml', // Output RSS file (relative to script location)
    'items_limit' => 20,      // Maximum number of items in the feed
    'date_format' => 'r',     // RFC 2822 date format for RSS
];

/**
 * Extract metadata and content from HTML file
 * 
 * @param string $file_path Path to HTML file
 * @return array|false Metadata array or false if extraction failed
 */

function extractMetadata($file_path) {
    // Read the file
    $html = file_get_contents($file_path);
    if (!$html) {
        return false;
    }
    
    $metadata = [];
    
    // Extract title
    preg_match('/<title>(.*?)<\/title>/is', $html, $title_matches);
    $metadata['title'] = isset($title_matches[1]) ? trim($title_matches[1]) : basename($file_path);
    
    // Extract description (meta description or first paragraph for summary)
    preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/is', $html, $desc_matches);
    if (isset($desc_matches[1])) {
        $metadata['description'] = trim($desc_matches[1]);
    } else {
        // Fallback to first paragraph content
        preg_match('/<p>(.*?)<\/p>/is', $html, $p_matches);
        $metadata['description'] = isset($p_matches[1]) 
            ? trim(strip_tags($p_matches[1])) 
            : 'No description available';
    }
    
    // Extract full content (everything inside the body tag)
    preg_match('/<body.*?>(.*?)<\/body>/is', $html, $body_matches);
    if (isset($body_matches[1])) {
        $metadata['content'] = trim($body_matches[1]);
    } else {
        // Fallback to the whole HTML if body tags aren't found
        $metadata['content'] = $html;
    }
    
    // Extract date
    // First try to find a meta date tag
    preg_match('/<meta\s+name=["\']date["\']\s+content=["\'](.*?)["\']/is', $html, $date_matches);
    
    // Then try to find date in the content (common format)
    if (!isset($date_matches[1])) {
        preg_match('/(\d{4}-\d{2}-\d{2})/is', $html, $date_matches);
    }
    
    $metadata['date'] = isset($date_matches[1]) 
        ? strtotime($date_matches[1]) 
        : filemtime($file_path); // Use file modification time as fallback
    
    // Get relative URL from file path
    $metadata['link'] = str_replace('./notes/', '', $file_path);
    
    return $metadata;
}

/**
 * Generate RSS feed XML
 * 
 * @param array $items Feed items
 * @param array $config Configuration
 * @return string RSS XML
 */

function generateRSS($items, $config) {
    $now = date($config['date_format']);
    
    // Start RSS XML
    $rss = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $rss .= '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">' . PHP_EOL;
    $rss .= '<channel>' . PHP_EOL;
    $rss .= '  <title>' . htmlspecialchars($config['site_title']) . '</title>' . PHP_EOL;
    $rss .= '  <link>' . htmlspecialchars($config['site_url']) . '</link>' . PHP_EOL;
    $rss .= '  <description>' . htmlspecialchars($config['site_description']) . '</description>' . PHP_EOL;
    $rss .= '  <language>en-us</language>' . PHP_EOL;
    $rss .= '  <lastBuildDate>' . $now . '</lastBuildDate>' . PHP_EOL;
    $rss .= '  <generator>Static HTML RSS Generator</generator>' . PHP_EOL;
    
    // Add items
    foreach ($items as $item) {
        $full_link = rtrim($config['site_url'], '/') . '/' . ltrim($item['link'], '/');
        
        $rss .= '  <item>' . PHP_EOL;
        $rss .= '    <title>' . htmlspecialchars($item['title']) . '</title>' . PHP_EOL;
        $rss .= '    <link>' . htmlspecialchars($full_link) . '</link>' . PHP_EOL;
        $rss .= '    <guid isPermaLink="true">' . htmlspecialchars($full_link) . '</guid>' . PHP_EOL;
        $rss .= '    <description>' . htmlspecialchars($item['description']) . '</description>' . PHP_EOL;
        $rss .= '    <content:encoded><![CDATA[' . $item['content'] . ']]></content:encoded>' . PHP_EOL;
        $rss .= '    <pubDate>' . date($config['date_format'], $item['date']) . '</pubDate>' . PHP_EOL;
        $rss .= '  </item>' . PHP_EOL;
    }
    
    // Close RSS XML
    $rss .= '</channel>' . PHP_EOL;
    $rss .= '</rss>';
    
    return $rss;
}

// Main execution

// Make sure the output directory exists
$dir = dirname($config['rss_file']);
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// Scan HTML directory
$html_files = glob($config['html_dir'] . '*.html');
$items = [];

// Process each HTML file
foreach ($html_files as $file) {
    $metadata = extractMetadata($file);
    if ($metadata) {
        $items[] = $metadata;
    }
}

// Sort items by date, newest first
usort($items, function($a, $b) {
    return $b['date'] - $a['date'];
});

// Limit the number of items
$items = array_slice($items, 0, $config['items_limit']);

// Generate RSS
$rss_content = generateRSS($items, $config);

// Save to file
file_put_contents($config['rss_file'], $rss_content);

echo "RSS feed generated successfully: " . $config['rss_file'] . PHP_EOL;
echo "Found " . count($items) . " items" . PHP_EOL;
?>