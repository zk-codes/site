<?php

// Enable Implicit Flushing To Send Output Immediately
ob_implicit_flush(true);

// Configure The Base Directory
// Set baseDirectory to the detected Git repository root
$baseDirectory = ''; 
$gitOutput = [];
$gitReturnVar = 0;

// Try To Find Git Repository Root To Use As Base Directory
echo "Attempting to detect Git repository root for base directory...\n";
flush();
exec("git rev-parse --show-toplevel 2>&1", $gitOutput, $gitReturnVar);

if ($gitReturnVar === 0 && !empty($gitOutput[0])) {
    $baseDirectory = rtrim($gitOutput[0], DIRECTORY_SEPARATOR);
    echo "Base Directory set to Git repository root: '{$baseDirectory}'\n";
} else {
    // Fallback: If Git root can't be found, use the script's own directory
    $baseDirectory = __DIR__;
    echo "Warning: Couldn't detect Git repository root. Base Directory defaulting to script's directory: '{$baseDirectory}'. Output: " . implode("\n", $gitOutput) . "\n";
}
flush();


// Git Repository Root Detection
$gitRepoRoot = '';
$gitOutput = [];
$gitReturnVar = 0;

// Try To Find Git Repository Root
exec("git rev-parse --show-toplevel 2>&1", $gitOutput, $gitReturnVar);

if ($gitReturnVar === 0 && !empty($gitOutput[0])) {
    $gitRepoRoot = rtrim($gitOutput[0], DIRECTORY_SEPARATOR);
} else {
    // Fallback to the base directory if git root can't be found (should already be set above)
    $gitRepoRoot = $baseDirectory;
}

// Files To Exclude From Processing
    // By Basename (e.g. 'index.html')
    $excludedFiles = ['.gitingore', '.htaccess', 'ClAUDE.md', 'cv.html', 'index.html', 'humans.txt', 'LICENSE', 'random.php', 'resume.html', 'robots.txt', 'search.php', 'successful.html', 'sitemap.xml'];
    echo "Excluded Files (basename): " . implode(', ', $excludedFiles) . "\n"; // Debug: Show excluded files
    // By Directory Path Relative To $baseDirectory (e.g. '/assets/templates')
    // Added '/assets/scripts' as it's now under the baseDirectory
    $excludedPaths = ['/assets/templates', '/assets/scripts'];
    echo "Excluded Paths (relative): " . implode(', ', $excludedPaths) . "\n"; // Debug: Show excluded paths
flush();

// Prepare Excluded Directory Absolute Paths For Checking
$excludedFullPaths = [];
foreach ($excludedPaths as $dir) {
    // Note: realpath can return false if path doesn't exist. Add error handling if needed.
    $fullPath = realpath($baseDirectory . $dir);
    if ($fullPath !== false) {
        $excludedFullPaths[] = $fullPath . DIRECTORY_SEPARATOR;
    } else {
        echo "Warning: Excluded path '{$baseDirectory}{$dir}' could not be resolved.\n"; // Debug: warn if path doesn't exist
    }
}
echo "Excluded Full Paths: " . implode(', ', $excludedFullPaths) . "\n"; // Debug: Show resolved excluded paths
flush();

// Initialize Counter For Processed Files
$filesProcessedCount = 0;

echo "\nStarting HTML file processing...\n";
flush();

// Iterating Over Each HTML File Recursively
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDirectory, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

// Debug: Flag To Check If Iterator Finds Any Files
$iteratorFoundFiles = false; 
foreach ($iterator as $file) {
    // Debug: Set Flag Of At Least One File Is Found By Iterator
    $iteratorFoundFiles = true; 

    // Ensure Processing HTML Files Only
    if (!$file->isFile() || $file->getExtension() !== 'html') {
        // Only output skipping if it's a file but not HTML, or if it's not a file (like a directory)
        if ($file->isFile() && $file->getExtension() !== 'html') {
            echo "Skipping '{$file->getPathname()}': Not an HTML file (extension is '{$file->getExtension()}').\n";
        } elseif (!$file->isFile()) {
            // This case handles directories which RecursiveDirectoryIterator will also yield
            // We don't need to report every directory, but can if desired for debugging
            // echo "Skipping '{$file->getPathname()}': Not a file (likely a directory).\n";
        }
        flush();
        continue;
    }

    // Get Full Path And Basename For Current File
    $filePath = $file->getPathname();
    $fileName = $file->getBasename();

    echo "Processing '{$filePath}'... ";
    flush();

    // Skip Excluded Files By Basename
    if (in_array($fileName, $excludedFiles)) {
        echo "Skipping excluded file (by basename: '{$fileName}').\n"; // Debug: Specific skip reason
        flush();
        continue;
    }

    // Skip Files Inside Excluded Directories
    $fileRealPath = realpath($filePath);
    $skippedInDir = false;
    foreach ($excludedFullPaths as $excludedFullPath) {
        // On Windows, realpath might return different casing or backslashes.
        // strpos is case-sensitive, so convert both to lowercase for comparison.
        // Also, normalize directory separators.
        $normalizedFileRealPath = str_replace('\\', '/', strtolower($fileRealPath));
        $normalizedExcludedFullPath = str_replace('\\', '/', strtolower($excludedFullPath));

        if ($fileRealPath !== false && strpos($normalizedFileRealPath, $normalizedExcludedFullPath) === 0) {
            echo "Skipping file in excluded directory ('{$fileRealPath}' is in '{$excludedFullPath}').\n"; // Debug: Specific skip reason
            $skippedInDir = true;
            break;
        }
    }
    if ($skippedInDir) {
        flush();
        continue;
    }

    // Read File Content With Error Handling
    $html = file_get_contents($filePath);
    if ($html === false) {
        echo "Error: Could not read content from '{$filePath}'.\n"; // Debug: Specific error
        flush();
        continue;
    }

    // Remove Any Existing Statistics Pattern From The HTML
    $statsPattern = '/<p[^>]*>.*?Statistics.*?<\/p>/is';
    $html = preg_replace($statsPattern, '', $html);

    // Find Content Inside <main> tag
    if (preg_match('/<main[^>]*>(.*?)<\/main>/is', $html, $matches)) {
        echo "Found <main> tag in '{$filePath}'.\n"; // Debug: Main tag found

        // Strip Tags And Count Words
        $textContent = strip_tags($matches[1]);
        $wordCount = str_word_count($textContent);

        // Calculate Reading Time (200 Words Per Minute)
        // Calculate Total Seconds
        $totalSeconds = floor(($wordCount / 200) * 60);

        // Ensure A Minimum 1 Second For Non-Zero Word Counts
        if ($totalSeconds === 0 && $wordCount > 0) {
            $totalSeconds = 1;
        }

        // Format Reading Time
        $minutes = floor($totalSeconds / 60);
        $seconds = $totalSeconds % 60;
        $formattedReadingTime = sprintf('%d:%02d', $minutes, $seconds);

        // Full Statistics Pattern To Update
        $newStatsContent = "<p><strong>Statistics</strong> &rarr; Word Count: {$wordCount} | Reading Time: {$formattedReadingTime}</p>";

        // Find "Copy & Share" Tag And Insert The New Statistics After It
        $copySharePattern = '/(<p[^>]*>.*?<strong>Copy &amp; share<\/strong>.*?<\/p>)/is';
        if (preg_match($copySharePattern, $html)) {
            // Debug: Copy & Share Pattern Found
            echo "Found 'Copy & Share' pattern in '{$filePath}'.\n";
            $updatedHtml = preg_replace($copySharePattern, "$1\n{$newStatsContent}", $html);
        } else {
            // Debug: Copy & Share Pattern Not Found
            echo "Did NOT find 'Copy & Share' pattern in '{$filePath}'. Inserting before </main>.\n";
            // Fallback: If "Copy & Share" Tag Not Found, Insert Before Closing </main> Tag
            $updatedHtml = preg_replace('/(<\/main>)/is', "{$newStatsContent}\n$1", $html);
        }

        // Save Changes With Error Handling
        if (file_put_contents($filePath, $updatedHtml) === false) {
            echo "Error: Could not write updated content to '{$filePath}'.\n";
            flush();

        } else {
            echo "Updated '{$filePath}' with word count: {$wordCount} and reading time: {$formattedReadingTime}.\n";

            // Increment The Counter
            $filesProcessedCount++;

            // Add File To Git Staging From The Repository Root
            $relativeFilePath = ltrim(str_replace($gitRepoRoot, '', $filePath), DIRECTORY_SEPARATOR);
            $gitAddCommand = "cd " . escapeshellarg($gitRepoRoot) . " && git add " . escapeshellarg($relativeFilePath) . " 2>&1";

            $gitAddOutput = [];
            $gitAddReturnVar = 0;
            exec($gitAddCommand, $gitAddOutput, $gitAddReturnVar);

            if ($gitAddReturnVar === 0) {
                echo "Successfully added '{$relativeFilePath}' to Git staging.\n";
            } else {
                echo "Error adding '{$relativeFilePath}' to Git staging. Command: '{$gitAddCommand}'. Output: " . implode("\n", $gitAddOutput) . "\n";
            }
            flush();
        }

    // If No <main> Tag Found, Or No content For Statistics Salculation
    } else {
        echo "No <main> tag found or no content for statistics calculation in '{$filePath}'.\n"; // Debug: Main tag not found
        flush();
    }
}

// Debug: Check If Iterator Found Any Files
if (!$iteratorFoundFiles) { 
    echo "DEBUG: The RecursiveDirectoryIterator did not find any files to process in '{$baseDirectory}'. Please check the directory path and its contents.\n";
}

// Notify When Finished And Display Total
echo "\nProcessing complete! Total HTML files processed: {$filesProcessedCount}\n";
flush();

?>