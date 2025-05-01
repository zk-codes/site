<?php
// Initialize variables
$results = '';
$wordCount = 0;
$readingTime = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Calculate score based on answers
    $score = 0;
    
    if (isset($_POST['setting'])) {
        $score += intval($_POST['setting']);
    }
    
    if (isset($_POST['themes'])) {
        $score += intval($_POST['themes']);
    }
    
    if (isset($_POST['characters'])) {
        $score += intval($_POST['characters']);
    }
    
    if (isset($_POST['technology'])) {
        $score += intval($_POST['technology']);
    }
    
    if (isset($_POST['tone'])) {
        $score += intval($_POST['tone']);
    }
    
    // Determine SF subgenre based on score
    if ($score <= 5) {
        $subgenre = "Solarpunk";
        $description = "You're Solarpunk—optimistic, sustainable, and community-focused. Like this emerging subgenre, you envision a future where humanity has successfully addressed ecological challenges through innovation and cooperation. You're the hopeful futurist who sees technology as a tool for ecological harmony rather than domination. Your greatest strength is imagining practical utopias that don't ignore real-world problems. When faced with challenges, you seek collaborative solutions that benefit all life forms. Your mantra might be: 'A better world is not only possible, it's inevitable with the right choices.'";
    } elseif ($score <= 9) {
        $subgenre = "Space Opera";
        $description = "You're Space Opera—vast, dramatic, and sweeping in scope. Like this beloved subgenre, you embrace grand narratives and epic adventures across galactic civilizations. You're the cosmic storyteller who weaves tales of heroism against the backdrop of the stars. Your imagination thrives on the grandest possible scale, exploring the human condition through the lens of interstellar conflict and alliance. When considering the future, you think in terms of empires, dynasties, and the clash of civilizations across light years. Your mantra might be: 'The universe is the ultimate stage for human drama.'";
    } elseif ($score <= 13) {
        $subgenre = "Cyberpunk";
        $description = "You're Cyberpunk—gritty, subversive, and technologically immersive. Like this influential subgenre, you're fascinated by the intersection of high technology and low life. You're the digital rebel navigating the shadows between corporate power and street-level resistance. Your perspective on the future is both cautionary and thrilling, recognizing technological progress comes with social costs. When analyzing systems, you instinctively look for the control mechanisms and how they might be hacked. Your mantra might be: 'The street finds its own uses for things.'";
    } elseif ($score <= 17) {
        $subgenre = "Hard Science Fiction";
        $description = "You're Hard Science Fiction—precise, rigorous, and intellectually stimulating. Like this respected subgenre, you value scientific accuracy and plausible extrapolation above all. You're the meticulous futurist who builds tomorrow's possibilities on today's scientific foundations. Your attention to detail means you're often the first to spot logical inconsistencies or implausible assumptions. When imagining the future, you carefully consider the technological implications and their cascading effects on society. Your mantra might be: 'The universe is stranger than we imagine, but obeys consistent rules.'";
    } elseif ($score <= 21) {
        $subgenre = "Post-Apocalyptic Fiction";
        $description = "You're Post-Apocalyptic Fiction—resilient, resourceful, and unflinchingly honest. Like this enduring subgenre, you're fascinated by how humanity adapts to catastrophic change. You're the survivor who finds meaning in rebuilding after collapse. Your clear-eyed assessment of human nature acknowledges both our capacity for savagery and our drive to recreate community. When thinking about civilization, you understand its fragility while appreciating the endurance of human connection. Your mantra might be: 'What remains after everything is lost reveals who we truly are.'";
    } else {
        $subgenre = "Biopunk";
        $description = "You're Biopunk—transformative, boundary-pushing, and organically complex. Like this cutting-edge subgenre, you're fascinated by the manipulation of life itself and the blurring of boundaries between natural and artificial. You're the visionary who explores how biotechnology might reshape what it means to be human. Your perspective embraces both the wonder and horror of biological innovation, from genetic engineering to synthetic organisms. When considering the future, you focus on how our understanding of life will transform bodies, ecosystems, and consciousness itself. Your mantra might be: 'Evolution is no longer just natural selection—it's also intelligent design.'";
    }
    
    // Create results content
    $results = "<h2>You are $subgenre!</h2>
                <p>$description</p>
                <p>•--♡--•</p>
                <p>Your quiz score: $score</p>";
                
    // Calculate word count and reading time
    $wordCount = str_word_count($description);
    $readingTime = ceil($wordCount / 200); // Assuming average reading speed of 200 words per minute
}

// Get current date for metadata
$currentDate = date("d M Y");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>What Science Fiction Subgenre Are You? | Zachary Kai</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" href="/assets/icon.ico" type="image/x-icon">
<link rel="stylesheet" href="/assets/style.css">
<link rel="alternate" type="application/rss+xml" title="Zachary Kai" href="/assets/rss.xml">
<link rel="webmention" href="https://webmention.io/zacharykai.net/webmention">
<link rel="canonical" href="https://zacharykai.net/">
<meta name="date" content="<?php echo date('Y-m-d'); ?>">
<meta name="last-modified" content="<?php echo date('Y-m-d'); ?>">
<meta name="description" content="Discover which science fiction subgenre matches your personality. A playful exploration of speculative preferences and storytelling affinities.">
</head>
<body>
<p><a href="#top" class="essentials">Begin reading...</a></p>
<header><nav><a href="/" title="My homepage.">Zachary Kai</a></nav></header>
<main>
<h1>What Science Fiction Subgenre Are You?</h1>
<p class="postmeta"><strong>Published</strong>: <?php echo $currentDate; ?> | <strong>Updated</strong>: <?php echo $currentDate; ?></p>
<p id="top">How we imagine the future, relate to technology, and envision humanity's potential varies widely. This quiz will reveal which science fiction subgenre resonates most with your cosmic sensibilities.</p>
<p>Answer honestly. The starship's AI doesn't judge, and neither do I.</p>
<p>As with all personality quizzes, this is more whimsy than hard science!</p>

<?php if ($results): ?>
<!-- Display quiz results -->
<?php echo $results; ?>
<p><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">Take the quiz again</a></p>
<?php else: ?>
<!-- Display quiz form -->    
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <fieldset>
            <legend>What setting appeals to you most?</legend>
            <fieldset class="options">
        <input type="radio" id="setting_1" name="setting" value="1" required>
        <label for="setting_1">A lush, sustainable Earth where technology and nature exist in harmony.</label>
        <br>
        <input type="radio" id="setting_2" name="setting" value="2">
        <label for="setting_2">Vast interstellar civilizations spanning multiple galaxies.</label>
        <br>
        <input type="radio" id="setting_3" name="setting" value="3">
        <label for="setting_3">A near-future metropolis dominated by corporations and digital networks.</label>
        <br>
        <input type="radio" id="setting_4" name="setting" value="4">
        <label for="setting_4">The realistic exploration of space using plausible technology.</label>
        <br>
        <input type="radio" id="setting_5" name="setting" value="5">
        <label for="setting_5">A devastated world where survivors struggle to rebuild civilization.</label>
        <br>
        <input type="radio" id="setting_6" name="setting" value="6">
        <label for="setting_6">A world transformed by genetic engineering and biotechnology.</label>
    </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>Which themes resonate with you most?</legend>
            <fieldset class="options">
        <input type="radio" id="themes_1" name="themes" value="1" required>
        <label for="themes_1">Ecological harmony, sustainable communities, and positive futures.</label>
        <br>
        <input type="radio" id="themes_2" name="themes" value="2">
        <label for="themes_2">Epic conflicts, galactic politics, and the destiny of civilizations.</label>
        <br>
        <input type="radio" id="themes_3" name="themes" value="3">
        <label for="themes_3">Digital consciousness, corporate control, and resistance in the margins.</label>
        <br>
        <input type="radio" id="themes_4" name="themes" value="4">
        <label for="themes_4">Scientific discovery, technological problem-solving, and exploration.</label>
        <br>
        <input type="radio" id="themes_5" name="themes" value="5">
        <label for="themes_5">Survival, the collapse of social structures, and human nature stripped bare.</label>
        <br>
        <input type="radio" id="themes_6" name="themes" value="6">
        <label for="themes_6">Biological transformation, the ethics of creating life, and new forms of consciousness.</label>
    </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>What types of characters do you find most compelling?</legend>
            <fieldset class="options">
        <input type="radio" id="characters_1" name="characters" value="1" required>
        <label for="characters_1">Community organizers, visionary engineers, and ecological activists.</label>
        <br>
        <input type="radio" id="characters_2" name="characters" value="2">
        <label for="characters_2">Space captains, diplomats navigating alien cultures, and galactic heroes.</label>
        <br>
        <input type="radio" id="characters_3" name="characters" value="3">
        <label for="characters_3">Hackers, rogue AIs, and individuals fighting against corporate control.</label>
        <br>
        <input type="radio" id="characters_4" name="characters" value="4">
        <label for="characters_4">Scientists, engineers, and rational problem-solvers facing cosmic challenges.</label>
        <br>
        <input type="radio" id="characters_5" name="characters" value="5">
        <label for="characters_5">Survivors, community leaders rebuilding society, and those navigating moral ambiguity.</label>
        <br>
        <input type="radio" id="characters_6" name="characters" value="6">
        <label for="characters_6">Genetic engineers, human-animal hybrids, and those questioning biological identity.</label>
    </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>What technology fascinates you most?</legend>
            <fieldset class="options">
        <input type="radio" id="technology_1" name="technology" value="1" required>
        <label for="technology_1">Renewable energy, sustainable architecture, and ecological restoration systems.</label>
        <br>
        <input type="radio" id="technology_2" name="technology" value="2">
        <label for="technology_2">Faster-than-light travel, planet-scale engineering, and advanced weapons systems.</label>
        <br>
        <input type="radio" id="technology_3" name="technology" value="3">
        <label for="technology_3">Neural interfaces, virtual reality, and artificial intelligence.</label>
        <br>
        <input type="radio" id="technology_4" name="technology" value="4">
        <label for="technology_4">Realistic spaceflight, robotics based on current science, and plausible future tech.</label>
        <br>
        <input type="radio" id="technology_5" name="technology" value="5">
        <label for="technology_5">Repurposed remnants of advanced technology, survival gear, and sustainable agriculture.</label>
        <br>
        <input type="radio" id="technology_6" name="technology" value="6">
        <label for="technology_6">Genetic modification, synthetic organisms, and biological computing.</label>
    </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>What narrative tone appeals to you most?</legend>
            <fieldset class="options">
        <input type="radio" id="tone_1" name="tone" value="1" required>
        <label for="tone_1">Hopeful and solution-oriented with an emphasis on cooperation.</label>
        <br>
        <input type="radio" id="tone_2" name="tone" value="2">
        <label for="tone_2">Grand, adventurous, and focused on the sweep of history and fate.</label>
        <br>
        <input type="radio" id="tone_3" name="tone" value="3">
        <label for="tone_3">Noir-influenced, rebellious, questioning the status quo.</label>
        <br>
        <input type="radio" id="tone_4" name="tone" value="4">
        <label for="tone_4">Intellectually stimulating, focused on logical problem-solving and scientific accuracy.</label>
        <br>
        <input type="radio" id="tone_5" name="tone" value="5">
        <label for="tone_5">Gritty, introspective, exploring human resilience in extreme circumstances.</label>
        <br>
        <input type="radio" id="tone_6" name="tone" value="6">
        <label for="tone_6">Philosophical, body-conscious, questioning the boundaries of humanity.</label>
    </fieldset>
        </fieldset>
        
<input type="submit" value="Discover Your Science Fiction Subgenre">
</form>
<?php endif; ?>

<p>•--♡--•</p>
<p class="essentials"><strong>Copy & Share</strong>: <a href="/forms/sf-subgenre">zacharykai.net/forms/sf-subgenre</a></p>
<p class="essentials">
    <strong>Reply Via</strong>:
    <a href="mailto:hi@zacharykai.net">Email</a> | 
    <a href="/guestbook">Guestbook</a> |
    <a href="/unoffice-hours">UnOffice Hours</a> | 
    <a href="https://webmention.io/zacharykai.net/webmention" target="_blank" rel="noopener">Webmention</a>
</p>
<p class="essentials"><strong>Statistics</strong> → Word Count: | Reading Time:</p>
</main>
<section class="h-card vcard">
<section class="h-card-image"><img class="u-photo" loading="lazy" src="/assets/zk_icon.png" alt="Zachary Kai's avatar."></section>
<section class="h-card-content">
<p><strong><a class="u-url p-name" href="https://zacharykai.net" rel="me"><span class="fn">Zachary Kai</span></a></strong> — <span class="p-pronouns">he/him</span> | <a class="u-email email" href="mailto:hi@zacharykai.net" rel="me">hi@zacharykai.net</a></p>
<p class="p-note">Zachary Kai is a space fantasy writer, offbeat queer, traveler, zinester, and avowed generalist. The internet is his livelihood and lifeline.</p>
</section>
</section>
<p><a href="#top" class="essentials">Read again...</a></p>
<footer><p>Est. 2024 || <a href="/about">About</a> | <a href="/changelog">Changelog</a> | <a href="/cv">CV</a> | <a href="/colophon">Colophon</a> | <a href="/contact">Contact</a> | <a href="/newsletter/">Newsletter</a> | <a href="/resume">Resume</a> | <a href="/assets/rss.xml">RSS</a> | <a href="/sitemap">Sitemap</a></p>
<p class="elsewhere">Elsewhere || <a href="https://github.com/zk-codes" target="_blank">Github</a> | <a href="https://indieweb.org/User:Zacharykai.net" target="_blank">Indieweb</a> | <a href="https://archive.org/details/@zacharykai" target="_blank">Internet Archive</a> | <a href="https://ko-fi.com/zacharykai" target="_blank">Ko-Fi</a> | <a href="https://www.linkedin.com/in/zachary-kai/" target="_blank">Linkedin</a></p></footer>
</body>
</html>