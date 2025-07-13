<?php
session_start(); // Start the session at the very beginning

// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Maze Generation & Solving Functions (Unchanged) ---

/**
 * Generates maze data using the Recursive Backtracker algorithm.
 * @param int $width The number of cells circumferentially.
 * @param int $height The number of cells radially.
 * @return array The grid representing the maze.
 */
function generateMazeData(int $width, int $height): array {
    $maze = array_fill(0, $height, array_fill(0, $width, []));
    $stack = [];

    // Start carving from a random cell
    $startX = rand(0, $width - 1);
    $startY = rand(0, $height - 1);
    $visited = array_fill(0, $height, array_fill(0, $width, false));
    $visited[$startY][$startX] = true;
    
    array_push($stack, [$startX, $startY]);

    while (count($stack) > 0) {
        [$cx, $cy] = end($stack);
        $directions = ['N' => [0, -1], 'S' => [0, 1], 'W' => [-1, 0], 'E' => [1, 0]];
        $neighbors = [];

        foreach ($directions as $dir => $move) {
            $nx = $cx + $move[0];
            $ny = $cy + $move[1];

            // Handle wrapping for 'E' and 'W' directions for a circular topology
            if ($dir === 'W' && $cx === 0) $nx = $width - 1;
            if ($dir === 'E' && $cx === $width - 1) $nx = 0;

            if ($nx >= 0 && $nx < $width && $ny >= 0 && $ny < $height && !$visited[$ny][$nx]) {
                $neighbors[$dir] = [$nx, $ny];
            }
        }

        if (count($neighbors) > 0) {
            $dir = array_rand($neighbors);
            [$nx, $ny] = $neighbors[$dir];
            
            // Carve path
            $maze[$cy][$cx][$dir] = true;
            $oppositeDir = ['N' => 'S', 'S' => 'N', 'E' => 'W', 'W' => 'E'];
            $maze[$ny][$nx][$oppositeDir[$dir]] = true;
            
            $visited[$ny][$nx] = true;
            array_push($stack, [$nx, $ny]);
        } else {
            array_pop($stack);
        }
    }
    return $maze;
}


/**
 * Solves the maze using a breadth-first search.
 * @return array The path from start to end.
 */
function solveMaze(array &$maze, int $startX, int $startY, int $endX, int $endY): array {
    $width = count($maze[0]);
    $height = count($maze);
    $queue = [[[$startX, $startY]]];
    $visited = array_fill(0, $height, array_fill(0, $width, false));
    $visited[$startY][$startX] = true;

    while (count($queue) > 0) {
        $path = array_shift($queue);
        [$x, $y] = end($path);

        if ($x === $endX && $y === $endY) {
            return $path;
        }

        foreach ($maze[$y][$x] as $dir => $isOpen) {
            if ($isOpen) {
                $move = ['N' => [0, -1], 'S' => [0, 1], 'W' => [-1, 0], 'E' => [1, 0]][$dir];
                $nx = $x + $move[0];
                $ny = $y + $move[1];

                // Handle wrapping for 'E' and 'W' directions
                if ($dir === 'W' && $x === 0) $nx = $width - 1;
                if ($dir === 'E' && $x === $width - 1) $nx = 0;

                if (!$visited[$ny][$nx]) {
                    $visited[$ny][$nx] = true;
                    $newPath = $path;
                    array_push($newPath, [$nx, $ny]);
                    array_push($queue, $newPath);
                }
            }
        }
    }
    return [];
}


/**
 * Draws the circular maze image and returns it as a Base64 encoded string. (Corrected path centering)
 * @param array      $maze         The maze data.
 * @param array|null $solutionPath The solution path, or null for the puzzle.
 * @param int        $endX         Exit column.
 * @param int        $endY         Exit row.
 * @return string The Base64 encoded PNG image data.
 */
function drawCircularMazeImage(array $maze, ?array $solutionPath, int $endX, int $endY): string
{
    $imgSize       = 1500;
    $padding       = 50;
    $lineThickness = 4;

    $rings   = count($maze);
    $sectors = count($maze[0]);

    $centerX      = $imgSize / 2;
    $centerY      = $imgSize / 2;
    $outerRadius  = $imgSize / 2 - $padding;
    $ringHeight   = $outerRadius / $rings;
    $sectorAngle  = 360 / $sectors;

    $image = imagecreatetruecolor($imgSize, $imgSize);

    // Allocate colors
    $bgColor       = imagecolorallocate($image, 255, 255, 255);
    $wallColor     = imagecolorallocate($image, 18, 18, 18);
    $solutionColor = imagecolorallocate($image, 220, 38, 127);
    $centerColor   = imagecolorallocate($image, 18, 18, 18);

    imagefill($image, 0, 0, $bgColor);
    imagesetthickness($image, $lineThickness);

    // Draw maze walls
    for ($r = 0; $r < $rings; $r++) {
        for ($c = 0; $c < $sectors; $c++) {
            $rad_in      = ($r * $ringHeight);
            $rad_out     = (($r + 1) * $ringHeight);
            $angle_start = $c * $sectorAngle;
            $angle_end   = ($c + 1) * $sectorAngle;

            // Draw counter-clockwise radial wall
            if (!isset($maze[$r][$c]['W'])) {
                $x1 = $centerX + $rad_in * cos(deg2rad($angle_start));
                $y1 = $centerY + $rad_in * sin(deg2rad($angle_start));
                $x2 = $centerX + $rad_out * cos(deg2rad($angle_start));
                $y2 = $centerY + $rad_out * sin(deg2rad($angle_start));
                imageline($image, $x1, $y1, $x2, $y2, $wallColor);
            }

            // Draw outer arc wall
            if (!isset($maze[$r][$c]['S'])) {
                imagearc($image, $centerX, $centerY, $rad_out * 2, $rad_out * 2, $angle_start, $angle_end, $wallColor);
            }
        }
    }

    // Draw exit path
    $exit_angle_start = $endX * $sectorAngle;
    $exit_angle_end   = ($endX + 1) * $sectorAngle;
    $exit_radius      = ($endY + 1) * $ringHeight;
    imagearc($image, $centerX, $centerY, $exit_radius * 2, $exit_radius * 2, $exit_angle_start, $exit_angle_end, $bgColor);

    // Draw solution path if provided
    if ($solutionPath) {
        imagesetthickness($image, $lineThickness + 2);

        for ($i = 0; $i < count($solutionPath) - 1; $i++) {
            [$c1, $r1] = $solutionPath[$i];
            [$c2, $r2] = $solutionPath[$i + 1];

            // --- NEW LOGIC: Differentiate between Radial and Circumferential moves ---

            // Case 1: Circumferential move (in the same ring)
            if ($r1 === $r2) {
                $path_radius   = ($r1 + 0.5) * $ringHeight;
                $path_diameter = $path_radius * 2;

                // Handle the wrap-around case
                if (abs($c1 - $c2) > 1) {
                    $wall_x = $centerX + $path_radius * cos(deg2rad(0));
                    $wall_y = $centerY + $path_radius * sin(deg2rad(0));
                    $sx1    = $centerX + $path_radius * cos(deg2rad(($c1 + 0.5) * $sectorAngle));
                    $sy1    = $centerY + $path_radius * sin(deg2rad(($c1 + 0.5) * $sectorAngle));
                    $sx2    = $centerX + $path_radius * cos(deg2rad(($c2 + 0.5) * $sectorAngle));
                    $sy2    = $centerY + $path_radius * sin(deg2rad(($c2 + 0.5) * $sectorAngle));
                    imageline($image, $sx1, $sy1, $wall_x, $wall_y, $solutionColor);
                    imageline($image, $wall_x, $wall_y, $sx2, $sy2, $solutionColor);
                } else {
                    // Normal move: Draw an ARC to follow the curved path
                    $angle1_deg  = ($c1 + 0.5) * $sectorAngle;
                    $angle2_deg  = ($c2 + 0.5) * $sectorAngle;
                    $start_angle = min($angle1_deg, $angle2_deg);
                    $end_angle   = max($angle1_deg, $angle2_deg);
                    imagearc($image, $centerX, $centerY, $path_diameter, $path_diameter, $start_angle, $end_angle, $solutionColor);
                }
            }
            // Case 2: Radial move (between rings)
            else {
                // Draw a STRAIGHT LINE as this path is a straight radius
                $path_rad1   = ($r1 + 0.5) * $ringHeight;
                $path_angle1 = deg2rad(($c1 + 0.5) * $sectorAngle);
                $path_rad2   = ($r2 + 0.5) * $ringHeight;

                $sx1 = $centerX + $path_rad1 * cos($path_angle1);
                $sy1 = $centerY + $path_rad1 * sin($path_angle1);
                $sx2 = $centerX + $path_rad2 * cos($path_angle1); // Angle is the same
                $sy2 = $centerY + $path_rad2 * sin($path_angle1); // Angle is the same
                imageline($image, $sx1, $sy1, $sx2, $sy2, $solutionColor);
            }
        }
    }

    // Draw center circle (start point)
    imagefilledellipse($image, $centerX, $centerY, $ringHeight / 2, $ringHeight / 2, $centerColor);

    // Capture image data to a variable
    ob_start();
    imagepng($image);
    $imageData = ob_get_contents();
    ob_end_clean();
    imagedestroy($image);

    return 'data:image/png;base64,' . base64_encode($imageData);
}

// --- Main Script Logic ---
$maze_image_data = null;
$solution_image_data = null;
$captcha_error = '';

$expected_captcha_phrase = "Circular Maze Maker";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_captcha = trim($_POST['captcha'] ?? '');

    if (empty($user_captcha)) {
        $captcha_error = "Please type the page's title.";
    } elseif (strcasecmp($user_captcha, $expected_captcha_phrase) !== 0) {
        $captcha_error = "Incorrect page title. Please try again.";
    } else {
        // --- ADJUSTED DIMENSIONS ---
        // Reduced sectors to make inner cells wider.
        // Adjusted rings for a good visual balance.
        $grid_cols = 24; // Number of sectors (columns)
        $grid_rows = 10; // Number of rings (rows)
        
        // Define start and end points
        // The maze starts at the center (ring 0) and ends on the outer ring.
        $startX = 0;
        $startY = 0;
        $endX = rand(0, $grid_cols - 1);
        $endY = $grid_rows - 1;

        // 1. Generate the maze structure
        $maze = generateMazeData($grid_cols, $grid_rows);
        
        // 2. Solve the maze
        $solutionPath = solveMaze($maze, $startX, $startY, $endX, $endY);

        // 3. Draw the images using the circular function
        $maze_image_data = drawCircularMazeImage($maze, null, $endX, $endY);
        $solution_image_data = drawCircularMazeImage($maze, $solutionPath, $endX, $endY);
    }
}

?>
<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
        <title>Circular Maze Maker | Zachary Kai</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="/assets/icon.ico" type="image/x-icon">
        <link rel="stylesheet" href="/assets/style.css">
        <link rel="stylesheet" href="/assets/styles/print.css" media="print">
        <link href="/assets/manifest.json" rel="manifest"/>
        <link rel="alternate" type="application/rss+xml" title="Zachary Kai" href="/assets/rss.xml">
        <link rel="webmention" href="https://webmention.io/zacharykai.net/webmention" />
        <link rel="canonical" href="https://zacharykai.net/tools/circlemaze">
        <meta name="date" content="2025-07-09">
        <meta name="last-modified" content="2025-07-09">
        <meta name="description" content="Click the button to create a random circular maze and its solution!">
    </head>
    <body>
        <p><a href="#top" class="essentials">Begin reading...</a></p>
        <header><nav><a href="/">Zachary Kai</a></nav></header>
        <main class="h-entry e-content">
            <header>
                <p class="breadcrumbs"><a href="/">Homepage</a> • <a href="/sitemap#tools">Tools</a></p>
                <h1 class="p-name">Circular Maze Maker</h1>
                <p class="postmeta">
                    <strong>Published</strong>: <time class="dt-published" datetime="2025-07-09">9 Jul 2025</time> |
                    <strong>Updated</strong>: <time class="dt-modified" datetime="2025-07-09">9 Jul 2025</time>
                </p>
            </header>
            <p id="top" class="p-summary">Click the button to create a random circular maze and its solution!</p>

            <section class="maze-form">
                <form action="/tools/circlemaze" method="post">
                    <label for="captcha">Type in this page's title:</label><br>
                    <input type="text" id="captcha" name="captcha" required>
                    <button type="submit">Make A Maze</button>
                </form>
            </section>

            <?php if ($maze_image_data && $solution_image_data): ?>
            <section class="maze-container">
                <h2>Your Circular Maze</h2>
                <p>Happy puzzling! You can right-click or long-press on the image to save it.</p>
                <img src="<?= htmlspecialchars($maze_image_data) ?>" alt="Generated circular maze puzzle">
                <hr>
                <h2>Solution</h2>
                <details>
                    <summary><strong>Click To Reveal The Solution</strong></summary>
                    <img src="<?= htmlspecialchars($solution_image_data) ?>" alt="Solution to the circular maze" style="padding-top: 1.2em;">
                </details>
            </section>
            <?php endif; ?>

            <p>•--♡--•</p>
            <section class="essentials">
                <p><strong>Copy & Share</strong>: <a href="/tools/circlemaze" class="u-url">zacharykai.net/tools/circlemaze</a></p>
                <p><strong>Statistics</strong> &rarr; Word Count: 44 | Reading Time: 0:13</p>
                <hr>
                <p>
                    <strong>Enjoyed This? Support What I Do:</strong>
                    <a href="/paypal" rel="noopener">PayPal</a> |
                    <a href="/stripe" rel="noopener">Stripe</a>
                </p>
                <hr>
                <p>
                    <strong>Reply Via</strong>:
                    <a href="/contact">Email</a> | 
                    <a href="/guestbook">Guestbook</a> |
                    <a href="/unoffice-hours">UnOffice Hours</a> | 
                    <a href="/webmention" rel="noopener">Webmention</a>
                </p>
                <p>
                    <strong>Found An Error?</strong>
                    <a href="/contact" rel="noopener">Suggest An Edit</a> |
                    <a href="/source" rel="noopener">View Source Code</a>
                </p>
            </section>
        </main>
        <section class="h-card vcard">
            <section class="h-card-image">
                <picture>
                    <source srcset="/assets/zk_icon.webp" type="image/webp">
                    <img class="u-photo" loading="lazy" src="/assets/zk_icon.png" alt="Zachary Kai's digital drawing: 5 stacked books (blue/teal/green/purple, black spine designs), green plant behind top book, purple heart on either side.">
                </picture>
            </section>
            <section class="h-card-content">
                <p><strong><a class="u-url u-id p-name" href="https://zacharykai.net" rel="me"><span class="fn">Zachary Kai</span></a></strong> — <span class="p-pronouns">he/him</span> | <a class="u-email email" href="mailto:hi@zacharykai.net" rel="me">hi@zacharykai.net</a></p>
                <p class="p-note">Zachary Kai is a space fantasy writer, offbeat queer, traveler, zinester, and avowed generalist. The internet is his livelihood and lifeline.</p>
            </section>
        </section>
        <section class="acknowledgement">
            <h2>Acknowledgement Of Country</h2>
            <p>I acknowledge the folks whose lands I owe my existence to: the Koori people. The traditional owners, storytellers, and first peoples. This land's been tended and lived alongside for millennia with knowledge passed down through generations. What a legacy. May it prevail.</p>
        </section>
        <p><a href="#top" class="essentials">Read again...</a></p>
        <footer>
            <p>Est. 2024 || 
                <a href="/about">About</a> | 
                <a href="/colophon">Accessibility & Colophon</a> | 
                <a href="/changelog">Changelog</a> | 
                <a href="/cv">CV</a> | 
                <a href="/hello">Contact</a> | 
                <a href="/newsletter">Newsletter</a> | 
                <a href="/random">Random</a> | 
                <a href="/assets/rss.xml">RSS</a> |  
                <a href="/sitemap">Sitemap</a>
            </p>
            <p class="elsewhere">Elsewhere || 
                <a href="/github" rel="noopener">Github</a> | 
                <a href="/indieweb" rel="noopener">Indieweb</a> | 
                <a href="/internet-archive" rel="noopener">Internet Archive</a> | 
                <a href="/linkedin" rel="noopener">Linkedin</a></p>
        </footer>
    </body>
</html>