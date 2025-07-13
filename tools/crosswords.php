<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
        <title>Crossword Puzzle Creator | Zachary Kai</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="/assets/icon.ico" type="image/x-icon">
        <link rel="stylesheet" href="/assets/style.css">
        <link rel="stylesheet" href="/assets/styles/print.css" media="print">
        <link href="/assets/manifest.json" rel="manifest"/>
        <link rel="alternate" type="application/rss+xml" title="Zachary Kai" href="/assets/rss.xml">
        <link rel="webmention" href="https://webmention.io/zacharykai.net/webmention" />
        <link rel="canonical" href="https://zacharykai.net/crossword-creator">
        <meta name="date" content="2025-07-08">
        <meta name="last-modified" content="2025-07-08">
        <meta name="description" content="Create custom crossword puzzles and export them as PNG images with transparent backgrounds.">
        <style>
            .puzzle-form {
                margin: 2em 0;
            }
            .puzzle-form input, .puzzle-form select, .puzzle-form textarea {
                margin: 0.5em 0;
                padding: 0.5em;
                width: 100%;
                max-width: 300px;
            }
            .puzzle-form textarea {
                max-width: 600px;
                height: 300px;
            }
            .puzzle-form button {
                padding: 0.5em 1em;
                margin: 1em 0;
            }
            .puzzle-preview {
                margin: 2em 0;
            }
            .puzzle-preview img {
                max-width: 100%;
                border: 1px solid #ccc;
            }
            .download-links {
                margin: 1em 0;
            }
            .download-links a {
                margin-right: 1em;
            }
            .error-message {
                color: #d00;
                padding: 1em;
                border: 1px solid #d00;
                margin: 1em 0;
            }
            .clues-section {
                margin: 2em 0;
            }
            .clues-columns {
                display: flex;
                gap: 3em;
                flex-wrap: wrap;
            }
            .clues-column {
                flex: 1;
                min-width: 300px;
            }
            .clues-list {
                counter-reset: clue-counter;
                padding-left: 2em;
            }
            .clues-list li {
                margin: 0.5em 0;
                list-style: none;
                position: relative;
            }
            .clues-list li::before {
                content: attr(data-number) ". ";
                position: absolute;
                left: -2em;
                font-weight: bold;
            }
            .clue-answer {
                display: block;
                font-style: italic;
                color: #666;
                font-size: 0.9em;
                margin-top: 0.2em;
            }
        </style>
    </head>
    <body>
        <p><a href="#top" class="essentials">Begin reading...</a></p>
        <header><nav><a href="/">Zachary Kai</a></nav></header>
        <main class="h-entry e-content">
            <header>
                <p class="breadcrumbs"><a href="/">Homepage</a> • <a href="/tools">Tools</a> •</p>
                <h1 class="p-name">Crossword Puzzle Creator</h1>
                <p class="postmeta">
                    <strong>Published</strong>: <time class="dt-published" datetime="2025-07-08">08 Jul 2025</time> | 
                    <strong>Updated</strong>: <time class="dt-modified" datetime="2025-07-08">08 Jul 2025</time>
                </p>
            </header>
            <p id="top" class="p-summary">Create custom crossword puzzles and export them as PNG images with transparent backgrounds.</p>
            
            <?php
            // Configuration
            $outputGridSize = 18; // Fixed output grid size
            $targetImageSize = 1500; // Target image size in pixels
            $cellSize = floor($targetImageSize / $outputGridSize); // Calculate cell size for 1500x1500 output
            $fontSize = floor(27 * ($cellSize / 83)); // Scale font size proportionally
            $numberFontSize = floor(17 * ($cellSize / 83)); // Scale number font size proportionally
            
            // Function to find EB Garamond font
            function findEBGaramondFont() {
                // Use the specific path requested
                $fontPath = $_SERVER['DOCUMENT_ROOT'] . '/assets/fonts/ebgaramond.ttf';
                
                if (file_exists($fontPath)) {
                    return $fontPath;
                }
                
                // Fallback paths if the main one doesn't exist
                $fallbackPaths = [
                    __DIR__ . '/assets/fonts/ebgaramond.ttf',
                    '/assets/fonts/ebgaramond.ttf',
                    $_SERVER['DOCUMENT_ROOT'] . '/assets/fonts/EBGaramond-Regular.ttf',
                    __DIR__ . '/assets/fonts/EBGaramond-Regular.ttf',
                ];
                
                foreach ($fallbackPaths as $path) {
                    if (file_exists($path)) {
                        return $path;
                    }
                }
                
                return null;
            }
            
            // Crossword generation class
            class CrosswordGenerator {
                private $words = [];
                private $grid = [];
                private $placedWords = [];
                private $gridSize = 18; // Working grid size
                
                public function __construct($wordsAndClues) {
                    // Parse and sort words by length (longest first)
                    foreach ($wordsAndClues as $item) {
                        $this->words[] = [
                            'word' => strtoupper($item['word']),
                            'clue' => $item['clue'],
                            'placed' => false
                        ];
                    }
                    usort($this->words, function($a, $b) {
                        return strlen($b['word']) - strlen($a['word']);
                    });
                    
                    // Initialize grid
                    $this->grid = array_fill(0, $this->gridSize, array_fill(0, $this->gridSize, null));
                }
                
                public function generate() {
                    // Place first word in center
                    if (!empty($this->words)) {
                        $firstWord = $this->words[0];
                        $startRow = floor($this->gridSize / 2);
                        $startCol = floor(($this->gridSize - strlen($firstWord['word'])) / 2);
                        
                        $this->placeWord($firstWord['word'], $startRow, $startCol, 'across', 0);
                        $this->words[0]['placed'] = true;
                        
                        // Try to place remaining words
                        $maxAttempts = 100;
                        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                            $allPlaced = true;
                            foreach ($this->words as $index => $word) {
                                if (!$word['placed']) {
                                    $allPlaced = false;
                                    if ($this->tryPlaceWord($index)) {
                                        $this->words[$index]['placed'] = true;
                                    }
                                }
                            }
                            if ($allPlaced) break;
                        }
                    }
                    
                    return $this->placedWords;
                }
                
                private function tryPlaceWord($wordIndex) {
                    $word = $this->words[$wordIndex]['word'];
                    
                    // Try to intersect with each placed word
                    foreach ($this->placedWords as $placedWord) {
                        $intersections = $this->findIntersections($word, $placedWord['word']);
                        
                        foreach ($intersections as $intersection) {
                            $newDir = ($placedWord['direction'] == 'across') ? 'down' : 'across';
                            
                            if ($placedWord['direction'] == 'across') {
                                $newRow = $placedWord['row'] - $intersection['new'];
                                $newCol = $placedWord['col'] + $intersection['placed'];
                            } else {
                                $newRow = $placedWord['row'] + $intersection['placed'];
                                $newCol = $placedWord['col'] - $intersection['new'];
                            }
                            
                            if ($this->canPlaceWord($word, $newRow, $newCol, $newDir)) {
                                $this->placeWord($word, $newRow, $newCol, $newDir, $wordIndex);
                                return true;
                            }
                        }
                    }
                    
                    return false;
                }
                
                private function findIntersections($word1, $word2) {
                    $intersections = [];
                    for ($i = 0; $i < strlen($word1); $i++) {
                        for ($j = 0; $j < strlen($word2); $j++) {
                            if ($word1[$i] == $word2[$j]) {
                                $intersections[] = ['new' => $i, 'placed' => $j];
                            }
                        }
                    }
                    return $intersections;
                }
                
                private function canPlaceWord($word, $row, $col, $direction) {
                    // Check bounds
                    if ($row < 0 || $col < 0) return false;
                    if ($direction == 'across' && $col + strlen($word) > $this->gridSize) return false;
                    if ($direction == 'down' && $row + strlen($word) > $this->gridSize) return false;
                    
                    // Check for conflicts
                    for ($i = 0; $i < strlen($word); $i++) {
                        if ($direction == 'across') {
                            $r = $row;
                            $c = $col + $i;
                        } else {
                            $r = $row + $i;
                            $c = $col;
                        }
                        
                        if (isset($this->grid[$r][$c]) && $this->grid[$r][$c] !== null && $this->grid[$r][$c] !== $word[$i]) {
                            return false;
                        }
                        
                        // Check adjacent cells (no touching parallel words)
                        if ($direction == 'across') {
                            if ($r > 0 && isset($this->grid[$r-1][$c]) && $this->grid[$r-1][$c] !== null && $this->grid[$r][$c] === null) return false;
                            if ($r < $this->gridSize-1 && isset($this->grid[$r+1][$c]) && $this->grid[$r+1][$c] !== null && $this->grid[$r][$c] === null) return false;
                        } else {
                            if ($c > 0 && isset($this->grid[$r][$c-1]) && $this->grid[$r][$c-1] !== null && $this->grid[$r][$c] === null) return false;
                            if ($c < $this->gridSize-1 && isset($this->grid[$r][$c+1]) && $this->grid[$r][$c+1] !== null && $this->grid[$r][$c] === null) return false;
                        }
                    }
                    
                    // Check word boundaries
                    if ($direction == 'across') {
                        if ($col > 0 && isset($this->grid[$row][$col-1]) && $this->grid[$row][$col-1] !== null) return false;
                        if ($col + strlen($word) < $this->gridSize && isset($this->grid[$row][$col+strlen($word)]) && $this->grid[$row][$col+strlen($word)] !== null) return false;
                    } else {
                        if ($row > 0 && isset($this->grid[$row-1][$col]) && $this->grid[$row-1][$col] !== null) return false;
                        if ($row + strlen($word) < $this->gridSize && isset($this->grid[$row+strlen($word)][$col]) && $this->grid[$row+strlen($word)][$col] !== null) return false;
                    }
                    
                    return true;
                }
                
                private function placeWord($word, $row, $col, $direction, $index) {
                    for ($i = 0; $i < strlen($word); $i++) {
                        if ($direction == 'across') {
                            $this->grid[$row][$col + $i] = $word[$i];
                        } else {
                            $this->grid[$row + $i][$col] = $word[$i];
                        }
                    }
                    
                    $this->placedWords[] = [
                        'word' => $word,
                        'clue' => $this->words[$index]['clue'],
                        'row' => $row,
                        'col' => $col,
                        'direction' => $direction
                    ];
                }
                
                public function getGrid() {
                    return $this->grid;
                }
            }
            
            // Function to create crossword puzzle PNG
            function createCrosswordPNG($placedWords, $grid, $showSolution = false) {
                global $cellSize, $fontSize, $outputGridSize, $numberFontSize;
                
                $fontPath = findEBGaramondFont();
                $useBuiltInFont = ($fontPath === null);
                
                // Always use 18x18 grid
                $gridWidth = $outputGridSize;
                $gridHeight = $outputGridSize;
                
                // Create arrays for the output grid
                $outputGrid = array_fill(0, $gridHeight, array_fill(0, $gridWidth, ''));
                $numbers = array_fill(0, $gridHeight, array_fill(0, $gridWidth, 0));
                $isBlack = array_fill(0, $gridHeight, array_fill(0, $gridWidth, true));
                
                // Copy the generated grid data
                for ($r = 0; $r < $gridHeight; $r++) {
                    for ($c = 0; $c < $gridWidth; $c++) {
                        if (isset($grid[$r][$c]) && $grid[$r][$c] !== null) {
                            $outputGrid[$r][$c] = $grid[$r][$c];
                            $isBlack[$r][$c] = false;
                        }
                    }
                }
                
                // Assign numbers - Updated logic
                $wordNumber = 1;
                $numberedWords = [];
                
                // Sort words by position
                usort($placedWords, function($a, $b) {
                    if ($a['row'] == $b['row']) {
                        return $a['col'] - $b['col'];
                    }
                    return $a['row'] - $b['row'];
                });
                
                // First pass: assign numbers to starting positions
                foreach ($placedWords as $index => $word) {
                    $row = $word['row'];
                    $col = $word['col'];
                    
                    // Check if this position already has a number
                    if ($numbers[$row][$col] == 0) {
                        $numbers[$row][$col] = $wordNumber;
                        $placedWords[$index]['number'] = $wordNumber;
                        $wordNumber++;
                    } else {
                        $placedWords[$index]['number'] = $numbers[$row][$col];
                    }
                }
                
                // Create image
                $imageWidth = $gridWidth * $cellSize;
                $imageHeight = $gridHeight * $cellSize;
                $image = imagecreatetruecolor($imageWidth, $imageHeight);
                
                // Set up transparency
                imagesavealpha($image, true);
                $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
                imagefill($image, 0, 0, $transparent);
                
                // Colors
                $black = imagecolorallocate($image, 0, 0, 0);
                $white = imagecolorallocate($image, 255, 255, 255);
                $lightGray = imagecolorallocate($image, 220, 220, 220); // Light gray for non-fillable squares
                $darkGray = imagecolorallocate($image, 150, 150, 150); // Darker gray for borders
                
                // Draw grid
                for ($r = 0; $r < $gridHeight; $r++) {
                    for ($c = 0; $c < $gridWidth; $c++) {
                        $x = $c * $cellSize;
                        $y = $r * $cellSize;
                        
                        if ($isBlack[$r][$c]) {
                            // Fill with light gray instead of black
                            imagefilledrectangle($image, $x, $y, $x + $cellSize - 1, $y + $cellSize - 1, $lightGray);
                            // Draw border to show it's still a square
                            imagerectangle($image, $x, $y, $x + $cellSize - 1, $y + $cellSize - 1, $darkGray);
                        } else {
                            // Fill white square
                            imagefilledrectangle($image, $x, $y, $x + $cellSize - 1, $y + $cellSize - 1, $white);
                            
                            // Draw cell border
                            imagerectangle($image, $x, $y, $x + $cellSize - 1, $y + $cellSize - 1, $black);
                            
                            // Draw number if present
                            if ($numbers[$r][$c] > 0) {
                                if ($useBuiltInFont) {
                                    imagestring($image, 2, $x + 4, $y + 4, $numbers[$r][$c], $black);
                                } else {
                                    imagettftext($image, $numberFontSize, 0, $x + 4, $y + $numberFontSize + 4, $black, $fontPath, $numbers[$r][$c]);
                                }
                            }
                            
                            // Draw letter if solution
                            if ($showSolution && $outputGrid[$r][$c] != '') {
                                if ($useBuiltInFont) {
                                    $fontNum = 5;
                                    $charWidth = imagefontwidth($fontNum);
                                    $charHeight = imagefontheight($fontNum);
                                    $textX = $x + ($cellSize - $charWidth) / 2;
                                    $textY = $y + ($cellSize - $charHeight) / 2;
                                    imagestring($image, $fontNum, $textX, $textY, $outputGrid[$r][$c], $black);
                                } else {
                                    $bbox = imagettfbbox($fontSize, 0, $fontPath, $outputGrid[$r][$c]);
                                    $textWidth = $bbox[2] - $bbox[0];
                                    $textHeight = $bbox[1] - $bbox[7];
                                    $textX = $x + ($cellSize - $textWidth) / 2;
                                    $textY = $y + ($cellSize + $textHeight) / 2;
                                    imagettftext($image, $fontSize, 0, $textX, $textY, $black, $fontPath, $outputGrid[$r][$c]);
                                }
                            }
                        }
                    }
                }
                
                // Return both the image and the numbered words
                return ['image' => $image, 'numberedWords' => $placedWords];
            }
            
            // Process form submission
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_puzzle'])) {
                // Parse words and clues
                $wordsAndClues = [];
                $lines = explode("\n", trim($_POST['words_data']));
                foreach ($lines as $line) {
                    $parts = explode('|', trim($line));
                    if (count($parts) >= 2) {
                        $wordsAndClues[] = [
                            'word' => trim($parts[0]),
                            'clue' => trim($parts[1])
                        ];
                    }
                }
                
                if (count($wordsAndClues) < 2) {
                    echo '<div class="error-message">Please enter at least 2 words with clues to create a puzzle.</div>';
                } else {
                    // Generate crossword
                    $generator = new CrosswordGenerator($wordsAndClues);
                    $placedWords = $generator->generate();
                    $grid = $generator->getGrid();
                    
                    if (count($placedWords) < 2) {
                        echo '<div class="error-message">Could not generate a valid crossword with the given words. Try different words or add more words that share common letters.</div>';
                    } else {
                        // Create empty puzzle
                        $emptyResult = createCrosswordPNG($placedWords, $grid, false);
                        $emptyPuzzle = $emptyResult['image'];
                        $numberedWords = $emptyResult['numberedWords'];
                        $emptyFilename = 'crossword_empty_' . time() . '.png';
                        imagepng($emptyPuzzle, $emptyFilename);
                        imagedestroy($emptyPuzzle);
                        
                        // Create solution
                        $solutionResult = createCrosswordPNG($placedWords, $grid, true);
                        $solutionPuzzle = $solutionResult['image'];
                        $solutionFilename = 'crossword_solution_' . time() . '.png';
                        imagepng($solutionPuzzle, $solutionFilename);
                        imagedestroy($solutionPuzzle);
                        
                        // Generate clues lists using numbered words
                        $acrossClues = [];
                        $downClues = [];
                        
                        foreach ($numberedWords as $word) {
                            if ($word['direction'] == 'across') {
                                $acrossClues[$word['number']] = [
                                    'number' => $word['number'],
                                    'clue' => $word['clue'],
                                    'answer' => $word['word']
                                ];
                            } else {
                                $downClues[$word['number']] = [
                                    'number' => $word['number'],
                                    'clue' => $word['clue'],
                                    'answer' => $word['word']
                                ];
                            }
                        }
                        
                        ksort($acrossClues);
                        ksort($downClues);
                        
                        ?>
                        <div class="puzzle-preview">
                            <h2>Puzzle Created Successfully!</h2>
                            
                            <p><strong>Words placed:</strong> <?php echo count($placedWords); ?> out of <?php echo count($wordsAndClues); ?></p>
                            <p><strong>Grid size:</strong> 18×18</p>
                            <p><strong>Image size:</strong> <?php echo $outputGridSize * $cellSize; ?>×<?php echo $outputGridSize * $cellSize; ?> pixels</p>
                            <?php if ($fontPath = findEBGaramondFont()): ?>
                            <p><strong>Font:</strong> EB Garamond (<?php echo basename($fontPath); ?>)</p>
                            <?php else: ?>
                            <p><strong>Font:</strong> Built-in (EB Garamond not found at /assets/fonts/ebgaramond.ttf)</p>
                            <?php endif; ?>
                            
                            <div class="download-links">
                                <a href="<?php echo $emptyFilename; ?>" download>Download Empty Puzzle (PNG)</a>
                                <a href="<?php echo $solutionFilename; ?>" download>Download Solution (PNG)</a>
                            </div>
                            
                            <h3>Empty Puzzle</h3>
                            <img src="<?php echo $emptyFilename; ?>" alt="Empty crossword puzzle">
                            
                            <h3>Solution</h3>
                            <img src="<?php echo $solutionFilename; ?>" alt="Crossword puzzle solution">
                            
                            <div class="clues-section">
                                <h3>Clues</h3>
                                <div class="clues-columns">
                                    <div class="clues-column">
                                        <h4>Across</h4>
                                        <ol class="clues-list">
                                            <?php foreach ($acrossClues as $num => $item): ?>
                                            <li data-number="<?php echo $num; ?>">
                                                <?php echo htmlspecialchars($item['clue']); ?>
                                                <span class="clue-answer">(Answer: <?php echo htmlspecialchars($item['answer']); ?>)</span>
                                            </li>
                                            <?php endforeach; ?>
                                        </ol>
                                    </div>
                                    <div class="clues-column">
                                        <h4>Down</h4>
                                        <ol class="clues-list">
                                            <?php foreach ($downClues as $num => $item): ?>
                                            <li data-number="<?php echo $num; ?>">
                                                <?php echo htmlspecialchars($item['clue']); ?>
                                                <span class="clue-answer">(Answer: <?php echo htmlspecialchars($item['answer']); ?>)</span>
                                            </li>
                                            <?php endforeach; ?>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (count($placedWords) < count($wordsAndClues)): ?>
                            <div class="clues-section">
                                <h4>Words that couldn't be placed:</h4>
                                <ul>
                                    <?php 
                                    $placedWordsList = array_map(function($w) { return $w['word']; }, $placedWords);
                                    foreach ($wordsAndClues as $item) {
                                        if (!in_array(strtoupper($item['word']), $placedWordsList)) {
                                            echo '<li><strong>' . htmlspecialchars($item['word']) . '</strong> - ' . htmlspecialchars($item['clue']) . '</li>';
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php
                    }
                }
            }
            ?>
            
            <form method="post" class="puzzle-form">
                <h2>Create New Puzzle</h2>
                
                <p><strong>Instructions:</strong> Enter your words and clues, one per line. The generator will automatically arrange them into an 18×18 crossword puzzle.</p>
                
                <p><strong>Note:</strong> Make sure you have the EB Garamond font file at <code>/assets/fonts/ebgaramond.ttf</code></p>
                
                <div>
                    <label for="words_data">Words and Clues:</label><br>
                    <textarea id="words_data" name="words_data" required placeholder="Format: WORD|Clue
Example:
HELLO|A greeting
WORLD|The planet Earth
PUZZLE|A game with pieces to solve
CROSSWORD|This type of puzzle
LETTER|A character in the alphabet
CREATE|To make something new
DESIGN|To plan the look of something"></textarea>
                    <p><small>Enter one word per line. Format: WORD|Clue<br>
                    The generator will automatically place words that share common letters in an 18×18 grid.</small></p>
                </div>
                
                <button type="submit" name="create_puzzle">Generate Puzzle</button>
            </form>
            
            <p>•--♡--•</p>
            <section class="essentials">
                <p><strong>Copy & Share</strong>: <a href="/crossword-creator" class="u-url">zacharykai.net/crossword-creator</a></p>
                <p><strong>Statistics</strong> &rarr; Word Count: 250 | Reading Time: 1:15</p>
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