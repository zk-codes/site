<?php

/**
 * imgcompiler.php - Resizes and optimizes images.
 *
 * This script processes images located in the 'assets/imgs/imgcompiler/' directory.
 * It resizes images to a target width of 900 pixels while maintaining aspect ratio,
 * and then overwrites the original files with optimized JPEG versions (90% quality).
 *
 * This script is designed to be run from the command line from the main project folder.
 * 
 * It uses the 'gd' extension.
 *
 * Usage: 
 * 
 * cd C:\Users\mail\OneDrive\Documents\MEGA\projects\sites\zk
 * php assets/scripts/imgcompiler.php
 *
 */

// Define the source and destination directory for images.
// Images are processed in place within the 'imgcompiler' subfolder.
$imageDir = __DIR__ . '/../imgs/imgcompiler/';

echo "Starting image compilation...\n";
echo "Looking for images in: " . $imageDir . "\n\n";

// Supported image extensions for processing.
$allowedExtensions = ['*.jpg', '*.jpeg', '*.png', '*.gif'];
$imagesFound = false; // Flag to track if any images were found and processed.

// Iterate over each allowed image extension.
foreach ($allowedExtensions as $extension) {
    // Use glob to find all files matching the current extension in the specified directory.
    $files = glob($imageDir . $extension);

    // If no files of the current type are found, skip to the next extension.
    if (empty($files)) {
        continue;
    }

    $imagesFound = true; // Set flag as images have been found.

    // Process each image file found.
    foreach ($files as $filePath) {
        echo "Processing: " . basename($filePath) . "\n";

        // Get image information to determine its type, width, and height.
        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            echo "  Error: Could not get image size for " . basename($filePath) . ". Skipping.\n";
            continue; // Skip to the next file if image info can't be retrieved.
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $type = $imageInfo[2]; // e.g., IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF.

        $sourceImage = null; // Initialize source image resource.

        // Load the image based on its detected type.
        switch ($type) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($filePath);
                break;
            default:
                echo "  Error: Unsupported image type for " . basename($filePath) . ". Skipping.\n";
                // 'continue 2' breaks out of the current (inner) foreach loop and moves to the next file
                // in the outer foreach loop (over files).
                continue 2;
        }

        // Check if the image failed to load.
        if (!$sourceImage) {
            echo "  Error: Could not load image " . basename($filePath) . ". Skipping.\n";
            continue; // Skip to the next file.
        }

        // Define the target width for the resized image.
        $targetWidth = 900;
        // Calculate the new height to maintain the original aspect ratio.
        $targetHeight = (int)(($height / $width) * $targetWidth);

        // Create a new true color image resource with the target dimensions.
        $destinationImage = imagecreatetruecolor($targetWidth, $targetHeight);

        // Resample (resize) the image from source to destination.
        imagecopyresampled(
            $destinationImage, // Destination image resource.
            $sourceImage,      // Source image resource.
            0, 0,              // Destination x, y coordinates (top-left).
            0, 0,              // Source x, y coordinates (top-left).
            $targetWidth,      // Destination width.
            $targetHeight,     // Destination height.
            $width,            // Source width.
            $height            // Source height.
        );

        // Define the output file path (overwriting the original file).
        $outputFilePath = $filePath;

        // Save the new image as a JPEG with 90% quality, overwriting the original.
        if (imagejpeg($destinationImage, $outputFilePath, 90)) {
            echo "  Resized and optimized successfully.\n";
        } else {
            echo "  Error: Could not save the optimized image.\n";
        }

        // Free up memory by destroying the image resources.
        imagedestroy($sourceImage);
        imagedestroy($destinationImage);
    }
}

// Provide feedback if no images were found throughout the entire process.
if (!$imagesFound) {
    echo "No images found in " . $imageDir . ". Please ensure there are images (JPG, JPEG, PNG, GIF) in this folder.\n";
} else {
    echo "\nImage compilation complete!\n";
}

?>
