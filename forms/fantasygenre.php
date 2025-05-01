<?php
// Initialize variables
$results = '';
$wordCount = 0;
$readingTime = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Calculate score based on answers
    $score = 0;
    
    if (isset($_POST['world_building'])) {
        $score += intval($_POST['world_building']);
    }
    
    if (isset($_POST['character_types'])) {
        $score += intval($_POST['character_types']);
    }
    
    if (isset($_POST['magic_system'])) {
        $score += intval($_POST['magic_system']);
    }
    
    if (isset($_POST['tone'])) {
        $score += intval($_POST['tone']);
    }
    
    if (isset($_POST['themes'])) {
        $score += intval($_POST['themes']);
    }
    
    // Determine fantasy subgenre based on score
    if ($score <= 5) {
        $subgenre = "High Fantasy";
        $description = "You're High Fantasy—epic, sweeping, and filled with wonder. Like this subgenre, you're drawn to grand quests and battles between good and evil. You see the world in vivid colors and believe in the power of noble hearts to overcome darkness. Your imagination stretches across vast landscapes and ancient histories. When faced with adversity, you trust that courage and friendship will light the way forward. Your mantra might be: 'Even the smallest person can change the course of the future.'";
    } elseif ($score <= 9) {
        $subgenre = "Fairy Tale Fantasy";
        $description = "You're Fairy Tale Fantasy—enchanting, symbolic, and timeless. Like this subgenre, you find meaning in the magical moments of everyday life. You're the storyteller who weaves profound truths into seemingly simple tales. Your sensitivity to patterns and archetypes reveals the deeper currents running beneath the surface of things. When confronted with challenges, you look to ancient wisdom and transformative magic. Your mantra might be: 'The beginning of wisdom is calling things by their true names.'";
    } elseif ($score <= 13) {
        $subgenre = "Urban Fantasy";
        $description = "You're Urban Fantasy—contemporary, gritty, and hiding magic in plain sight. Like this subgenre, you see the extraordinary lurking just beneath the surface of the ordinary world. You're the boundary-crosser who moves between different realities with ease. Your practical approach to wonder means you can find enchantment in city streets and forgotten corners. When magic intrudes into the everyday, you adapt quickly and face it head-on. Your mantra might be: 'The real world is stranger than we know.'";
    } elseif ($score <= 17) {
        $subgenre = "Historical Fantasy";
        $description = "You're Historical Fantasy—richly detailed, culturally immersive, and anchored in the past. Like this subgenre, you value the authentic textures of history woven with threads of the magical. You're the time-traveler who reveals how the world might have been if myths were true. Your respect for both historical accuracy and imaginative possibility creates uniquely compelling visions. When exploring new ideas, you ground them in the familiar soil of the past. Your mantra might be: 'To understand our present, we must reimagine our history.'";
    } elseif ($score <= 21) {
        $subgenre = "Magical Realism";
        $description = "You're Magical Realism—subtle, profound, and blurring the boundaries of the real. Like this subgenre, you perceive the miraculous within the mundane without fanfare or explanation. You're the quiet observer who notices how reality sometimes ripples with inexplicable wonder. Your greatest strength is accepting the extraordinary as part of everyday life. When magical elements appear in your world, you embrace them with the same calm acceptance as sunrise or rain. Your mantra might be: 'The extraordinary lies within the ordinary.'";
    } else {
        $subgenre = "Mythic Fantasy";
        $description = "You're Mythic Fantasy—primal, resonant, and connected to ancient powers. Like this subgenre, you're attuned to the archetypal forces that have shaped human consciousness across millennia. You're the keeper of old stories that still pulse with relevance. Your understanding of symbolic language lets you speak directly to the collective unconscious. When navigating life's challenges, you draw on the wisdom embedded in myths from across cultures. Your mantra might be: 'The gods are still speaking, if we know how to listen.'";
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
<title>What Fantasy Subgenre Are You? | Zachary Kai</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" href="/assets/icon.ico" type="image/x-icon">
<link rel="stylesheet" href="/assets/style.css">
<link rel="alternate" type="application/rss+xml" title="Zachary Kai" href="/assets/rss.xml">
<link rel="webmention" href="https://webmention.io/zacharykai.net/webmention">
<link rel="canonical" href="https://zacharykai.net/">
<meta name="date" content="<?php echo date('Y-m-d'); ?>">
<meta name="last-modified" content="<?php echo date('Y-m-d'); ?>">
<meta name="description" content="Discover which fantasy subgenre matches your personality. A playful exploration of storytelling preferences and personal traits.">
</head>
<body>
<p><a href="#top" class="essentials">Begin reading...</a></p>
<header><nav><a href="/" title="My homepage.">Zachary Kai</a></nav></header>
<main>
<h1>What Fantasy Subgenre Are You?</h1>
<p class="postmeta"><strong>Published</strong>: <?php echo $currentDate; ?> | <strong>Updated</strong>: <?php echo $currentDate; ?></p>
<p id="top">How we imagine magic, adventure, and wonder varies uniquely for each of us. This quiz will reveal which fantasy subgenre resonates most with your essence.</p>
<p>Answer honestly. The ancient scrolls don't judge, and neither do I.</p>
<p>As with all personality quizzes, this is more enchantment than divination!</p>

<?php if ($results): ?>
<!-- Display quiz results -->
<?php echo $results; ?>
<p><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">Take the quiz again</a></p>
<?php else: ?>
<!-- Display quiz form -->    
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <fieldset>
            <legend>What draws you most to a fantasy world?</legend>
            <fieldset class="options">
        <input type="radio" id="world_1" name="world_building" value="1" required>
        <label for="world_1">Sweeping landscapes, ancient kingdoms, and maps that invite exploration.</label>
        <br>
        <input type="radio" id="world_2" name="world_building" value="2">
        <label for="world_2">Enchanted forests, hidden cottages, and places where transformation happens.</label>
        <br>
        <input type="radio" id="world_3" name="world_building" value="3">
        <label for="world_3">The familiar modern world with secret magical elements hiding in plain sight.</label>
        <br>
        <input type="radio" id="world_4" name="world_building" value="4">
        <label for="world_4">Meticulously researched historical settings with magical twists.</label>
        <br>
        <input type="radio" id="world_5" name="world_building" value="5">
        <label for="world_5">Everyday reality where unexplained events occur without fanfare.</label>
        <br>
        <input type="radio" id="world_6" name="world_building" value="6">
        <label for="world_6">Primal landscapes shaped by gods and cosmic forces beyond human understanding.</label>
    </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>Which characters appeal to you most?</legend>
            <fieldset class="options">
        <input type="radio" id="character_1" name="character_types" value="1" required>
        <label for="character_1">Heroes with destiny, wise mentors, and clearly defined villains.</label>
        <br>
        <input type="radio" id="character_2" name="character_types" value="2">
        <label for="character_2">Tricksters, transformed beings, and those who must solve magical riddles.</label>
        <br>
        <input type="radio" id="character_3" name="character_types" value="3">
        <label for="character_3">Modern people with magical abilities, supernatural creatures in disguise.</label>
        <br>
        <input type="radio" id="character_4" name="character_types" value="4">
        <label for="character_4">Historical figures interacting with fictional magical characters.</label>
        <br>
        <input type="radio" id="character_5" name="character_types" value="5">
        <label for="character_5">Ordinary people experiencing extraordinary circumstances they accept without question.</label>
        <br>
        <input type="radio" id="character_6" name="character_types" value="6">
        <label for="character_6">Mortals walking among gods, characters embodying primeval forces.</label>
    </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>How do you prefer magic to work in stories?</legend>
            <fieldset class="options">
        <input type="radio" id="magic_1" name="magic_system" value="1" required>
        <label for="magic_1">Clear systems with established rules, often wielded by special individuals.</label>
        <br>
        <input type="radio" id="magic_2" name="magic_system" value="2">
        <label for="magic_2">Whimsical, transformative, and often following poetic rules of three or seven.</label>
        <br>
        <input type="radio" id="magic_3" name="magic_system" value="3">
        <label for="magic_3">Hidden beneath the surface of modern life, with secret organizations and magical communities.</label>
        <br>
        <input type="radio" id="magic_4" name="magic_system" value="4">
        <label for="magic_4">Connected to historical beliefs and practices, feeling authentic to the period.</label>
        <br>
        <input type="radio" id="magic_5" name="magic_system" value="5">
        <label for="magic_5">Subtle and unexplained, woven into reality without drawing attention to itself.</label>
        <br>
        <input type="radio" id="magic_6" name="magic_system" value="6">
        <label for="magic_6">Primal forces tied to creation myths, often manifesting through rituals or divine intervention.</label>
    </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>What tone do you gravitate toward in stories?</legend>
            <fieldset class="options">
        <input type="radio" id="tone_1" name="tone" value="1" required>
        <label for="tone_1">Epic and inspiring, with clear moral lessons.</label>
        <br>
        <input type="radio" id="tone_2" name="tone" value="2">
        <label for="tone_2">Enchanting and symbolic, with hidden meanings.</label>
        <br>
        <input type="radio" id="tone_3" name="tone" value="3">
        <label for="tone_3">Fast-paced and contemporary, with touches of humor amid danger.</label>
        <br>
        <input type="radio" id="tone_4" name="tone" value="4">
        <label for="tone_4">Immersive and detailed, blending historical fact with fantasy.</label>
        <br>
        <input type="radio" id="tone_5" name="tone" value="5">
        <label for="tone_5">Matter-of-fact about the extraordinary, often with lyrical prose.</label>
        <br>
        <input type="radio" id="tone_6" name="tone" value="6">
        <label for="tone_6">Profound and sometimes enigmatic, touching on universal truths.</label>
    </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>Which themes resonate most with you?</legend>
            <fieldset class="options">
        <input type="radio" id="themes_1" name="themes" value="1" required>
        <label for="themes_1">Good versus evil, coming of age, the hero's journey.</label>
        <br>
        <input type="radio" id="themes_2" name="themes" value="2">
        <label for="themes_2">Transformation, the power of kindness, hidden wisdom in simple packages.</label>
        <br>
        <input type="radio" id="themes_3" name="themes" value="3">
        <label for="themes_3">Balance between mundane and magical worlds, finding one's place between realms.</label>
        <br>
        <input type="radio" id="themes_4" name="themes" value="4">
        <label for="themes_4">How history might have been, cultural identity, forgotten knowledge.</label>
        <br>
        <input type="radio" id="themes_5" name="themes" value="5">
        <label for="themes_5">The extraordinary within ordinary life, family legacies, cultural memory.</label>
        <br>
        <input type="radio" id="themes_6" name="themes" value="6">
        <label for="themes_6">Humanity's relationship with divine forces, archetypal journeys, cycles of existence.</label>
    </fieldset>
        </fieldset>
        
<input type="submit" value="Discover Your Fantasy Subgenre">
</form>
<?php endif; ?>

<p>•--♡--•</p>
<p class="essentials"><strong>Copy & Share</strong>: <a href="/forms/fantasy">zacharykai.net/forms/fantasy</a></p>
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