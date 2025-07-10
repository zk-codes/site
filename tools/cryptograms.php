<?php
// === PHP LOGIC FOR CRYPTOGRAM GENERATION (v6.2 - Key Spacing) ===

$puzzleImagePath = null;
$solutionImagePath = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sentence']) && !empty(trim($_POST['sentence']))) {

    // --- 1. SETUP & CONFIGURATION ---
    $fontFile = $_SERVER['DOCUMENT_ROOT'] . '/assets/fonts/ebgaramond.ttf';
    $tempDir = $_SERVER['DOCUMENT_ROOT'] . '/tools/cryptograms_temp/';
    $tempUrlDir = '/tools/cryptograms_temp/';
    
    if (!is_dir($tempDir)) { @mkdir($tempDir, 0755, true); }
    if (!file_exists($fontFile)) { die("Error: Font file not found."); }

    $uniqueId = uniqid();
    $puzzleImagePath = $tempUrlDir . 'puzzle_' . $uniqueId . '.png';
    $solutionImagePath = $tempUrlDir . 'solution_' . $uniqueId . '.png';
    $fullPuzzlePath = $tempDir . 'puzzle_' . $uniqueId . '.png';
    $fullSolutionPath = $tempDir . 'solution_' . $uniqueId . '.png';

    // Layout metrics
    $imageWidth = 1500;
    $imageHeight = 1500;
    $margin = 50;
    $numberFontSize = 60;
    $solutionLetterFontSize = 55;
    $itemWidth = 100;
    $boxHeight = 90;
    $itemSpacing = 5;
    $wordSpacing = 40;
    $itemHeight = ($boxHeight * 2) + $itemSpacing;
    $lineHeight = $itemHeight + 40;

    // --- 2. CRYPTOGRAM LOGIC ---
    $originalSentence = strtoupper(trim($_POST['sentence']));
    $alphabet = range('A', 'Z');
    $numbers = range(1, 26);
    shuffle($numbers);
    $cipherMap = array_combine($alphabet, $numbers);

    $words = preg_split('/\s+/', $originalSentence, -1, PREG_SPLIT_NO_EMPTY);
    $structuredWords = [];
    foreach ($words as $word) {
        $wordData = ['letters' => []];
        for ($i = 0; $i < strlen($word); $i++) {
            $char = $word[$i];
            $isLetter = ctype_alpha($char);
            $wordData['letters'][] = [
                'original' => $char,
                'cipher' => $isLetter ? $cipherMap[$char] : $char,
                'is_letter' => $isLetter,
            ];
        }
        $wordData['width'] = count($wordData['letters']) * $itemWidth;
        $structuredWords[] = $wordData;
    }
    
    $allLetters = array_filter(array_merge(...array_column($structuredWords, 'letters')), function($char) { return $char['is_letter']; });
    $hint = !empty($allLetters) ? $allLetters[array_rand($allLetters)] : null;
    
    // --- 3. LAYOUT & IMAGE GENERATION ---

    function calculateLayout($structuredWords, $maxWidth, $wordSpacing) {
        $lines = [];
        $currentLine = [];
        $currentWidth = 0;
        foreach ($structuredWords as $word) {
            if (!empty($currentLine) && ($currentWidth + $wordSpacing + $word['width']) > $maxWidth) {
                $lines[] = ['items' => $currentLine, 'width' => $currentWidth - $wordSpacing];
                $currentLine = [];
                $currentWidth = 0;
            }
            $currentLine[] = $word;
            $currentWidth += $word['width'] + $wordSpacing;
        }
        if (!empty($currentLine)) {
            $lines[] = ['items' => $currentLine, 'width' => $currentWidth - $wordSpacing];
        }
        return $lines;
    }
    
    $layoutLines = calculateLayout($structuredWords, $imageWidth - (2 * $margin), $wordSpacing);

function drawKey($image, $isSolution, $config) {
    extract($config);
    $black = imagecolorallocate($image, 0, 0, 0);
    $gray = imagecolorallocate($image, 180, 180, 180);
    $blue = imagecolorallocate($image, 20, 20, 200);

    // --- Controls for the Dynamic Layout ---
    $keyFontSize = 28;
    $keyPadding = 25;
    $keyBoxWidth = 75;

    $keyRows = [range('A', 'I'), range('J', 'R'), range('S', 'Z')];
    $lineSpacing = 70;
    $y = $margin;

    // --- Dynamic Layout Calculation (First Pass) ---
    $allRowsLayout = [];
    foreach ($keyRows as $row) {
        $currentRowWidth = 0;
        foreach ($row as $letter) {
            $labelText = $letter . " = ";
            $labelBox = imagettfbbox($keyFontSize, 0, $fontFile, $labelText);
            $labelWidth = $labelBox[2] - $labelBox[0];
            $currentRowWidth += $labelWidth + $keyBoxWidth;
        }
        $currentRowWidth += ($keyPadding * (count($row) - 1));
        $allRowsLayout[] = $currentRowWidth;
    }

    // --- Drawing the Key (Second Pass) ---
    foreach ($keyRows as $rowIndex => $row) {
        $rowWidth = $allRowsLayout[$rowIndex];
        $x = ($imageWidth - $rowWidth) / 2;

        foreach ($row as $letter) {
            $isHint = ($hint && $letter === $hint['original']);
            $labelText = $letter . " = ";
            $labelBox = imagettfbbox($keyFontSize, 0, $fontFile, $labelText);
            $labelWidth = $labelBox[2] - $labelBox[0];

            imagettftext($image, $keyFontSize, 0, $x, $y + 35, $black, $fontFile, $labelText);
            $boxX = $x + $labelWidth;

            if ($isSolution) {
                // **NEW**: Adds a border and centers the text for the solution key.
                imagesetthickness($image, 2);
                imagerectangle($image, $boxX, $y, $boxX + $keyBoxWidth, $y + 45, $gray);
                
                $textColor = $isHint ? $blue : $black;
                $content = (string)$cipherMap[$letter];

                $contentBox = imagettfbbox($keyFontSize, 0, $fontFile, $content);
                $contentWidth = $contentBox[2] - $contentBox[0];
                $contentX = $boxX + ($keyBoxWidth - $contentWidth) / 2;

                imagettftext($image, $keyFontSize, 0, $contentX, $y + 35, $textColor, $fontFile, $content);

            } else {
                // This is the existing logic for the puzzle page.
                imagesetthickness($image, 2);
                imagerectangle($image, $boxX, $y, $boxX + $keyBoxWidth, $y + 45, $gray);

                if ($isHint) {
                    $content = (string)$hint['cipher'];
                    $contentBox = imagettfbbox($keyFontSize, 0, $fontFile, $content);
                    $contentWidth = $contentBox[2] - $contentBox[0];
                    $contentX = $boxX + ($keyBoxWidth - $contentWidth) / 2;
                    imagettftext($image, $keyFontSize, 0, $contentX, $y + 35, $blue, $fontFile, $content);
                }
            }
            
            $x += $labelWidth + $keyBoxWidth + $keyPadding;
        }
        $y += $lineSpacing;
    }
    
    return count($keyRows) * $lineSpacing + $margin;
}

    function drawCryptogramImage($filePath, $isSolution, $config) {
        extract($config);
        $image = imagecreatetruecolor($imageWidth, $imageHeight);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $gray = imagecolorallocate($image, 180, 180, 180);
        $blue = imagecolorallocate($image, 20, 20, 200);

        imagefill($image, 0, 0, $white);
        $keyHeight = drawKey($image, $isSolution, $config);

        $puzzleBlockHeight = count($layoutLines) * $lineHeight;
        $y = $keyHeight + (($imageHeight - $keyHeight - $puzzleBlockHeight) / 2);
        
        foreach ($layoutLines as $line) {
            $x = ($imageWidth - $line['width']) / 2;
            foreach ($line['items'] as $word) {
                foreach ($word['letters'] as $item) {
                    $isHint = ($hint && $item['original'] === $hint['original']);
                    
                    if ($item['is_letter']) {
                        $topBoxY = $y;
                        $bottomBoxY = $y + $boxHeight + $itemSpacing;
                        imagesetthickness($image, 3);
                        imagerectangle($image, $x, $topBoxY, $x + $itemWidth, $topBoxY + $boxHeight, $gray);
                        
                        // **FIX**: Cast cipher number to a string
                        $cipherText = (string) $item['cipher'];
                        $cipherBox = imagettfbbox($numberFontSize, 0, $fontFile, $cipherText);
                        $cipherX = $x + ($itemWidth - ($cipherBox[2] - $cipherBox[0])) / 2;
                        $cipherY = $topBoxY + ($boxHeight - ($cipherBox[7] - $cipherBox[1])) / 2;
                        imagettftext($image, $numberFontSize, 0, $cipherX, $cipherY, $black, $fontFile, $cipherText);

                        imagerectangle($image, $x, $bottomBoxY, $x + $itemWidth, $bottomBoxY + $boxHeight, $gray);
                        
                        if ($isSolution || $isHint) {
                            $solutionBox = imagettfbbox($solutionLetterFontSize, 0, $fontFile, $item['original']);
                            $solutionX = $x + ($itemWidth - ($solutionBox[2] - $solutionBox[0])) / 2;
                            $solutionY = $bottomBoxY + ($boxHeight - ($solutionBox[7] - $solutionBox[1])) / 2;
                            imagettftext($image, $solutionLetterFontSize, 0, $solutionX, $solutionY, ($isSolution && $isHint ? $blue : $black), $fontFile, $item['original']);
                        }
                    } else {
                        $puncBox = imagettfbbox($numberFontSize, 0, $fontFile, $item['original']);
                        $puncX = $x + ($itemWidth - ($puncBox[2] - $puncBox[0])) / 2;
                        $puncY = $y + ($itemHeight - ($puncBox[7] - $puncBox[1])) / 2;
                        imagettftext($image, $numberFontSize, 0, $puncX, $puncY, $gray, $fontFile, $item['original']);
                    }
                    $x += $itemWidth;
                }
                $x += $wordSpacing;
            }
            $y += $lineHeight;
        }

        imagepng($image, $filePath);
        imagedestroy($image);
    }
    
    $config = compact(
        'imageWidth', 'imageHeight', 'fontFile', 'layoutLines', 'margin', 'cipherMap', 'hint',
        'numberFontSize', 'solutionLetterFontSize', 'itemWidth', 'boxHeight', 'itemSpacing', 'itemHeight', 'lineHeight', 'wordSpacing'
    );
    
    drawCryptogramImage($fullPuzzlePath, false, $config);
    drawCryptogramImage($fullSolutionPath, true, $config);

    if ($handle = opendir($tempDir)) {
        while (false !== ($file = readdir($handle))) {
            if (strpos($file, 'puzzle_') === 0 || strpos($file, 'solution_') === 0) {
                if (filemtime($tempDir . $file) < time() - 600) { @unlink($tempDir . $file); }
            }
        }
        closedir($handle);
    }
}
?>

<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
        <title>Cryptogram Maker | Zachary Kai</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="/assets/icon.ico" type="image/x-icon">
        <link rel="stylesheet" href="/assets/style.css">
        <link rel="stylesheet" href="/assets/styles/print.css" media="print">
        <link href="/assets/manifest.json" rel="manifest"/>
        <link rel="alternate" type="application/rss+xml" title="Zachary Kai" href="/assets/rss.xml">
        <link rel="webmention" href="https://webmention.io/zacharykai.net/webmention" />
        <link rel="canonical" href="https://zacharykai.net/tools/cryptograms">
        <meta name="date" content="2024-07-09">
        <meta name="last-modified" content="2024-07-09">
        <meta name="description" content="Create your own cryptogram puzzles with this simple online tool.">
        <style>
        </style>
    </head>
    <body>
        <p><a href="#top" class="essentials">Begin reading...</a></p>
        <header><nav><a href="/">Zachary Kai</a></nav></header>
        <main class="h-entry">
            <header>
                <p class="breadcrumbs"><a href="/">Homepage</a> • <a href="/tools">Tools</a> •</p>
                <h1 class="p-name">Cryptogram Generator</h1>
                <p class="postmeta">
                    <strong>Published</strong>: <time class="dt-published" datetime="2024-07-09">09 Jul 2024</time> |
                    <strong>Updated</strong>: <time class="dt-modified" datetime="2024-07-09">09 Jul 2024</time>
                </p>
            </header>
            <p id="top" class="p-summary">A simple tool to generate your own cryptogram puzzles.</p>
            
            <form action="/tools/cryptograms" method="post">
                <label for="sentence">Enter your sentence:</label><br>
                <textarea id="sentence" name="sentence" rows="2" cols="50" required>
                    <?php echo isset($_POST['sentence']) ? htmlspecialchars($_POST['sentence']) : ''; ?>
                </textarea>
                <input type="submit" value="Generate Cryptogram">
            </form>
            
            <?php if ($puzzleImagePath && $solutionImagePath): ?>
                <section class="cryptogram-output">
                    <h2>Your Cryptogram</h2>
                    <p>Right-click or long-press the images to save and share them!</p>
                    
                    <h3>Puzzle</h3>
                    <img src="<?php echo htmlspecialchars($puzzleImagePath); ?>" alt="Cryptogram Puzzle">
                    
                    <details>
                        <summary><strong>Click to view the solution...</strong></summary>
                        <img src="<?php echo htmlspecialchars($solutionImagePath); ?>" alt="Cryptogram Solution">
                    </details>
                </section>
            <?php endif; ?>

            <p>•--♡--•</p>
            <section class="essentials">
                <p><strong>Copy & Share</strong>: <a href="/tools/cryptograms" class="u-url">zacharykai.net/tools/cryptograms</a></p>
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