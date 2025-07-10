<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
        <title>Repeats Finder | Zachary Kai</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="/assets/icon.ico" type="image/x-icon">
        <link rel="stylesheet" href="/assets/style.css">
        <link rel="stylesheet" href="/assets/styles/print.css" media="print">
        <link href="/assets/manifest.json" rel="manifest"/>
        <link rel="alternate" type="application/rss+xml" title="Zachary Kai" href="/assets/rss.xml">
        <link rel="webmention" href="https://webmention.io/zacharykai.net/webmention" />
        <link rel="canonical" href="https://zacharykai.net/tools/repeatfinder">
        <meta name="date" content="20XX-XX-XX">
        <meta name="last-modified" content="20XX-XX-XX">
        <meta name="description" content="Paste your text to analyze repeated phrases, word echoes, and generate a report of your top ten most frequently used words.">
        <style>.phrase-highlight {background-color: #FFD700; border-radius: 3px;} .proximity-highlight {background-color: #ADD8E6; border-radius: 3px;}</style>
    </head>
    <body>
        <p><a href="#top" class="essentials">Begin reading...</a></p>
        <header><nav><a href="/">Zachary Kai</a></nav></header>
        <main class="h-entry">
            <header>
                <p class="breadcrumbs"><a href="/">Homepage</a> • <a href="/sitemap#tools">Tools</a></p>
                <h1 class="p-name">Repeats Finder</h1>
                <p class="postmeta">
                    <strong>Published</strong>: <time class="dt-published" datetime="20XX-XX-XX">XX XXX 20XX</time> | 
                    <strong>Updated</strong>: <time class="dt-modified" datetime="20XX-XX-XX">XX XXX 20XX</time>
                </p>
            </header>
            <p id="top" class="p-summary">Paste your text to analyze repeated phrases, word echoes, and generate a report of your top ten most frequently used words.</p>
            <form action="" method="post">
                <label for="text_to_analyze">Enter your written piece below:*</label>
                <textarea name="text_to_analyze">
                <?php echo isset($_POST['text_to_analyze']) ? htmlspecialchars($_POST['text_to_analyze']) : ''; ?>
                </textarea>
                <input type="submit" name="submit" value="Analyze Text">
            </form>

            <?php
            
            if (isset($_POST['submit']) && !empty($_POST['text_to_analyze'])) {
            
            // --- CONFIGURATION & STOP WORDS ---

            $proximity_window = 15;
            $min_phrase_len = 3;
            $max_phrase_len = 5;

            $stop_words = [
            
                // Articles, Prepositions, Conjunctions, Pronouns...

                'a','an','the','and','but','or','for','nor','so','yet','in','on','at','to','from','by','with','of','about','as','is','am','are','was','were','be','been','being','have','has','had','do','does','did','i','you','he','she','it','we','they','me','him','her','us','them','my','your','his','its','our','their','that','which','who','what','when','where','why','how','not','so','up','out',
                
                // Dialogue Tags

                'said','asked','replied','shouted','answered','exclaimed', 'muttered','mumbled',

            ];
    
            $stop_words_map = array_flip($stop_words); // Use a map for faster lookups

            // --- INITIALIZATION ---

            $text = $_POST['text_to_analyze'];
            $original_words = preg_split('/(\s+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
            
            $clean_words = [];
            $original_to_clean_map = []; // Map from original_words index to clean_words index
            $clean_to_original_map = []; // Map from clean_words index to original_words index
            $clean_word_index = 0;

            foreach ($original_words as $i => $word_part) {
                if (!preg_match('/\s+/', $word_part)) { // If it's not just whitespace
                    $cleaned_word = strtolower(preg_replace('/[^\w]/', '', $word_part));
                    if (!empty($cleaned_word)) {
                        $clean_words[] = $cleaned_word;
                        $original_to_clean_map[$i] = $clean_word_index;
                        $clean_to_original_map[$clean_word_index] = $i;
                        $clean_word_index++;
                    }
                }
            }

            $word_count = count($clean_words);
            $highlights = array_fill(0, count($original_words), 'none');

            // --- 1. PHRASE HIGHLIGHTING (FILTERED) ---
            
            $phrases = [];
            for ($len = $max_phrase_len; $len >= $min_phrase_len; $len--) {
                for ($i = 0; $i <= $word_count - $len; $i++) {
                    $phrase_words = array_slice($clean_words, $i, $len);
                    $phrase = implode(' ', $phrase_words);
                    
                    $is_significant_phrase = false;
                    foreach($phrase_words as $p_word) {
                        if (!isset($stop_words_map[$p_word])) {
                            $is_significant_phrase = true;
                            break;
                        }
                    }

                    if ($is_significant_phrase) {
                        if (!isset($phrases[$phrase])) {
                            $phrases[$phrase] = [];
                        }
                        $phrases[$phrase][] = $i;
                    }
                }
            }
    
            foreach ($phrases as $phrase => $positions) {
                if (count($positions) > 1) {
                    $phrase_len = count(explode(' ', $phrase));
                    foreach ($positions as $start_pos_clean) {
                        for ($i = 0; $i < $phrase_len; $i++) {
                            $current_clean_word_index = $start_pos_clean + $i;
                            if (isset($clean_to_original_map[$current_clean_word_index])) {
                                $word_idx_in_original = $clean_to_original_map[$current_clean_word_index];
                                $highlights[$word_idx_in_original] = 'phrase';
                            }
                        }
                    }
                }
            }

            // --- 2. PROXIMITY HIGHLIGHTING (FILTERED) ---
            
            $word_positions = [];
            foreach ($clean_words as $i => $word) {
                if (!isset($stop_words_map[$word])) {
                    $word_positions[$word][] = $i;
                }
            }

            foreach ($word_positions as $word => $positions) {
                if (count($positions) > 1) {
                    for ($i = 0; $i < count($positions) - 1; $i++) {
                        if (($positions[$i+1] - $positions[$i]) <= $proximity_window) {
                            $word_idx1_clean = $positions[$i];
                            $word_idx2_clean = $positions[$i+1];

                            if (isset($clean_to_original_map[$word_idx1_clean])) {
                                $word_idx1_original = $clean_to_original_map[$word_idx1_clean];
                                if ($highlights[$word_idx1_original] === 'none') {
                                    $highlights[$word_idx1_original] = 'proximity';
                                }
                            }
                            if (isset($clean_to_original_map[$word_idx2_clean])) {
                                $word_idx2_original = $clean_to_original_map[$word_idx2_clean];
                                if ($highlights[$word_idx2_original] === 'none') {
                                    $highlights[$word_idx2_original] = 'proximity';
                                }
                            }
                        }
                    }
                }
            }

            // --- 3. TOP 10 WORD REPORT ---

            $word_frequencies = array_count_values($clean_words);
            $filtered_frequencies = array_diff_key($word_frequencies, $stop_words_map);
            arsort($filtered_frequencies);
            $top_10_words = array_slice($filtered_frequencies, 0, 10, true);

            // --- 4. PRODUCE OUTPUT ---

            echo '<section class="container">';

            // Render Highlighted Text

            echo '<section class="results-box">';
            echo '<h3>Analysis Results</h3>';
            $output_html = '';
            $current_highlight = 'none';

            foreach ($original_words as $i => $word) {
                $highlight_type = $highlights[$i] ?? 'none';
        
                if ($highlight_type !== $current_highlight) {
                    if ($current_highlight !== 'none') $output_html .= '</span>';
                    if ($highlight_type !== 'none') $output_html .= '<span class="' . $highlight_type . '-highlight">';
                    $current_highlight = $highlight_type;
                }

            $output_html .= htmlspecialchars($word);
            
            }
            
            if ($current_highlight !== 'none') $output_html .= '</span>';
    
            echo '<p>' . nl2br($output_html) . '</p>';
            echo '</section>';

            // Render Report

            echo '<section class="results-box">';
            echo '<h3>Top 10 Significant Words</h3>';
            echo '<p>Excluding common prepositions, articles, pronouns, and dialogue tags.</p>';
            if (empty($top_10_words)) {
            echo '<p>Not enough text to generate a report.</p>';
            } else {
                echo '<ol>';
                foreach ($top_10_words as $word => $count) {
                    echo '<li>' . htmlspecialchars($word) . '&nbsp; &rarr; &nbsp;' . $count . '</li>';
                }
            echo '</ol>';

            }

            echo '</section>';
            echo '</section>';

            }
            ?>

            <p>•--♡--•</p>
            <section class="essentials">
                <p><strong>Copy & Share</strong>: <a href="/tools/repeatsfinder" class="u-url">zacharykai.net/tools/repeatsfinder</a></p>
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