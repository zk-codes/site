<?php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

?>
<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
        <title>Circular Maze Generator | Zachary Kai</title>
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
        <meta name="description" content="A simple tool to generate a random circular maze and its solution.">
        <style>
            .maze-form, .maze-container {
                text-align: center;
            }
            .maze-container img {
                max-width: 100%;
                height: auto;
                display: block;
                margin: 1em auto;
                border: 1px solid #ddd;
            }
            .maze-container details { 
                margin-top: 1.5em; 
            }
            .maze-form button {
                font-size: 1.1em;
                padding: 0.5em 1em;
                cursor: pointer;
            }
        </style>
    </head>
    <body>
        <p><a href="#top" class="essentials">Begin reading...</a></p>
        <header><nav><a href="/">Zachary Kai</a></nav></header>
        <main>
            <header>
                <p class="breadcrumbs"><a href="/">Homepage</a> • <a href="/tools">Tools</a> •</p>
                <h1 class="p-name">Circular Maze Generator</h1>
                <p class="postmeta">
                    <strong>Published</strong>: <time class="dt-published" datetime="2025-07-09">09 Jul 2025</time> |
                    <strong>Updated</strong>: <time class="dt-modified" datetime="2025-07-09">09 Jul 2025</time>
                </p>
            </header>
            <p id="top" class="p-summary">A simple tool to generate a random circular maze. Click the button below to create a new puzzle and its solution, starting from the center.</p>

            <section class="maze-form">
                <form action="/tools/circlemaze" method="post">
                    <button type="submit">Generate New Maze</button>
                </form>
            </section>

            <?php if ($maze_image_data && $solution_image_data): ?>
            <section class="maze-container">
                <hr>
                <h2>Your Maze  Maze</h2>
                <p>Right-click or long-press the image to save it.</p>
                <img src="<?= htmlspecialchars($maze_image_data) ?>" alt="Generated circular maze puzzle">

                <h2>Solution</h2>
                <details>
                    <summary><strong>Click to reveal the solution</strong></summary>
                    <img src="<?= htmlspecialchars($solution_image_data) ?>" alt="Solution to the circular maze">
                </details>
            </section>
            <?php endif; ?>

            <p>•--♡--•</p>
            <section class="essentials">
                <p><strong>Copy & Share</strong>: <a href="/tools/circlemaze" class="u-url">zacharykai.net/tools/circlemaze</a></p>
                </section>
        </main>
        </body>
</html>