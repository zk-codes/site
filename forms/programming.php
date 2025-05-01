<?php
// Initialize variables
$results = '';
$wordCount = 0;
$readingTime = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Calculate score based on answers
    $score = 0;
    
    if (isset($_POST['problem_solving'])) {
        $score += intval($_POST['problem_solving']);
    }
    
    if (isset($_POST['learning_curve'])) {
        $score += intval($_POST['learning_curve']);
    }
    
    if (isset($_POST['community'])) {
        $score += intval($_POST['community']);
    }
    
    if (isset($_POST['structure'])) {
        $score += intval($_POST['structure']);
    }
    
    if (isset($_POST['projects'])) {
        $score += intval($_POST['projects']);
    }
    
    // Determine programming language based on score
    if ($score <= 5) {
        $language = "HTML";
        $description = "You're HTML—foundational, expressive, and universally understood. Like this language, you value clarity and accessibility above all. You're the thoughtful communicator who ensures everyone can understand the message. Your greatest strength is creating structure that supports and elevates content. When faced with a challenge, you focus on inclusivity and reaching the widest audience possible. Your mantra might be: 'Content should be accessible to everyone.'";
    } elseif ($score <= 9) {
        $language = "CSS";
        $description = "You're CSS—creative, detail-oriented, and visually expressive. Like this language, you see the beauty in subtle refinements and presentation. You're the artist who transforms the ordinary into something extraordinary. Your sensitivity to aesthetics allows you to create experiences that resonate emotionally. When given constraints, you find ingenious ways to express yourself within the boundaries. Your mantra might be: 'Form and function are inseparable.'";
    } elseif ($score <= 13) {
        $language = "PHP";
        $description = "You're PHP—practical, resourceful, and enduring. Like this language, you value getting things done and solving real-world problems. You're the craftsperson who builds useful tools that stand the test of time. Your pragmatic approach means you focus on outcomes rather than theoretical perfection. When faced with limitations, you work with what you have rather than waiting for ideal conditions. Your mantra might be: 'The best solution is one that works.'";
    } elseif ($score <= 17) {
        $language = "Python";
        $description = "You're Python—approachable, versatile, and beloved by many. Like this language, you believe in simplicity and readability. You're the friendly guide who helps newcomers find their way through complex terrain. Your greatest strength is making difficult concepts accessible. When faced with a challenge, you prefer an elegant solution over a complicated one. Your mantra might be: 'Simple is better than complex.'";
    } elseif ($score <= 21) {
        $language = "JavaScript";
        $description = "You're JavaScript—adaptable, everywhere, and constantly evolving. Like this language, you thrive in diverse environments and aren't afraid to reinvent yourself. You're the bridge-builder connecting different worlds. Your curiosity drives you to explore new frameworks and approaches. When faced with limitations, you find creative workarounds that others might miss. Your mantra might be: 'There's more than one way to solve a problem.'";
    } else {
        $language = "Rust";
        $description = "You're Rust—modern, safety-conscious, and forward-thinking. Like this language, you value innovation that doesn't compromise reliability. You're the pioneer who explores new territories while bringing others along. Your commitment to quality means you often challenge conventional wisdom. When developing solutions, you consider edge cases that others might overlook. Your mantra might be: 'Question assumptions, verify everything.'";
    }
    
    // Create results content
    $results = "<h2>You are $language!</h2>
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
<title>What Programming Language Are You? | Zachary Kai</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" href="/assets/icon.ico" type="image/x-icon">
<link rel="stylesheet" href="/assets/style.css">
<link rel="alternate" type="application/rss+xml" title="Zachary Kai" href="/assets/rss.xml">
<link rel="webmention" href="https://webmention.io/zacharykai.net/webmention">
<link rel="canonical" href="https://zacharykai.net/">
<meta name="date" content="<?php echo date('Y-m-d'); ?>">
<meta name="last-modified" content="<?php echo date('Y-m-d'); ?>">
<meta name="description" content="Discover which programming language matches your personality. A playful exploration of coding preferences and personal traits.">
</head>
<body>
<p><a href="#top" class="essentials">Begin reading...</a></p>
<header><nav><a href="/" title="My homepage.">Zachary Kai</a></nav></header>
<main>
<h1>What Programming Language Are You?</h1>
<p class="postmeta"><strong>Published</strong>: <?php echo $currentDate; ?> | <strong>Updated</strong>: <?php echo $currentDate; ?></p>
<p id="top">How we approach problem-solving, structure, and creativity varies. This quiz will reveal which language resonates most with your essence.</p>
<p>Answer honestly. The terminal doesn't judge, and neither do I.</p>
<p>As with all personality quizzes, this is more whimsy than science!</p>

<?php if ($results): ?>
<!-- Display quiz results -->
<?php echo $results; ?>
<p><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">Take the quiz again</a></p>
<?php else: ?>
<!-- Display quiz form -->    
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <fieldset>
            <legend>What's your approach to problem-solving?</legend>
            <fieldset class="options">
        <input type="radio" id="problem_1" name="problem_solving" value="1" required>
        <label for="problem_1">I prefer straightforward solutions that anyone can understand.</label>
        <br>
        <input type="radio" id="problem_2" name="problem_solving" value="2">
        <label for="problem_2">I'm flexible and adapt my approach based on the specific situation.</label>
        <br>
        <input type="radio" id="problem_3" name="problem_solving" value="3">
        <label for="problem_3">I like structured approaches with clear patterns and best practices.</label>
        <br>
        <input type="radio" id="problem_4" name="problem_solving" value="4">
        <label for="problem_4">I enjoy working close to the system, optimizing for performance.</label>
        <br>
        <input type="radio" id="problem_5" name="problem_solving" value="5">
        <label for="problem_5">I value correctness and prevention of errors above all else.</label>
    </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>How do you feel about learning curves?</legend>
            <fieldset class="options">
        <input type="radio" id="learning_1" name="learning_curve" value="1" required>
        <label for="learning_1">I prefer technologies that are easy to pick up with quick results.</label>
        <br>
        <input type="radio" id="learning_2" name="learning_curve" value="2">
        <label for="learning_2">I don't mind some complexity if it means greater flexibility.</label>
        <br>
        <input type="radio" id="learning_3" name="learning_curve" value="3">
        <label for="learning_3">I appreciate technologies with comprehensive documentation and established patterns.</label>
        <br>
        <input type="radio" id="learning_4" name="learning_curve" value="4">
        <label for="learning_4">I'm willing to invest significant time mastering difficult concepts for greater control.</label>
        <br>
        <input type="radio" id="learning_5" name="learning_curve" value="5">
        <label for="learning_5">I embrace steep learning curves that lead to more robust and efficient solutions.</label>
    </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>How important is community to you?</legend>
            <fieldset class="options">
        <input type="radio" id="community_1" name="community" value="1" required>
        <label for="community_1">I value large, welcoming communities with abundant learning resources.</label>
        <br>
        <input type="radio" id="community_2" name="community" value="2">
        <label for="community_2">I appreciate diverse ecosystems with many frameworks and libraries.</label>
        <br>
        <input type="radio" id="community_3" name="community" value="3">
        <label for="community_3">I prefer established communities with enterprise backing and stability.</label>
        <br>
        <input type="radio" id="community_4" name="community" value="4">
        <label for="community_4">I respect communities with deep technical expertise and historical knowledge.</label>
        <br>
        <input type="radio" id="community_5" name="community" value="5">
        <label for="community_5">I'm drawn to forward-thinking communities focused on innovation and best practices.</label>
    </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>How do you feel about structure and rules?</legend>
            <fieldset class="options">
        <input type="radio" id="structure_1" name="structure" value="1" required>
        <label for="structure_1">I prefer minimal syntax and freedom to express ideas my way.</label>
        <br>
        <input type="radio" id="structure_2" name="structure" value="2">
        <label for="structure_2">I like flexibility with optional structure when needed.</label>
        <br>
        <input type="radio" id="structure_3" name="structure" value="3">
        <label for="structure_3">I appreciate clear conventions and well-defined patterns.</label>
        <br>
        <input type="radio" id="structure_4" name="structure" value="4">
        <label for="structure_4">I value precise control even if it means more verbose code.</label>
        <br>
        <input type="radio" id="structure_5" name="structure" value="5">
        <label for="structure_5">I believe strong constraints often lead to better, safer code.</label>
    </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>What kinds of projects excite you most?</legend>
            <fieldset class="options">
        <input type="radio" id="projects_1" name="projects" value="1" required>
        <label for="projects_1">Content creation, documentation, or accessibility-focused projects.</label>
        <br>
        <input type="radio" id="projects_2" name="projects" value="2">
        <label for="projects_2">Design, user interfaces, or visual storytelling.</label>
        <br>
        <input type="radio" id="projects_3" name="projects" value="3">
        <label for="projects_3">Web applications, content management systems, or e-commerce.</label>
        <br>
        <input type="radio" id="projects_4" name="projects" value="4">
        <label for="projects_4">Data analysis, automation, or teaching tools.</label>
        <br>
        <input type="radio" id="projects_5" name="projects" value="5">
        <label for="projects_5">Interactive experiences, cross-platform apps, or system architecture.</label>
        <br>
        <input type="radio" id="projects_6" name="projects" value="6">
        <label for="projects_6">Next-generation tools, concurrent systems, or safety-critical software.</label>
    </fieldset>
        </fieldset>
        
<input type="submit" value="Discover Your Programming Language">
</form>
<?php endif; ?>

<p>•--♡--•</p>
<p class="essentials"><strong>Copy & Share</strong>: <a href="/forms/programming">zacharykai.net/forms/programming</a></p>
<p class="essentials">
    <strong>Reply Via</strong>:
    <a href="mailto:hi@zacharykai.net">Email</a> | 
    <a href="/guestbook">Guestbook</a> |
    <a href="/unoffice-hours">UnOffice Hours</a> | 
    <a href="https://webmention.io/zacharykai.net/webmention" target="_blank" rel="noopener">Webmention</a>
</p>
<p class="essentials"><strong>Statistics</strong> → Word Count: <?php echo $wordCount; ?> | Reading Time: <?php echo $readingTime; ?> mins</p>
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