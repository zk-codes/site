<?php

// Configure The Base Directory
$baseDirectory = __DIR__;

// Git Repository Root Detection
$gitRepoRoot = '';
$gitOutput = [];
$gitReturnVar = 0;

// Try To Find Git Repository Root
exec("git rev-parse --show-toplevel 2>&1", $gitOutput, $gitReturnVar);

if ($gitReturnVar === 0 && !empty($gitOutput[0])) {
    $gitRepoRoot = rtrim($gitOutput[0], DIRECTORY_SEPARATOR);
    echo "Git repository root detected: '{$gitRepoRoot}'\n";
} else {
    echo "Warning: Couldn't detect Git repository root. Git commands might not work as expected. Output: " . implode("\n", $gitOutput) . "\n";
    // Fallback To baseDirectory If Git Root Can't Be Found
    $gitRepoRoot = $baseDirectory;
}

// Files To Exclude From Processing
    // By Basename (e.g. 'index.html')
    $excludedFiles = ['cv.html', 'index.html', 'resume.html'];
    // By Directory Path Relative To $baseDirectory (e.g. '/assets/templates')
    $excludedPaths = ['/assets/templates'];

// Prepare Excluded Directory Absolute Paths For Checking
$excludedFullPaths = [];
foreach ($excludedPaths as $dir) {
    $fullPath = realpath($baseDirectory . $dir);
    if ($fullPath !== false) {
        $excludedFullPaths[] = $fullPath . DIRECTORY_SEPARATOR;
    }
}

// Iterating Over Each HTML File Recursively
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDirectory, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($iterator as $file) {
    // Ensure Processing HTML Files Only
    if (!$file->isFile() || $file->getExtension() !== 'html') {
        continue;
    }

    // Get Full Path And Basename For Current File
    $filePath = $file->getPathname();
    $fileName = $file->getBasename();

    // Skip Excluded Files By Basename
    if (in_array($fileName, $excludedFiles)) {
        echo "Skipping excluded file (by basename): '{$filePath}'\n";
        continue;
    }

    // Skip Files Inside Excluded Directories
    $fileRealPath = realpath($filePath);
    $skippedInDir = false;
    foreach ($excludedFullPaths as $excludedFullPath) {
        if ($fileRealPath !== false && strpos($fileRealPath, $excludedFullPath) === 0) {
            echo "Skipping file in excluded directory: '{$filePath}'\n";
            $skippedInDir = true;
            break;
        }
    }
    if ($skippedInDir) {
        continue;
    }

    // Read File Content With Error Handling
    $html = file_get_contents($filePath);
    if ($html === false) {
        echo "Error: Could not read content from file: '{$filePath}'\n";
        continue;
    }

    // Remove Any Existing Statistics Pattern From The HTML
    $statsPattern = '/<p><strong>Statistics<\/strong> &rarr; Word Count:.*? \| Reading Time:.*?<\/p>/is';
    $html = preg_replace($statsPattern, '', $html);

    // Find Content Inside <main> tag
    if (preg_match('/<main[^>]*>(.*?)<\/main>/is', $html, $matches)) {

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
        $copySharePattern = '/(<p><strong>Copy &amp; share<\/strong><\/p>)/is';
        if (preg_match($copySharePattern, $html)) {
            $updatedHtml = preg_replace($copySharePattern, "$1\n{$newStatsContent}", $html);
        } else {
            // Fallback: If "Copy & Share" Tag Not Found, Insert Before Closing </main> Tag
            $updatedHtml = preg_replace('/(<\/main>)/is', "{$newStatsContent}\n$1", $html);
        }

        // Save Changes With Error Handling
        if (file_put_contents($filePath, $updatedHtml) === false) {
            echo "Error: Could not write updated content to file: '{$filePath}'\n";
        } else {
            echo "Updated '{$filePath}' with word count: {$wordCount} and reading time: {$formattedReadingTime}\n";

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
        }

    // If No <main> Tag Found, Or No content For Statistics Salculation
    } else {
        echo "No <main> tag found in '{$filePath}', or no content for statistics calculation.\n";
    }
}

// Notify When Finished
echo "Processing complete!\n";

?>