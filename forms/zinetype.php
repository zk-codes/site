<?php
// Initialize variables
$results = '';
$wordCount = 0;
$readingTime = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Calculate score based on answers
    $score = 0;
    
    if (isset($_POST['expression'])) {
        $score += intval($_POST['expression']);
    }
    
    if (isset($_POST['production'])) {
        $score += intval($_POST['production']);
    }
    
    if (isset($_POST['audience'])) {
        $score += intval($_POST['audience']);
    }
    
    if (isset($_POST['content'])) {
        $score += intval($_POST['content']);
    }
    
    if (isset($_POST['aesthetic'])) {
        $score += intval($_POST['aesthetic']);
    }
    
    // Determine zine type based on score
    if ($score <= 5) {
        $zineType = "Perzine";
        $description = "You're a Perzine—intimate, authentic, and courageously vulnerable. Like this deeply personal zine format, you value honest self-expression and emotional truth above all. You're the compassionate friend who shares their struggles to help others feel less alone. Your greatest strength is turning your personal experiences into universal insights. When faced with challenges, you process them through creative reflection rather than hiding your feelings. Your mantra might be: 'Vulnerability is strength, and your story matters.'";
    } elseif ($score <= 9) {
        $zineType = "Art Zine";
        $description = "You're an Art Zine—visually striking, boundary-pushing, and expressively bold. Like this visually-driven format, you communicate through imagery and aesthetic choices as much as words. You're the visual thinker who sees connections others miss. Your ability to translate concepts into visual language makes complex ideas accessible in new ways. When given constraints, you see them as creative challenges rather than limitations. Your mantra might be: 'A single image can speak volumes more than a thousand words.'";
    } elseif ($score <= 13) {
        $zineType = "Fanzine";
        $description = "You're a Fanzine—passionate, community-building, and celebratory. Like this enthusiasm-driven format, you deeply engage with the things you love and build connections through shared interests. You're the enthusiastic friend who introduces people to new obsessions. Your talent for spotting overlooked gems helps elevate underappreciated works and artists. When you discover something wonderful, your instinct is to create a space for others to appreciate it too. Your mantra might be: 'Shared joy is multiplied joy.'";
    } elseif ($score <= 17) {
        $zineType = "Political Zine";
        $description = "You're a Political Zine—thought-provoking, principled, and action-oriented. Like this change-making format, you believe in translating ideas into tangible impacts on the world. You're the insightful advocate who helps others see systemic patterns. Your courage to speak uncomfortable truths creates space for necessary conversations. When faced with injustice, you're compelled to document, analyze, and organize rather than remain silent. Your mantra might be: 'Another world is not only possible but necessary.'";
    } elseif ($score <= 21) {
        $zineType = "Instructional Zine";
        $description = "You're an Instructional Zine—practical, generous, and empowering. Like this skill-sharing format, you believe knowledge should be accessible to everyone. You're the supportive teacher who breaks down complex concepts into manageable steps. Your talent for clear explanation helps others gain confidence in new skills. When you learn something valuable, your instinct is to document it to help others along similar paths. Your mantra might be: 'Knowledge shared is knowledge multiplied.'";
    } else {
        $zineType = "Compilation Zine";
        $description = "You're a Compilation Zine—collaborative, inclusive, and connective. Like this community-centered format, you excel at bringing diverse voices together into cohesive wholes. You're the natural facilitator who creates platforms for others to shine. Your talent for spotting connections between seemingly disparate elements creates unexpected insights. When organizing projects, you prioritize multiple perspectives over single narratives. Your mantra might be: 'The whole becomes greater than the sum of its parts.'";
    }
    
    // Create results content
    $results = "<h2>You are a $zineType!</h2>
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
<title>What Type of Zine Are You? | Zachary Kai</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" href="/assets/icon.ico" type="image/x-icon">
<link rel="stylesheet" href="/assets/style.css">
<link rel="alternate" type="application/rss+xml" title="Zachary Kai" href="/assets/rss.xml">
<link rel="webmention" href="https://webmention.io/zacharykai.net/webmention">
<link rel="canonical" href="https://zacharykai.net/">
<meta name="date" content="<?php echo date('Y-m-d'); ?>">
<meta name="last-modified" content="<?php echo date('Y-m-d'); ?>">
<meta name="description" content="Discover which type of zine matches your creative personality. A playful exploration of DIY publishing styles and personal expression.">
</head>
<body>
<p><a href="#top" class="essentials">Begin reading...</a></p>
<header><nav><a href="/" title="My homepage.">Zachary Kai</a></nav></header>
<main>
<h1>What Type of Zine Are You?</h1>
<p class="postmeta"><strong>Published</strong>: <?php echo $currentDate; ?> | <strong>Updated</strong>: <?php echo $currentDate; ?></p>
<p id="top">Our approaches to creative expression, community building, and DIY publishing reveal much about ourselves. This quiz will uncover which zine format resonates most with your creative spirit.</p>
<p>Answer honestly. The photocopier doesn't judge, and neither do I.</p>
<p>As with all personality quizzes, this is more exploration than definition!</p>

<?php if ($results): ?>
<!-- Display quiz results -->
<?php echo $results; ?>
<p><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">Take the quiz again</a></p>
<?php else: ?>
<!-- Display quiz form -->    
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <fieldset>
            <legend>How do you prefer to express yourself?</legend>
            <fieldset class="options">
        <input type="radio" id="expression_1" name="expression" value="1" required>
        <label for="expression_1">Through personal stories and intimate reflections from my own life.</label>
        <br>
        <input type="radio" id="expression_2" name="expression" value="2">
        <label for="expression_2">Through visual art, illustrations, and creative design elements.</label>
        <br>
        <input type="radio" id="expression_3" name="expression" value="3">
        <label for="expression_3">Through enthusiastic deep-dives into topics and works I'm passionate about.</label>
        <br>
        <input type="radio" id="expression_4" name="expression" value="4">
        <label for="expression_4">Through analysis of social issues and advocacy for meaningful change.</label>
        <br>
        <input type="radio" id="expression_5" name="expression" value="5">
        <label for="expression_5">Through practical guides and step-by-step instructions to help others.</label>
        <br>
        <input type="radio" id="expression_6" name="expression" value="6">
        <label for="expression_6">Through collecting and curating diverse voices and perspectives.</label>
    </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>What's your approach to production and distribution?</legend>
            <fieldset class="options">
        <input type="radio" id="production_1" name="production" value="1" required>
        <label for="production_1">Intimate and limited—handmade for close friends and specific communities.</label>
        <br>
        <input type="radio" id="production_2" name="production" value="2">
        <label for="production_2">Focused on high-quality reproduction of visual elements, even in small runs.</label>
        <br>
        <input type="radio" id="production_3" name="production" value="3">
        <label for="production_3">Distributed through networks of people who share specific interests or fandoms.</label>
        <br>
        <input type="radio" id="production_4" name="production" value="4">
        <label for="production_4">Accessible and widespread—the message is more important than the medium.</label>
        <br>
        <input type="radio" id="production_5" name="production" value="5">
        <label for="production_5">Clear and functional—making sure information is presented in the most useful way.</label>
        <br>
        <input type="radio" id="production_6" name="production" value="6">
        <label for="production_6">Collaborative and community-based, involving multiple contributors in decisions.</label>
    </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>Who do you create for?</legend>
            <fieldset class="options">
        <input type="radio" id="audience_1" name="audience" value="1" required>
        <label for="audience_1">Primarily for myself as a form of processing—if others connect with it, that's a bonus.</label>
        <br>
        <input type="radio" id="audience_2" name="audience" value="2">
        <label for="audience_2">For those who appreciate visual storytelling and aesthetic experiences.</label>
        <br>
        <input type="radio" id="audience_3" name="audience" value="3">
        <label for="audience_3">For fellow enthusiasts who share my passion for specific subjects or cultures.</label>
        <br>
        <input type="radio" id="audience_4" name="audience" value="4">
        <label for="audience_4">For those seeking alternative perspectives on social and political issues.</label>
        <br>
        <input type="radio" id="audience_5" name="audience" value="5">
        <label for="audience_5">For people looking to learn practical skills or understand how-to processes.</label>
        <br>
        <input type="radio" id="audience_6" name="audience" value="6">
        <label for="audience_6">For diverse communities seeking connection and representation in printed form.</label>
    </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>What content excites you most?</legend>
            <fieldset class="options">
        <input type="radio" id="content_1" name="content" value="1" required>
        <label for="content_1">Confessional writing, diary entries, personal essays, and intimate reflections.</label>
        <br>
        <input type="radio" id="content_2" name="content" value="2">
        <label for="content_2">Visual art, comics, photography, experimental layouts, and graphic design.</label>
        <br>
        <input type="radio" id="content_3" name="content" value="3">
        <label for="content_3">Reviews, interviews, recommendations, and celebrations of niche interests.</label>
        <br>
        <input type="radio" id="content_4" name="content" value="4">
        <label for="content_4">Critical essays, manifestos, calls to action, and resource lists for advocacy.</label>
        <br>
        <input type="radio" id="content_5" name="content" value="5">
        <label for="content_5">Tutorials, recipes, DIY guides, skill-shares, and accessible how-to content.</label>
        <br>
        <input type="radio" id="content_6" name="content" value="6">
        <label for="content_6">Themed collections bringing together multiple contributors and diverse voices.</label>
    </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>What aesthetic or production values are most important to you?</legend>
            <fieldset class="options">
        <input type="radio" id="aesthetic_1" name="aesthetic" value="1" required>
        <label for="aesthetic_1">Raw authenticity—handwritten text, personal touches, genuine imperfections.</label>
        <br>
        <input type="radio" id="aesthetic_2" name="aesthetic" value="2">
        <label for="aesthetic_2">Visual impact—interesting layout, striking imagery, and thoughtful design elements.</label>
        <br>
        <input type="radio" id="aesthetic_3" name="aesthetic" value="3">
        <label for="aesthetic_3">Insider references and cultural touchpoints that resonate with specific communities.</label>
        <br>
        <input type="radio" id="aesthetic_4" name="aesthetic" value="4">
        <label for="aesthetic_4">Directness and clarity—content that challenges systems and sparks conversation.</label>
        <br>
        <input type="radio" id="aesthetic_5" name="aesthetic" value="5">
        <label for="aesthetic_5">Usability and clarity—well-structured information that's easy to follow and apply.</label>
        <br>
        <input type="radio" id="aesthetic_6" name="aesthetic" value="6">
        <label for="aesthetic_6">Cohesive variety—bringing together different styles into a harmonious whole.</label>
    </fieldset>
        </fieldset>
        
<input type="submit" value="Discover Your Zine Type">
</form>
<?php endif; ?>

<p>•--♡--•</p>
<p class="essentials"><strong>Copy & Share</strong>: <a href="/forms/zine">zacharykai.net/forms/zine</a></p>
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