<?php

// Configure The Directory And File Pattern
$directory = '/';
$filePattern = '*.html';

// Files To Exclude From Processing
$excludedFiles = ['cv.html', 'index.html', 'resume.html','/assets/templates'];

// Iterating Over Each HTML File
foreach (glob($directory . '/' . $filePattern) as $file) {
    // Get The File's Base Name For Exclusions
    $fileName = basename($file);

    // Skip Excluded Files
    if (in_array($fileName, $excludedFiles)) {
        echo "Skipping excluded file: '{$file}'\n";
        continue;
    }

    $html = file_get_contents($file);

    // Find Content Inside <main>
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

        // Format The Reading Time
        $minutes = floor($totalSeconds / 60);
        $seconds = $totalSeconds % 60;
        $formattedReadingTime = sprintf('%d:%02d', $minutes, $seconds);

        // Full Statistics Pattern To Update
        $newStatsContent = "<p><strong>Statistics</strong> &rarr; Word Count: {$wordCount} | Reading Time: {$formattedReadingTime}</p>";

        // Check If Statistics Pattern Already Exists
        if (preg_match('/<p><strong>Statistics<\/strong> &rarr; Word Count:.*? \| Reading Time:.*?<\/p>/is', $html)) {
            // Replace Existing Statistics Pattern
            $updatedHtml = preg_replace('/<p><strong>Statistics<\/strong> &rarr; Word Count:.*? \| Reading Time:.*?<\/p>/is', $newStatsContent, $html);
        } else {
            // If Not Found, Add After Closing </main> Tag
            $updatedHtml = preg_replace('/(<\/main>)/is', "$1\n{$newStatsContent}", $html);
        }

        // Save Changes
        file_put_contents($file, $updatedHtml);
        echo "Updated '{$file}' with word count: {$wordCount} and reading time: {$formattedReadingTime}\n";

    // If No <main> Tag Found
    } else {
        echo "No <main> tag found in '{$file}', skipping statistics update.\n";
    }
}

?>
