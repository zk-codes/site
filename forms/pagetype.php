<?php
// Initialize variables
$results = '';
$wordCount = 0;
$readingTime = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Calculate score based on answers
    $score = 0;
    
    if (isset($_POST['content_approach'])) {
        $score += intval($_POST['content_approach']);
    }
    
    if (isset($_POST['communication'])) {
        $score += intval($_POST['communication']);
    }
    
    if (isset($_POST['presentation'])) {
        $score += intval($_POST['presentation']);
    }
    
    if (isset($_POST['purpose'])) {
        $score += intval($_POST['purpose']);
    }
    
    if (isset($_POST['personality'])) {
        $score += intval($_POST['personality']);
    }
    
    // Determine website page based on score
    if ($score <= 5) {
        $page = "About Page";
        $description = "You're an About Page—authentic, welcoming, and the perfect introduction. Like this cornerstone of personal websites, you value meaningful connections and honest self-presentation. You're the friendly face that makes others feel at ease from the first moment. Your greatest strength is balancing vulnerability with confidence. When meeting new people, you naturally create a comfortable space for genuine conversation. Your mantra might be: 'Authenticity creates the strongest connections.'";
    } elseif ($score <= 9) {
        $page = "Blog";
        $description = "You're a Blog—thoughtful, evolving, and full of insights. Like this dynamic section of a website, you're constantly growing and sharing your journey with others. You're the storyteller who finds meaning in everyday experiences. Your curiosity about the world makes you an engaging presence with fresh perspectives. When processing life events, you naturally reflect on their broader implications. Your mantra might be: 'Every experience has a lesson worth sharing.'";
    } elseif ($score <= 13) {
        $page = "Portfolio";
        $description = "You're a Portfolio—accomplished, selective, and results-oriented. Like this showcase section, you value tangible achievements and concrete evidence of growth. You're the craftsperson who takes pride in work well done. Your practical approach to life means you focus on building a meaningful body of work. When faced with challenges, you see them as opportunities to develop new skills worth displaying. Your mantra might be: 'Actions and results speak louder than words.'";
    } elseif ($score <= 17) {
        $page = "Contact Page";
        $description = "You're a Contact Page—accessible, purposeful, and connection-focused. Like this essential website element, you prioritize building bridges between people. You're the facilitator who makes difficult connections seem effortless. Your greatest strength is creating clear pathways for communication. When in social situations, you naturally connect people who should know each other. Your mantra might be: 'Meaningful relationships begin with an open door.'";
    } elseif ($score <= 21) {
        $page = "Projects Page";
        $description = "You're a Projects Page—creative, process-oriented, and always in motion. Like this dynamic section, you're defined by what you're building and the journey of creation. You're the innovator who finds joy in the process as much as the outcome. Your enthusiasm for new ideas makes you an inspiring presence who motivates others. When life gets routine, you seek out fresh challenges to tackle. Your mantra might be: 'The magic happens in the making.'";
    } else {
        $page = "Digital Garden";
        $description = "You're a Digital Garden—experimental, interconnected, and endlessly curious. Like this modern web concept, you value growth over perfection and connections over categories. You're the explorer who discovers unexpected patterns across seemingly unrelated domains. Your comfort with uncertainty allows you to thrive in ambiguity and emerging concepts. When approaching knowledge, you see it as an evolving ecosystem rather than fixed facts. Your mantra might be: 'Cultivate ideas, let them grow in unexpected directions.'";
    }
    
    // Create results content
    $results = "<h2>You are a $page!</h2>
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
<title>What Page on a Personal Website Are You? | Your Name</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" href="/assets/icon.ico" type="image/x-icon">
<link rel="stylesheet" href="/assets/style.css">
<meta name="date" content="<?php echo date('Y-m-d'); ?>">
<meta name="last-modified" content="<?php echo date('Y-m-d'); ?>">
<meta name="description" content="Discover which personal website page matches your personality. A playful exploration of digital identity and personal traits.">
</head>
<body>
<p><a href="#top" class="essentials">Begin reading...</a></p>
<header><nav><a href="/" title="My homepage.">Your Name</a></nav></header>
<main>
<h1>What Page on a Personal Website Are You?</h1>
<p class="postmeta"><strong>Published</strong>: <?php echo $currentDate; ?> | <strong>Updated</strong>: <?php echo $currentDate; ?></p>
<p id="top">How we express ourselves, connect with others, and share our work varies. This quiz will reveal which website page best represents your essence.</p>
<p>Answer honestly. The browser doesn't judge, and neither do I.</p>
<p>As with all personality quizzes, this is more whimsy than science!</p>

<?php if ($results): ?>
<!-- Display quiz results -->
<?php echo $results; ?>
<p><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">Take the quiz again</a></p>
<?php else: ?>
<!-- Display quiz form -->    
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <fieldset>
            <legend>How do you approach sharing content with others?</legend>
            <fieldset class="options">
                <input type="radio" id="approach_1" name="content_approach" value="1" required>
                <label for="approach_1">I prefer clear, straightforward information that introduces who I am.</label>
                <br>
                <input type="radio" id="approach_2" name="content_approach" value="2">
                <label for="approach_2">I enjoy writing longer-form content that explores ideas and experiences.</label>
                <br>
                <input type="radio" id="approach_3" name="content_approach" value="3">
                <label for="approach_3">I like showcasing my best work and accomplishments.</label>
                <br>
                <input type="radio" id="approach_4" name="content_approach" value="4">
                <label for="approach_4">I value direct, functional information that helps people reach me.</label>
                <br>
                <input type="radio" id="approach_5" name="content_approach" value="5">
                <label for="approach_5">I prefer highlighting what I'm currently working on and building.</label>
                <br>
                <input type="radio" id="approach_6" name="content_approach" value="6">
                <label for="approach_6">I like creating interconnected pieces of content that evolve over time.</label>
            </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>What's your communication style?</legend>
            <fieldset class="options">
                <input type="radio" id="communication_1" name="communication" value="1" required>
                <label for="communication_1">I'm personal and revealing—I want people to know the real me.</label>
                <br>
                <input type="radio" id="communication_2" name="communication" value="2">
                <label for="communication_2">I'm reflective and thoughtful—I process ideas through writing.</label>
                <br>
                <input type="radio" id="communication_3" name="communication" value="3">
                <label for="communication_3">I'm professional and results-oriented—I let my work speak for itself.</label>
                <br>
                <input type="radio" id="communication_4" name="communication" value="4">
                <label for="communication_4">I'm direct and accessible—I make it easy for people to reach me.</label>
                <br>
                <input type="radio" id="communication_5" name="communication" value="5">
                <label for="communication_5">I'm enthusiastic and process-focused—I share my journey of creation.</label>
                <br>
                <input type="radio" id="communication_6" name="communication" value="6">
                <label for="communication_6">I'm exploratory and non-linear—I connect ideas across different domains.</label>
            </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>How important is presentation to you?</legend>
            <fieldset class="options">
                <input type="radio" id="presentation_1" name="presentation" value="1" required>
                <label for="presentation_1">I value warmth and approachability over polish.</label>
                <br>
                <input type="radio" id="presentation_2" name="presentation" value="2">
                <label for="presentation_2">I appreciate a good reading experience with clear structure.</label>
                <br>
                <input type="radio" id="presentation_3" name="presentation" value="3">
                <label for="presentation_3">I believe in professional, visually impressive displays.</label>
                <br>
                <input type="radio" id="presentation_4" name="presentation" value="4">
                <label for="presentation_4">I prefer functional clarity that helps people accomplish their goals.</label>
                <br>
                <input type="radio" id="presentation_5" name="presentation" value="5">
                <label for="presentation_5">I like engaging, visual demonstrations of process and progress.</label>
                <br>
                <input type="radio" id="presentation_6" name="presentation" value="6">
                <label for="presentation_6">I'm comfortable with some messiness if it allows for richer connections.</label>
            </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>What's the main purpose of your online presence?</legend>
            <fieldset class="options">
                <input type="radio" id="purpose_1" name="purpose" value="1" required>
                <label for="purpose_1">To help people understand who I am as a person.</label>
                <br>
                <input type="radio" id="purpose_2" name="purpose" value="2">
                <label for="purpose_2">To share my thoughts, experiences, and evolving perspectives.</label>
                <br>
                <input type="radio" id="purpose_3" name="purpose" value="3">
                <label for="purpose_3">To showcase my skills and accomplishments to potential opportunities.</label>
                <br>
                <input type="radio" id="purpose_4" name="purpose" value="4">
                <label for="purpose_4">To make it easy for the right people to find and connect with me.</label>
                <br>
                <input type="radio" id="purpose_5" name="purpose" value="5">
                <label for="purpose_5">To document what I'm building and creating in my life.</label>
                <br>
                <input type="radio" id="purpose_6" name="purpose" value="6">
                <label for="purpose_6">To explore ideas publicly and build a network of connected thoughts.</label>
            </fieldset>
        </fieldset>
        
        <fieldset>
            <legend>Which best describes your personality?</legend>
            <fieldset class="options">
                <input type="radio" id="personality_1" name="personality" value="1" required>
                <label for="personality_1">I'm welcoming and authentic—I value genuine connections.</label>
                <br>
                <input type="radio" id="personality_2" name="personality" value="2">
                <label for="personality_2">I'm observant and analytical—I find meaning in everyday experiences.</label>
                <br>
                <input type="radio" id="personality_3" name="personality" value="3">
                <label for="personality_3">I'm achievement-oriented and selective—I take pride in quality work.</label>
                <br>
                <input type="radio" id="personality_4" name="personality" value="4">
                <label for="personality_4">I'm helpful and network-minded—I connect people with resources.</label>
                <br>
                <input type="radio" id="personality_5" name="personality" value="5">
                <label for="personality_5">I'm creative and process-focused—I love bringing new things to life.</label>
                <br>
                <input type="radio" id="personality_6" name="personality" value="6">
                <label for="personality_6">I'm curious and experimental—I see unexpected connections between ideas.</label>
            </fieldset>
        </fieldset>
        
        <input type="submit" value="Discover Your Website Page">
    </form>
<?php endif; ?>

<p>•--♡--•</p>
<p class="essentials"><strong>Copy & Share</strong>: <a href="/forms/website-page">yourwebsite.com/forms/website-page</a></p>
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
<section class="h-card-image"><img class="u-photo" loading="lazy" src="/assets/your_icon.png" alt="Your avatar."></section>
<section class="h-card-content">
<p><strong><a class="u-url p-name" href="https://yourwebsite.com" rel="me"><span class="fn">Your Name</span></a></strong> — <span class="p-pronouns">your/pronouns</span> | <a class="u-email email" href="mailto:hello@yourwebsite.com" rel="me">hello@yourwebsite.com</a></p>
<p class="p-note">Your brief bio goes here. A sentence or two about who you are and what you do.</p>
</section>
</section>
<p><a href="#top" class="essentials">Read again...</a></p>
<footer><p>Est. 2024 || <a href="/about">About</a> | <a href="/blog">Blog</a> | <a href="/portfolio">Portfolio</a> | <a href="/contact">Contact</a> | <a href="/projects">Projects</a></p></footer>
</body>
</html>