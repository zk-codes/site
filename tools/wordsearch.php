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
        <title>Word Search Maker | Zachary Kai</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="/assets/icon.ico" type="image/x-icon">
        <link rel="stylesheet" href="/assets/style.css">
        <link rel="stylesheet" href="/assets/styles/print.css" media="print">
        <link href="/assets/manifest.json" rel="manifest"/>
        <link rel="alternate" type="application/rss+xml" title="Zachary Kai" href="/assets/rss.xml">
        <link rel="webmention" href="https://webmention.io/zacharykai.net/webmention" />
        <link rel="canonical" href="https://zacharykai.net/tools/wordsearch">
        <meta name="date" content="2025-07-13">
        <meta name="last-modified" content="2025-07-13">
        <meta name="description" content="Input a list of words and click the button to create a word search and its solution!">
        <style>
            .word-search-results img { max-width: 100%; height: auto; border: 1px solid #ddd; margin-top: 1em; }
            .word-list { column-count: 2; margin-left: 2em;}
        </style>
    </head>
    <body>
        <p><a href="#top" class="essentials">Begin reading...</a></p>
        <header><nav><a href="/">Zachary Kai</a></nav></header>
        <main class="h-entry">
            <header>
                <p class="breadcrumbs"><a href="/">Homepage</a> • <a href="/sitemap#tools">Tools</a></p>
                <h1 class="p-name">Word Search Maker</h1>
                <p class="postmeta">
                    <strong>Published</strong>: <time class="dt-published" datetime="2025-07-13">13 Jul 2025</time> |
                    <strong>Updated</strong>: <time class="dt-modified" datetime="2025-07-13">13 Jul 2025</time>
                </p>
            </header>

            <p id="top" class="p-summary">Input a list of words and click the button to create a word search and its solution!</p>
            
            <section>
                <h2>Create Your Word Search</h2>
                <form action="/tools/wordsearch" method="post" class="word-search-form">
                    <label for="words">Enter your words (separated by new lines or commas):</label><br>
                    <textarea id="words" name="words" required><?php echo isset($_POST['words']); ?></textarea>
                    <br>
                    <button type="submit">Generate Puzzle</button>
                </form>
            </section>

            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                
                <?php if (!empty($generated_content)): ?>
                    <section class="word-search-results">
                        <h2>Your Word Search</h2>

                        <?php if (!empty($unplaced_words)): ?>
                        <div class="warning-box">
                            <strong>Note:</strong> The following words could not be placed (try fewer or shorter words):
                            <?php echo htmlspecialchars(implode(', ', $unplaced_words)); ?>.
                        </div>
                        <?php endif; ?>

                        <h3>Words To Find</h3>
                        <ul class="word-list">
                            <?php foreach ($generated_content['word_list'] as $word): ?>
                                <li><?php echo htmlspecialchars($word); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <hr>
                        <h3>Puzzle</h3>
                        <img src="<?php echo $generated_content['puzzle_uri']; ?>" alt="Word Search">
                        <hr>
                        <details style="padding-top: 1.2em;">
                            <summary><strong>Click To Reveal The Solution</strong></summary>
                            <h3>Solution</h3>
                            <img src="<?php echo $generated_content['solution_uri']; ?>" alt="Word Search Solution">
                        </details>
                        
                    </section>
                <?php endif; ?>
            <?php endif; ?>
            
            <p>•--♡--•</p>
            <section class="essentials">
                <p><strong>Copy & Share</strong>: <a href="/" class="u-url">zacharykai.net/</a></p>
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