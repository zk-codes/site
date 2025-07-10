<?php
// --- WORD SEARCH GENERATOR - BACKEND LOGIC ---

// Start a session to store puzzle data for download links.
session_start();

// --- CONFIGURATION ---
define('GRID_SIZE', 25);
define('IMAGE_SIZE', 1500);
// Path is relative to this file's location in /tools/
define('FONT_PATH', realpath(__DIR__ . '/../assets/fonts/ebgaramond.ttf')); 

// --- GLOBAL VARIABLES ---
$errors = [];
$placed_words = [];
$unplaced_words = [];
$generated_content = [];

// --- DOWNLOAD HANDLER ---
if (isset($_GET['download']) && isset($_SESSION['wordsearch_data'])) {
    $data = $_SESSION['wordsearch_data'];
    $filename = "wordsearch.png";
    $solution_data = [];

    if ($_GET['download'] === 'solution') {
        $solution_data = $data['placed_words'];
        $filename = "wordsearch_solution.png";
    }
    
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    generate_image($data['grid'], $solution_data, 'stream');
    exit;
}


// --- FORM HANDLING (POST REQUEST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. PARSE AND VALIDATE WORDS
    $raw_words = $_POST['words'];
    if (empty($raw_words)) {
        $errors[] = "No words were entered. Please provide a list of words.";
    } else {
        $words = preg_split('/[\s,]+/', $raw_words);
        $words = array_filter(array_map(function($word) {
            $clean_word = strtoupper(preg_replace("/[^a-zA-Z]/", "", $word));
            if (strlen($clean_word) > GRID_SIZE) {
                global $errors;
                $errors[] = "The word '{$clean_word}' is too long to fit in the " . GRID_SIZE . "x" . GRID_SIZE . " grid.";
            }
            return $clean_word;
        }, $words));

        if (empty($words)) {
            $errors[] = "The input contained no valid words. Please use letters only.";
        }
    }

    // 2. GENERATION LOGIC (if no errors)
    if (empty($errors)) {
        $grid = create_empty_grid();
        list($grid, $placed_words, $unplaced_words) = place_words_on_grid($grid, $words);
        $grid = fill_empty_cells($grid);

        $_SESSION['wordsearch_data'] = [
            'grid' => $grid,
            'placed_words' => $placed_words,
        ];

        $generated_content = [
            'puzzle_uri' => generate_image($grid),
            'solution_uri' => generate_image($grid, $placed_words),
            'word_list' => array_keys($placed_words)
        ];
    }
}

// --- CORE FUNCTIONS ---

function create_empty_grid() {
    return array_fill(0, GRID_SIZE, array_fill(0, GRID_SIZE, null));
}

function place_words_on_grid($grid, $words) {
    $directions = [[0, 1], [0, -1], [1, 0], [-1, 0], [1, 1], [-1, -1], [1, -1], [-1, 1]];
    $placed = [];
    $unplaced = [];
    usort($words, function($a, $b) { return strlen($b) - strlen($a); });

    foreach ($words as $word) {
        $word_len = strlen($word);
        $is_placed = false;
        $attempts = 0;
        while (!$is_placed && $attempts < 100) {
            $direction = $directions[array_rand($directions)];
            $start_row = rand(0, GRID_SIZE - 1);
            $start_col = rand(0, GRID_SIZE - 1);
            $end_row = $start_row + ($word_len - 1) * $direction[0];
            $end_col = $start_col + ($word_len - 1) * $direction[1];

            if ($end_row >= 0 && $end_row < GRID_SIZE && $end_col >= 0 && $end_col < GRID_SIZE) {
                $can_place = true;
                for ($i = 0; $i < $word_len; $i++) {
                    $row = $start_row + $i * $direction[0];
                    $col = $start_col + $i * $direction[1];
                    if ($grid[$row][$col] !== null && $grid[$row][$col] !== $word[$i]) {
                        $can_place = false;
                        break;
                    }
                }
                if ($can_place) {
                    for ($i = 0; $i < $word_len; $i++) {
                        $row = $start_row + $i * $direction[0];
                        $col = $start_col + $i * $direction[1];
                        $grid[$row][$col] = $word[$i];
                    }
                    $placed[$word] = ['start_row' => $start_row, 'start_col' => $start_col, 'end_row' => $end_row, 'end_col' => $end_col];
                    $is_placed = true;
                }
            }
            $attempts++;
        }
        if (!$is_placed) {
            $unplaced[] = $word;
        }
    }
    return [$grid, $placed, $unplaced];
}

function fill_empty_cells($grid) {
    for ($r = 0; $r < GRID_SIZE; $r++) {
        for ($c = 0; $c < GRID_SIZE; $c++) {
            if ($grid[$r][$c] === null) {
                $grid[$r][$c] = chr(rand(65, 90));
            }
        }
    }
    return $grid;
}

function generate_image($grid, $solution_words = [], $output_mode = 'base64') {
    if (!FONT_PATH || !file_exists(FONT_PATH)) {
        $im = imagecreatetruecolor(IMAGE_SIZE, 300);
        $bg = imagecolorallocate($im, 255, 255, 255);
        $red = imagecolorallocate($im, 220, 0, 0);
        imagefill($im, 0, 0, $bg);
        imagestring($im, 5, 20, 20, "FATAL ERROR: Font file not found!", $red);
        imagestring($im, 5, 20, 40, "Check the FONT_PATH in the PHP script.", $red);
        imagestring($im, 5, 20, 60, "Expected resolved path: " . (FONT_PATH ?: "Not resolved"), $red);
    } else {
        $im = imagecreatetruecolor(IMAGE_SIZE, IMAGE_SIZE);
        $bg_color = imagecolorallocate($im, 255, 255, 255);
        $text_color = imagecolorallocate($im, 18, 18, 18);
        // **CHANGE**: Highlight color is now a more readable light blue.
        $solution_highlight_color = imagecolorallocatealpha($im, 173, 216, 230, 65); // Light Blue, semi-transparent
        
        imagefill($im, 0, 0, $bg_color);
        $margin = 60;
        $cell_size = (IMAGE_SIZE - (2 * $margin)) / GRID_SIZE;
        $font_size = $cell_size * 0.6;

        for ($r = 0; $r < GRID_SIZE; $r++) {
            for ($c = 0; $c < GRID_SIZE; $c++) {
                $letter = $grid[$r][$c];
                $x_pos = $c * $cell_size + $margin + ($cell_size / 2);
                $y_pos = $r * $cell_size + $margin + ($cell_size / 2);
                $bbox = imagettfbbox($font_size, 0, FONT_PATH, $letter);
                $text_width = $bbox[2] - $bbox[0];
                $text_height = $bbox[1] - $bbox[7];
                $final_x = $x_pos - ($text_width / 2);
                $final_y = $y_pos + ($text_height / 2);
                imagettftext($im, $font_size, 0, $final_x, $final_y, $text_color, FONT_PATH, $letter);
            }
        }
        if (!empty($solution_words)) {
            // **CHANGE**: Line thickness slightly reduced for better letter visibility.
            imagesetthickness($im, $cell_size * 0.7);
            foreach ($solution_words as $word => $pos) {
                $start_x = $pos['start_col'] * $cell_size + $margin + ($cell_size / 2);
                $start_y = $pos['start_row'] * $cell_size + $margin + ($cell_size / 2);
                $end_x = $pos['end_col'] * $cell_size + $margin + ($cell_size / 2);
                $end_y = $pos['end_row'] * $cell_size + $margin + ($cell_size / 2);
                imageline($im, $start_x, $start_y, $end_x, $end_y, $solution_highlight_color);
            }
        }
    }

    if ($output_mode === 'stream') {
        imagepng($im);
    } else {
        ob_start();
        imagepng($im);
        $image_data = ob_get_contents();
        ob_end_clean();
        imagedestroy($im);
        return 'data:image/png;base64,' . base64_encode($image_data);
    }
    imagedestroy($im);
}
?>
<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
        <title>Word Search Generator | Zachary Kai</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="/assets/icon.ico" type="image/x-icon">
        <link rel="stylesheet" href="/assets/style.css">
        <link rel="canonical" href="https://zacharykai.net/tools/wordsearch">
        <meta name="description" content="A dynamic, server-side Word Search Generator. Input a list of words and receive a custom word search puzzle and corresponding answer key.">
        <style>
            .word-search-form textarea { width: 100%; min-height: 150px; font-family: monospace; padding: 10px; font-size: 1rem; border: 1px solid #ccc; }
            .word-search-form button { display: inline-block; padding: 10px 20px; background-color: #333; color: #fff; border: none; font-size: 1rem; cursor: pointer; margin-top: 10px; }
            .word-search-results, .error-box, .warning-box { margin-top: 2em; border: 1px solid #ccc; padding: 1em; }
            .error-box { border-color: #d9534f; color: #d9534f; background-color: #f2dede; }
            .warning-box { border-color: #f0ad4e; color: #a17021; background-color: #fcf8e3; }
            .word-search-results h2 { margin-top: 0; }
            .word-search-results img { max-width: 100%; height: auto; border: 1px solid #ddd; margin-top: 1em; }
            /* **CHANGE**: Removed Roman numerals for a standard bulleted list. */
            .word-list { column-count: 2; list-style-type: disc; margin-left: 2em;}
            .download-links { margin: 1.5em 0; }
            .download-links a { margin-right: 1em; font-weight: bold; }
        </style>
    </head>
    <body>
        <p><a href="#top" class="essentials">Begin reading...</a></p>
        <header><nav><a href="/">Zachary Kai</a></nav></header>
        <main class="h-entry">
            <header>
                <p class="breadcrumbs"><a href="/">Homepage</a> • Tools •</p>
                <h1 class="p-name">Word Search Generator</h1>
                <p class="postmeta">
                    <strong>Published</strong>: <time class="dt-published" datetime="2025-07-08">08 Jul 2025</time>
                </p>
            </header>

            <p id="top" class="p-summary">A dynamic tool to create your own word search puzzles. Enter your words below, separated by commas or new lines, to generate a high-resolution, printable 25x25 grid puzzle and its corresponding answer key.</p>
            
            <section>
                <h2>Create Your Puzzle</h2>
                <form action="/tools/wordsearch" method="post" class="word-search-form">
                    <label for="words">Enter your words (separated by new lines or commas):</label><br>
                    <textarea id="words" name="words" required><?php echo isset($_POST['words']) ? htmlspecialchars($_POST['words']) : 'FINAL,VERSION,READABLE,HIGHLIGHT,BLUE,STANDARD,LIST,BULLETS,ZACHARYKAI,PUZZLE,SOLUTION,PHP,SERVER,DYNAMIC'; ?></textarea>
                    <br>
                    <button type="submit">Generate Puzzle</button>
                </form>
            </section>

            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <p>•--♡--•</p>
                
                <?php if (!empty($errors)): ?>
                    <div class="error-box">
                        <strong>Could not generate puzzle due to the following errors:</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($generated_content)): ?>
                    <section class="word-search-results">
                        <h2>Your Custom Word Search</h2>

                        <?php if (!empty($unplaced_words)): ?>
                        <div class="warning-box">
                            <strong>Note:</strong> The following words could not be placed (try fewer or shorter words):
                            <?php echo htmlspecialchars(implode(', ', $unplaced_words)); ?>.
                        </div>
                        <?php endif; ?>
                        
                        <div class="download-links">
                            <a href="/tools/wordsearch?download=puzzle"><strong>Download Puzzle PNG</strong></a>
                            <a href="/tools/wordsearch?download=solution"><strong>Download Solution PNG</strong></a>
                        </div>

                        <h3>Words to Find</h3>
                        <ul class="word-list">
                            <?php foreach ($generated_content['word_list'] as $word): ?>
                                <li><?php echo htmlspecialchars($word); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <hr>
                        
                        <h3>Puzzle Preview</h3>
                        <img src="<?php echo $generated_content['puzzle_uri']; ?>" alt="Generated Word Search Puzzle Preview">

                        <h3>Solution Preview</h3>
                        <img src="<?php echo $generated_content['solution_uri']; ?>" alt="Generated Word Search Solution Preview">
                    </section>
                <?php endif; ?>
            <?php endif; ?>
            
            <p>•--♡--•</p>
            <section class="essentials">
                 <p><strong>Copy & Share</strong>: <a href="/tools/wordsearch" class="u-url">zacharykai.net/tools/wordsearch</a></p>
            </section>
        </main>
        <footer>
            <p>Est. 2024 || 
                <a href="/sitemap">Sitemap</a>
            </p>
        </footer>
    </body>
</html>