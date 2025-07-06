<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Webring Members

$sites = [
    [
        'id' => 1,
        'site_name' => 'Zachary Kai',
        'site_url' => 'https://zacharykai.net/',
    ],
    [
        'id' => 2,
        'site_name' => 'Joe Crawford',
        'site_url' => 'https://artlung.com/',
    ],
];

// Find Current Site Index Based On Referrer

function findCurrentSiteIndex($sites, $referrer_url) {
    if (empty($referrer_url)) {
        return -1;
    }
    
    foreach ($sites as $index => $site) {
        $site_domain = parse_url($site['site_url'], PHP_URL_HOST);
        $referrer_domain = parse_url($referrer_url, PHP_URL_HOST);
        
        if ($site_domain && $referrer_domain) {
            // Remove www. for comparison
            $site_domain = preg_replace('/^www\./', '', $site_domain);
            $referrer_domain = preg_replace('/^www\./', '', $referrer_domain);
            
            if ($site_domain === $referrer_domain) {
                return $index;
            }
        }
    }
    
    return -1;
}

// Check If It's A Navigation Request

$action = $_GET['action'] ?? null;

if ($action && in_array($action, ['random', 'next', 'prev', 'previous', 'list', 'all'])) {
    
    // Handle Navigation
    $referrer = $_SERVER['HTTP_REFERER'] ?? '';

    if (empty($sites)) {
        header("Location: /");
        exit;
    }

    $target_site = null;

    switch ($action) {
        case 'random':
            $random_index = array_rand($sites);
            $target_site = $sites[$random_index];
            break;
            
        case 'next':
            $current_index = findCurrentSiteIndex($sites, $referrer);
            if ($current_index >= 0) {
                $next_index = ($current_index + 1) % count($sites);
                $target_site = $sites[$next_index];
            } else {
                $target_site = $sites[0];
            }
            break;
            
        case 'prev':
        case 'previous':
            $current_index = findCurrentSiteIndex($sites, $referrer);
            if ($current_index >= 0) {
                $prev_index = ($current_index - 1 + count($sites)) % count($sites);
                $target_site = $sites[$prev_index];
            } else {
                $target_site = end($sites);
            }
            break;
            
        case 'list':
        case 'all':
            header("Location: /#members");
            exit;
    }

    // Redirect To Target Site If Found
    if ($target_site) {
        $redirect_url = $target_site['site_url'];
        
        if (!preg_match('/^https?:\/\//', $redirect_url)) {
            $redirect_url = 'http://' . $redirect_url;
        }
        
        header("Location: " . $redirect_url);
        exit;
    } else {
        header("Location: /");
        exit;
    }
}

// Display the main webring page
?>
<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8"/>
        <title>Star Wars Webring</title>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <link rel="shortcut icon" href="/assets/icon.ico" type="image/x-icon"/>
        <link rel="stylesheet" href="/assets/style.css"/>
        <link rel="stylesheet" href="/assets/styles/print.css" media="print">
        <link href="/assets/manifest.json" rel="manifest"/>
        <link rel="alternate" type="application/rss+xml" title="Star Wars Webring" href="/assets/rss.xml"/>
        <link rel="webmention" href="https://webmention.io/zacharykai.net/webmention"/>
        <link rel="canonical" href="https://zacharykai.net/lists/starwars"/>
        <meta name="date" content="2025-07-05"/>
        <meta name="last-modified" content="2025-07-05"/>
        <meta name="description" content="A webring connecting Star Wars fans across the internet galaxy."/>
    </head>
    <body>
        <p><a href="#top" class="essentials">Begin reading...</a></p>
        <header><nav><a href="/">Zachary Kai</a></nav></header>
        <main>
            <header>
                <p class="breadcrumbs"><a href="/">Homepage</a> • <a href="/sitemap#lists">Lists</a></p>
                <h1 class="p-name">Star Wars Webring</h1>
                <p class="postmeta">
                    <strong>Published</strong>: <time class="dt-published" datetime="2025-07-05">5 Jul 2025</time> | 
                    <strong>Updated</strong>: <time class="dt-modified" datetime="2025-07-05">5 Jul 2025</time>
                </p>
            </header>
            <p id="top" class="p-summary">A webring connecting Star Wars fan sites across the galaxy. May the Force be with you!</p>
            <p><strong>Disclaimer</strong>: This webring is a fan-created project and is not affiliated with Disney, Lucasfilm, or any official Star Wars entities. Star Wars is a trademark of Disney/Lucasfilm. All fan content is created for non-commercial purposes.</p>
            <details>
                <summary><strong>Table Of Contents</strong></summary>
                <ul>
                    <li><a href="#navigate">Navigate The Galaxy</a></li>
                    <li><a href="#members">Members</a></li>
                    <li><a href="#guidelines">Submission Guidelines</a></li>
                    <li><a href="#join">Join The Webring</a></li>
                </ul>
            </details>
            <h2 id="navigate">Navigate The Galaxy</h2>
            <p>Webrings are a classic way for folks to find others with similar interests! Use these links to explore other sites in the webring:</p>
            <ul>
                <li><a href="?action=random">Random Site</a> - Jump to a random member site</li>
                <li><a href="?action=next">Next Site</a> - Visit the next site in the ring</li>
                <li><a href="?action=prev">Previous Site</a> - Visit the previous site in the ring</li>
                <li><a href="#members">View All Sites</a> - See the complete member list</li>
            </ul>
            <p></p>
            <hr>
            <h2 id="members">Current Members</h2>
            <p><em>Listed in order of joining.</em></p>
            
            <?php if (empty($sites)): ?>
                <p>No one yet. Check back soon!</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($sites as $site): ?>
                    <li><a href="<?php echo htmlspecialchars($site['site_url']); ?>" rel="noopener"><?php echo htmlspecialchars($site['site_name']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <h2 id="guidelines">Submission Guidelines</h2>
            <p>To join, your site must be a personal one and actively maintained. And have the below code somewhere visible! (Format it and style it however you'd like, provided it's accessible.)</p>
            <p><pre><code>
                <a href="https://zacharykai.net/lists/starwars">Star Wars Webring</a> → <a href="https://zacharykai.net/lists/starwars?action=prev">Previous</a> | <a href="https://zacharykai.net/lists/starwars?action=random">Random</a> | <a href="https://zacharykai.net/lists/starwars?action=next">Next</a>
            </code></pre></p>
            <h3 id="join">Join The Webring</h3>
            <p>Want to join the webring? Use the form below or <a href="/hello">contact me</a>!</p>
            <ul>
                <li>I'll check your site meets the guidelines</li>
                <li>Once approved, I'll email you with the HTML code for webring navigation</li>
                <li>You can request changes or removal at any time</li>
                <li>Email addresses are never displayed publicly</li>
            </ul>
            <form id="submission-form" action="/assets/scripts/submissions.php" method="post">
                <label for="name">Write what you'd like me to call you:*</label>
                <br/>
                <input type="text" id="name" name="name" required/>
                <br/>
                <label for="email">Enter in your email:*</label>
                <br/>
                <input type="email" id="email" name="email" required/>
                <br/>
                <label for="site_name">Type in your site's name:*</label>
                <br/>
                <input type="text" id="site_name" name="site_name" required/>
                <br/>
                <label for="site_url">Enter your site's URL:*</label>
                <br/>
                <input type="url" id="site_url" name="site_url" required/>
                <br/>
                <label for="favorite_character">Tell me your favorite Star Wars character:</label>
                <br/>
                <input type="text" id="favorite_character" name="favorite_character"/>
                <br/>
                <label for="captcha">Enter this page's title:*</label>
                <br>
                <input type="text" id="captcha" name="captcha" required>
                <br>
                <input type="hidden" name="form_type" value="starwars">
                <button type="submit">Join The Galaxy!</button>
            </form>
            <p>•--♡--•</p>
            <section class="essentials">
                <p><strong>Copy & Share</strong>: <a href="/lists/starwars" class="u-url">zacharykai.net/lists/starwars</a></p>
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
                    <a href="/source" rel="noopener">Suggest An Edit</a> |
                    <a href="/source" rel="noopener">View Source Code</a>
                </p>
            </section>
        </main>
        <section class="h-card vcard">
            <section class="h-card-image">
                <picture>
                    <source srcset="/assets/zk_icon.webp" type="image/webp">
                    <img class="u-photo" loading="lazy" src="/assets/zk_icon.png" alt="A colorful, stylized digital illustration by Zachary Kai featuring a stack of five books in shades of blue, teal, green, and purple, each with bold black geometric or abstract spine designs. A leafy green plant is tucked behind the top book, its fronds spreading upward. On either side of the stack is a purple heart.">
                </picture>
            </section>
            <section class="h-card-content">
                <p><strong><a class="u-url u-id p-name" href="https://zacharykai.net" rel="me"><span class="fn">Zachary Kai</span></a></strong> — <span class="p-pronouns">he/him</span> | <a class="u-email email" href="mailto:hi@zacharykai.net" rel="me">hi@zacharykai.net</a></p>
                <p class="p-note">Zachary Kai is a space fantasy writer, offbeat queer, traveler, zinester, and avowed generalist. The internet is his livelihood and lifeline.</p>
            </section>
        </section>
        <section class="acknowledgement">
            <p><strong>Acknowledgement Of Country</strong></p>
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