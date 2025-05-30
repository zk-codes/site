<?php
/**
 * HTML Word Counter & Reading Time Calculator
 * 
 * This script processes HTML files in a directory, counts words in the <main> tag,
 * calculates reading time, and inserts statistics into each file.
 * 
 * Features:
 * - Recursively processes all HTML files in a directory
 * - Counts words only within <main> content areas
 * - Calculates reading time based on 200 words per minute
 * - Inserts statistics after "Copy & Share" sections
 * - Excludes specified files and directories
 * - Adds processed files to Git staging
 */

// ================================
// CONFIGURATION
// ================================

// Enable immediate output display
ob_implicit_flush(true);

// Files to exclude from processing (case-insensitive)
$excludedFiles = [
    '.gitignore', '.htaccess', 'ClAUDE.md', 'cv.html', 'index.html', 
    'humans.txt', 'LICENSE', 'random.php', 'resume.html', 'robots.txt', 
    'search.php', 'successful.html', 'sitemap.xml'
];

// Directories to exclude from processing
$excludedPaths = [
    '.git', 'assets/templates', 'assets/scripts', 'assets/imgs', 'assets/buttons'
];

// ================================
// DIRECTORY SETUP
// ================================

// Determine base directory (current working directory or Git root)
$baseDirectory = getcwd();
$gitOutput = [];
exec('git rev-parse --show-toplevel 2>/dev/null', $gitOutput, $gitReturnVar);

if ($gitReturnVar === 0 && !empty($gitOutput[0])) {
    $baseDirectory = rtrim($gitOutput[0], "/\\");
    echo "Using Git repository root: '{$baseDirectory}'\n";
} else {
    echo "Using current directory: '{$baseDirectory}'\n";
}

// Convert excluded paths to full system paths
$excludedFullPaths = [];
foreach ($excludedPaths as $dir) {
    $fullPath = $baseDirectory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dir);
    if (is_dir($fullPath)) {
        $excludedFullPaths[] = rtrim($fullPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
}

// ================================
// FILE PROCESSING
// ================================

$filesProcessed = 0;
echo "\nProcessing HTML files...\n";

// Create recursive iterator to find all files
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDirectory, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    // Only process HTML files
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'html') {
        continue;
    }
    
    $filePath = $file->getPathname();
    $fileName = $file->getBasename();
    
    // Skip files in excluded directories
    $skipFile = false;
    foreach ($excludedFullPaths as $excludedPath) {
        if (strpos($filePath, $excludedPath) === 0) {
            echo "Skipping '{$fileName}': In excluded directory\n";
            $skipFile = true;
            break;
        }
    }
    if ($skipFile) continue;
    
    // Skip explicitly excluded files
    if (in_array(strtolower($fileName), array_map('strtolower', $excludedFiles))) {
        echo "Skipping '{$fileName}': Excluded file\n";
        continue;
    }
    
    echo "Processing '{$fileName}'... ";
    
    // ================================
    // FILE CONTENT PROCESSING
    // ================================
    
    // Read file content with error handling
    $html = file_get_contents($filePath);
    if ($html === false) {
        echo "Error reading file\n";
        continue;
    }
    
    // Skip empty files
    if (empty(trim($html))) {
        echo "Skipping empty file\n";
        continue;
    }
    
    // Remove any existing statistics to avoid duplicates
    $html = preg_replace('/\s*<p[^>]*><strong>Statistics<\/strong>[^<]*<\/p>\s*/i', '', $html);
    
    // ================================
    // WORD COUNT CALCULATION
    // ================================
    
    // Parse HTML using DOMDocument for reliable content extraction
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Suppress HTML parsing warnings
    $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    
    // Find and process main content
    $mainElements = $dom->getElementsByTagName('main');
    
    if ($mainElements->length > 0) {
        // Extract text content from main element
        $mainContent = $mainElements->item(0)->textContent;
        $wordCount = str_word_count(trim($mainContent));
        echo "Found main tag with {$wordCount} words. ";
        
        // Calculate reading time (200 words per minute)
        $totalSeconds = max(1, floor(($wordCount / 200) * 60));
        $minutes = floor($totalSeconds / 60);
        $seconds = $totalSeconds % 60;
        $readingTime = sprintf('%d:%02d', $minutes, $seconds);
        
        // ================================
        // STATISTICS INSERTION
        // ================================
        
        // Create statistics HTML
        $statsContent = "<p><strong>Statistics</strong> &rarr; Word Count: {$wordCount} | Reading Time: {$readingTime}</p>";
        
        // Try to insert after "Copy & Share" paragraph (preferred location)
        $copySharePattern = '/(<p[^>]*>.*?<strong>Copy (&amp;|&) Share<\/strong>.*?<\/p>)\s*(<hr>)/i';
        if (preg_match($copySharePattern, $html)) {
            $html = preg_replace($copySharePattern, "$1\n                $statsContent\n                $3", $html);
            echo "Inserted after Copy & Share (before hr). ";
        } else {
            // Fallback: try to insert after Copy & Share without hr
            $copyShareSimple = '/(<p[^>]*>.*?<strong>Copy (&amp;|&) Share<\/strong>.*?<\/p>)/i';
            if (preg_match($copyShareSimple, $html)) {
                $html = preg_replace($copyShareSimple, "$1\n                $statsContent", $html);
                echo "Inserted after Copy & Share (no hr). ";
            } else {
                // Final fallback: insert before closing main tag
                $html = preg_replace('/(\s*<\/main>)/i', "\n                $statsContent\n$1", $html);
                echo "Inserted before </main>. ";
            }
        }
        
        // Fix main tag indentation for consistency
        $html = preg_replace('/(\s*<\/main>)/', "\n        </main>", $html);
        
        // ================================
        // FILE SAVING & GIT INTEGRATION
        // ================================
        
        // Save updated file
        if (file_put_contents($filePath, $html) !== false) {
            echo "Updated with {$wordCount} words, reading time: {$readingTime}\n";
            $filesProcessed++;
            
            // Add to Git staging if we're in a Git repository
            if ($gitReturnVar === 0) {
                $relativePath = str_replace($baseDirectory . DIRECTORY_SEPARATOR, '', $filePath);
                exec("cd " . escapeshellarg($baseDirectory) . " && git add " . escapeshellarg($relativePath) . " 2>/dev/null");
            }
        } else {
            echo "Error saving file\n";
        }
        
    } else {
        // Handle files without main tags
        echo "No <main> tag found. ";
        
        // Debug information for troubleshooting
        if (preg_match('/<main[^>]*>/i', $html)) {
            echo "Found <main> opening tag with regex but DOMDocument couldn't parse it.\n";
        } else {
            echo "No <main> tag found at all.\n";
        }
    }
}

// ================================
// COMPLETION SUMMARY
// ================================

echo "\nComplete! Processed {$filesProcessed} HTML files.\n";
?>