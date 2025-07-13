<?php

// --- Sudoku Generator and Image Renderer ---
// Author: Zachary Kai
// Website: zacharykai.net
// Date: 09 Jul 2024

// This script generates a Sudoku puzzle and its solution, renders them as PNG images,
// and displays them on a webpage. It is designed to be a single, self-contained file.

class SudokuGenerator {
    private $grid;
    private $solution;

    public function __construct() {
        $this->grid = array_fill(0, 9, array_fill(0, 9, 0));
    }

    /**
     * Generates a new puzzle and its solution.
     */
    public function generate() {
        $this->fillGrid();
        $this->solution = array_map(function($row) { return $row; }, $this->grid); // Deep copy
        $this->createPuzzle();
    }

    /**
     * Fills the grid with a valid, complete Sudoku solution using backtracking.
     */
    private function fillGrid() {
        $this->solve(0, 0);
    }

    /**
     * Recursive backtracking solver.
     */
    private function solve($row, $col) {
        if ($row == 9) {
            return true; // Grid is filled
        }

        $nextRow = ($col == 8) ? $row + 1 : $row;
        $nextCol = ($col == 8) ? 0 : $col + 1;

        $numbers = range(1, 9);
        shuffle($numbers); // Randomize to get a new puzzle each time

        foreach ($numbers as $num) {
            if ($this->isSafe($row, $col, $num)) {
                $this->grid[$row][$col] = $num;
                if ($this->solve($nextRow, $nextCol)) {
                    return true;
                }
                $this->grid[$row][$col] = 0; // Backtrack
            }
        }
        return false;
    }

    /**
     * Removes digits from the solved grid to create a puzzle.
     * This implementation removes a fixed number of cells for consistent difficulty.
     * It ensures the puzzle has a single unique solution.
     */
    private function createPuzzle() {
        $cellsToRemove = 45; // Adjust for difficulty. 40-50 is a good range.
        $removed = 0;

        while ($removed < $cellsToRemove) {
            $row = rand(0, 8);
            $col = rand(0, 8);

            if ($this->grid[$row][$col] != 0) {
                $backup = $this->grid[$row][$col];
                $this->grid[$row][$col] = 0;

                // Make a copy to test for a unique solution
                $testGrid = array_map(function($r) { return $r; }, $this->grid);
                $solutions = 0;
                $this->countSolutions($testGrid, $solutions);

                if ($solutions != 1) {
                    // If not unique, restore the number and try another cell
                    $this->grid[$row][$col] = $backup;
                } else {
                    $removed++;
                }
            }
        }
    }
    
    /**
     * Counts the number of solutions for a given grid to ensure uniqueness.
     */
    private function countSolutions(&$grid, &$count) {
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($grid[$row][$col] == 0) {
                    for ($num = 1; $num <= 9; $num++) {
                        if ($this->isSafeGrid($grid, $row, $col, $num)) {
                            $grid[$row][$col] = $num;
                            $this->countSolutions($grid, $count);
                            $grid[$row][$col] = 0; // Backtrack
                        }
                    }
                    return;
                }
            }
        }
        $count++;
    }


    /**
     * Checks if a number can be placed in a specific cell in the main grid.
     */
    private function isSafe($row, $col, $num) {
        return $this->isSafeGrid($this->grid, $row, $col, $num);
    }

    /**
     * Checks if a number can be placed in a specific cell in any given grid.
     */
    private function isSafeGrid($grid, $row, $col, $num) {
        // Check row
        for ($x = 0; $x < 9; $x++) {
            if ($grid[$row][$x] == $num) return false;
        }
        // Check column
        for ($x = 0; $x < 9; $x++) {
            if ($grid[$x][$col] == $num) return false;
        }
        // Check 3x3 subgrid
        $startRow = $row - $row % 3;
        $startCol = $col - $col % 3;
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                if ($grid[$i + $startRow][$j + $startCol] == $num) return false;
            }
        }
        return true;
    }

    public function getPuzzle() {
        return $this->grid;
    }

    public function getSolution() {
        return $this->solution;
    }
}

/**
 * Renders a Sudoku grid as a PNG image.
 *
 * @param array $grid The 9x9 Sudoku grid data.
 * @param string $filename The path to save the PNG file.
 * @param string $fontPath Path to the TTF font file.
 */
function renderSudokuImage($grid, $filename, $fontPath) {
    // --- Image Setup ---
    $imageSize = 1500;
    $cellSize = $imageSize / 9;
    $image = imagecreatetruecolor($imageSize, $imageSize);

    // --- Colors ---
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $lightGray = imagecolorallocate($image, 204, 204, 204);

    imagefill($image, 0, 0, $white);

    // --- Draw Grid Lines ---
    $thinLineThickness = 1;
    $thickLineThickness = 5;

    for ($i = 0; $i <= 9; $i++) {
        $pos = $i * $cellSize;
        $thickness = ($i % 3 == 0) ? $thickLineThickness : $thinLineThickness;

        // Draw horizontal line
        imagesetthickness($image, $thickness);
        imageline($image, 0, $pos, $imageSize, $pos, $black);

        // Draw vertical line
        imageline($image, $pos, 0, $pos, $imageSize, $black);
    }

    // --- Draw Numbers ---
    $fontSize = $cellSize / 2.5;
    
    if (!file_exists($fontPath)) {
        // Fallback if font is not found
        // Draw a simple error message on the image
        imagestring($image, 5, 50, 725, "Error: Font file not found at " . $fontPath, $black);
    } else {
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($grid[$row][$col] != 0) {
                    $number = $grid[$row][$col];
                    
                    // Calculate text position for centering
                    $bbox = imagettfbbox($fontSize, 0, $fontPath, $number);
                    $textWidth = $bbox[2] - $bbox[0];
                    $textHeight = $bbox[1] - $bbox[7];
                    
                    $x = ($col * $cellSize) + ($cellSize - $textWidth) / 2;
                    $y = ($row * $cellSize) + ($cellSize + $textHeight) / 2;

                    imagettftext($image, $fontSize, 0, $x, $y, $black, $fontPath, $number);
                }
            }
        }
    }
    
    // --- Save and Clean Up ---
    imagepng($image, $filename);
    imagedestroy($image);
}

// --- Main Execution ---

// Define file paths.
// The font path is now relative to the document root for server-wide consistency.
$fontFile = $_SERVER['DOCUMENT_ROOT'] . '/assets/fonts/ebgaramond.ttf';
// Image files are saved in the same directory as this script.
$puzzleImageFile = __DIR__ . '/puzzle.png';
$solutionImageFile = __DIR__ . '/solution.png';

// 1. Generate the Sudoku
$sudoku = new SudokuGenerator();
$sudoku->generate();

// 2. Get the puzzle and solution grids
$puzzleGrid = $sudoku->getPuzzle();
$solutionGrid = $sudoku->getSolution();

// 3. Render the images
renderSudokuImage($puzzleGrid, $puzzleImageFile, $fontFile);
renderSudokuImage($solutionGrid, $solutionImageFile, $fontFile);

// The PHP logic is complete. The script will now proceed to render the HTML below.
// The img tags will point to the 'puzzle.png' and 'solution.png' files that were just created.

?>
<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
        <title>Sudoku Puzzle | Zachary Kai</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="/assets/icon.ico" type="image/x-icon">
        <link rel="stylesheet" href="/assets/style.css">
        <link rel="stylesheet" href="/assets/styles/print.css" media="print">
        <link href="/assets/manifest.json" rel="manifest"/>
        <link rel="alternate" type="application/rss+xml" title="Zachary Kai" href="/assets/rss.xml">
        <link rel="webmention" href="https://webmention.io/zacharykai.net/webmention" />
        <link rel="canonical" href="https://zacharykai.net/tools/sudoku">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
        <meta name="date" content="2024-07-09">
        <meta name="last-modified" content="2024-07-09">
        <meta name="description" content="A dynamically generated Sudoku puzzle and its solution.">
    </head>
    <body>
        <p><a href="#top" class="essentials">Begin reading...</a></p>
        <header><nav><a href="/">Zachary Kai</a></nav></header>
        <main class="h-entry e-content">
            <header>
                <p class="breadcrumbs"><a href="/">Homepage</a> • <a href="/tools">Tools</a> •</p>
                <h1 class="p-name">Sudoku Generator</h1>
                <p class="postmeta">
                    <strong>Published</strong>: <time class="dt-published" datetime="2024-07-09">09 Jul 2024</time> |
                    <strong>Updated</strong>: <time class="dt-modified" datetime="2024-07-09">09 Jul 2024</time>
                </p>
            </header>
            <p id="top" class="p-summary">Here is a new Sudoku puzzle for you to solve. The solution is provided below. Refresh the page to get a new puzzle.</p>
            
            <section>
                <h2>Puzzle</h2>
                <p>A new 9x9 Sudoku grid. Good luck!</p>
                <img src="puzzle.png?t=<?php echo time(); ?>" alt="Sudoku Puzzle">
            </section>            
            <section>
                <h2>Solution</h2>
                <p>Stuck? Here is the solution to the puzzle above.</p>
                <img src="solution.png?t=<?php echo time(); ?>" alt="Sudoku Solution">
            </section>
            <p>•--♡--•</p>
            <section class="essentials">
                <p><strong>Copy & Share</strong>: <a href="/tools/sudoku" class="u-url">zacharykai.net/tools/sudoku</a></p>
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