<!DOCTYPE html>
<html lang="en-US">

    <!-- Head -->
    <head>
        <!-- Meta Tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- File Links -->
        <link rel="shortcut icon" href="/assets/icon.ico" type="image/x-icon">
        <link rel="stylesheet" href="/assets/style.css">
        <link rel="stylesheet" href="/assets/print.css" media="print">
        <link rel="alternate" type="application/rss+xml" title="Zachary Kai" href="/assets/rss.xml">
        <link rel="webmention" href="https://webmention.io/zacharykai.net/webmention" />
        <link rel="canonical" href="https://zacharykai.net">
        <!-- Page Info -->
        <title>Zachary Kai</title>
        <meta name="date" content="2024-03-25">
        <meta name="last-modified" content="2025-10-24">
        <meta name="description" content="Hi! I'm Zachary Kai, a scifi writer, and the sole operator of Road Less Read. Also an offbeat queer, twenty-something, curiosity enthusiast, and traveler.">
    </head>

    <!-- Body -->
    <body>

        <!-- Skip Link -->
        <p><a href="#top" class="essentials">Begin reading...</a></p>

        <!-- Site Header -->
        <header><nav><a href="https://zacharykai.net/" class="u-url u-uid">Zachary Kai</a></nav></header>

        <!-- Main Content -->
        <main class="h-entry e-content">

            <!-- Page Header -->
            <header>
                <p class="essentials"><a href="/">Homepage</a> • <a href="/sitemap#pages">Pages</a> ↴</p>
                <h1 class="p-name">Zachary Kai</h1>
                <p class="essentials">
                    <strong>Written By</strong>: <a href="/about">Zachary Kai</a> »
                    <strong>Published</strong>: <time class="dt-published" datetime="2024-03-25">25 Mar 2024</time> | 
                    <strong>Updated</strong>: <time class="dt-modified" datetime="2025-11-08">8 Nov 2025</time>
                </p>
            </header>

            <!-- Page Body -->
            <section class="e-content">

                <!-- Introduction -->
                <p id="top" class="p-summary">Hi! I'm Zachary Kai, a <a href="https://lunaseeker.com/catalog" rel="noopener">space fantasy writer</a>. Also an offbeat queer, twenty-something, curiosity enthusiast, and traveler. An avowed generalist, and learning to be proud of it.</p>

                <!-- More About Me + Links -->
                <p><strong>I'm seeking remote work! Here's my <a href="https://lunaseeker.com/cv" rel="noopener">CV</a>. If I'd be a good fit, <a href="/hello">contact me</a>.</strong></p>
                <p>I'm Australian, and oscillate between living there and overseas. Now? I'm in Kosovo.</p>
                <p>I run <a href="https://www.roadlessread.com/" rel="noopener">Road Less Read</a>, where I compile my book notes. I make <a href="/art">art</a> and <a href="/zines">zines</a>, take <a href="/photos">photos</a>, so I'd love <a href="/hello#trade">to trade</a>! I also have <a href="/workshops" rel="noopener">various offerings</a> (which you can partake in!)</p>
                <p>See: <a href="/about">about me</a>, <a href="/now">what I'm working on</a>, <a href="/search">search the site</a>, and <a href="/sitemap">the sitemap</a> for everything else.</p>
                <p id="rel-me">Meeting folks is a delight! <a href="/hello">Contact me</a>, <a href="/guestbook">sign the guestbook</a>, or <a href="/unoffice-hours">book an UnOffice Hours call</a>.</p>
                <p>Want to go for a web wander? Visit the <a href="/blogroll">blogroll</a>, <a href="/bookmarks">my bookmarks</a>, or a <a href="/random">random page</a>.</p>
                
                <hr>

                <!-- Latest Notes: Script For Pulling In Latest Ones -->
                <h2>Latest Notes</h2>
                    
                    <ul>
                        <?php
                        $notesDir = __DIR__ . '/notes/';
                        $files = scandir($notesDir);
                        $posts = [];

                        foreach ($files as $file) {
                        if (pathinfo($file, PATHINFO_EXTENSION) === 'html') {
                            $filePath = $notesDir . $file;
                            $content = file_get_contents($filePath);
                            $title = '';
                            $dateAttr = '';

                        // Extract Title & Remove " | Zachary Kai"
                        if (preg_match('/<title>(.*?)<\/title>/s', $content, $matches)) {
                            $fullTitle = trim($matches[1]);
                            $title = preg_replace('/ \| Zachary Kai$/i', '', $fullTitle);
                        }

                        // Extract DateTime Attribute
                        if (preg_match('/<time\s+class="dt-published"\s+datetime="([^"]+)">.*?<\/time>/s', $content, $matches)) {
                            $dateAttr = $matches[1];
                        }

                        if (!empty($title) && !empty($dateAttr)) {
                            $slug = pathinfo($file, PATHINFO_FILENAME);
                            $formattedDisplayDate = date('j M Y', strtotime($dateAttr));
                            $posts[] = [
                                'title' => $title,
                                'date_attr' => $dateAttr,
                                'display_date' => $formattedDisplayDate,
                                'url' => '/notes/' . $slug,
                            ];
                            }
                            }
                        }

                        // Sort Posts In Descending Order
                        usort($posts, function($a, $b) {
                            return strtotime($b['date_attr']) - strtotime($a['date_attr']);
                        });

                        // Get Latest Six Posts
                        $latestSixPosts = array_slice($posts, 0, 6);

                        foreach ($latestSixPosts as $post) {
                        echo '                    <li><a href="' . htmlspecialchars($post['url']) . '">' . htmlspecialchars($post['title']) . '</a> | ' . htmlspecialchars($post['display_date']) . '</li>' . "\n";
                        }
                    ?>
                </ul>

                <hr>
                
                <!-- Webrings -->
                <h2 id="webrings">Webrings</h2>
                <ul>
                    <li>
                        <!-- A11y Webring -->
                        <strong>
                            <a href="https://a11y-webring.club/" rel="noopener">a11y-webring.club</a>
                        </strong>
                        →
                        <a rel="noopener" href="https://a11y-webring.club/prev">Previous</a>
                        |
                        <a rel="noopener" href="https://a11y-webring.club/random">Random</a>
                        |
                        <a rel="noopener" href="https://a11y-webring.club/next">Next</a>
                    </li>
                    <li>
                        <!-- Bucketfish Webring -->
                        <strong>
                            <a href="https://webring.bucketfish.me" rel="noopener">Bucketfish Webring</a>
                        </strong>
                        →
                        <a href="https://webring.bucketfish.me/redirect.html?to=prev&name=Zachary Kai" rel="noopener">Previous</a>
                        |
                        <a href="https://webring.bucketfish.me/redirect.html?to=next&name=Zachary Kai" rel="noopener">Next</a>
                    </li>
                    <li>
                        <!-- Catppuccin Webring -->
                        <strong>
                            <a href="https://ctp-webr.ing/">Catppuccin Webring</a>
                        </strong>
                        →
                        <a href="https://ctp-webr.ing/zacharykai/previous">Previous</a>
                        |
                        <a href="https://ctp-webr.ing/zacharykai/next">Next</a>
                    </li>
                    <li>
                        <!-- CSS Joy Webring -->
                        <strong>
                            <a href="https://cs.sjoy.lol/" rel="noopener">CSS Joy Webring</a>
                        </strong>
                        →
                        <a href="https://webri.ng/webring/cssjoy/previous?via=https://zacharykai.net/" rel="noopener">Previous</a>
                        |
                        <a href="https://webri.ng/webring/cssjoy/random?via=https://zacharykai.net/" rel="noopener">Random</a>
                        |
                        <a href="https://webri.ng/webring/cssjoy/next?via=https://zacharykai.net/" rel="noopener">Next</a>
                    </li>
                    <li>
                        <!-- Design Systems Webring -->
                        <strong>
                            <a href="https://design-system.club/" rel="noopener">Design Systems Webring</a>
                        </strong>
                        →
                        <a href="https://design-system.club/prev" rel="noopener">Previous</a>
                        |
                        <a href="https://design-system.club/random" rel="noopener">Random</a>
                        |
                        <a href="https://design-system.club/next" rel="noopener">Next</a>
                    </li>
                    <li>
                        <!-- Ghostring -->
                        <strong>
                            <a href="https://ghostring.neocities.org/" rel="noopener">Ghostring</a>
                        </strong>
                        →
                        <a href="https://ghostring.neocities.org/zacharykai/previous" rel="noopener">Previous</a>
                        |
                        <a href="https://ghostring.neocities.org/zacharykai/next" rel="noopener">Next</a> 
                    </li>
                    <li>
                        <!-- IndieWeb Webring -->
                        <strong>
                            <a href="https://xn--sr8hvo.ws" rel="noopener">IndieWeb Webring 🕸💍</a> 
                        </strong>
                        →
                        <a href="https://xn--sr8hvo.ws/previous" rel="noopener">Previous</a>
                        |
                        <a href="https://xn--sr8hvo.ws/next" rel="noopener">Next</a>
                    </li>
                    <li>
                        <!-- Loop Webring -->
                        <strong>
                            <a href="https://loop.graycot.dev/webring.html?action=home" rel="noopener">Loop Webring</a>
                        </strong>
                        →
                        <a href="https://loop.graycot.dev/webring.html?action=prev" rel="noopener">Previous</a>
                        |
                        <a href="https://loop.graycot.dev/webring.html?action=next" rel="noopener">Next</a>
                    </li>
                    <li>
                        <!-- Meta Ring -->
                        <strong>
                            <a href="https://meta-ring.hedy.dev/" rel="noopener">Meta Ring</a>
                        </strong>
                        →
                        <a href="https://meta-ring.hedy.dev/previous" rel="noopener">Previous</a>
                        |
                        <a href="https://meta-ring.hedy.dev/random" rel="noopener">Random</a>
                        |
                        <a href="https://meta-ring.hedy.dev/next" rel="noopener">Next</a>
                    </li>
                    <li>
                        <!-- Muhokama.Fun Ring -->
                        <strong>
                            <a href="https://ring.muhokama.fun">Muhokama.Fun Ring</a>
                        </strong>
                        →
                        <a href="https://ring.muhokama.fun/u/zacharykai/pred">Previous</a>
                        |
                        <a href="https://ring.muhokama.fun/u/zacharykai/succ">Next</a>
                    </li>
                    <li>
                        <!-- Retronaut Webring -->
                        <strong>
                            <a href="https://webring.dinhe.net/" rel="noopener">Retronaut Webring</a>
                        </strong>
                        →
                        <a href="https://webring.dinhe.net/prev/zacharykai.net" rel="noopener">Previous</a>
                        |
                        <a href="https://webring.dinhe.net/next/zacharykai.net" rel="noopener">Next</a>
                    </li>
                    <li>
                        <!-- Star Wars Webring -->
                        <strong>
                            <a href="/lists/starwars" rel="noopener">Star Wars Webring</a>
                        </strong>
                        →
                        <a href="/lists/starwars?action=prev" rel="noopener">Previous</a>
                        |
                        <a href="/lists/starwars?action=random" rel="noopener">Random</a>
                        |
                        <a href="/lists/starwars?action=next" rel="noopener">Next</a>
                    </li>
                    <li>
                        <!-- The Claw Webring -->
                        <strong>
                            <a href="https://github.com/whitep4nth3r/the-claw-webring" rel="noopener">The Claw Webring</a>
                        </strong>
                    </li>
                    <li>
                        <!-- UnOffice Hours Webring -->
                        <strong>
                            <a href="https://unofficehours.com/" rel="noopener">UnOffice Hours Webring</a>
                        </strong>
                        →
                        <a href="https://unofficehours.com/prev.html" rel="noopener">Previous</a>
                        |
                        <a href="https://unofficehours.com/random.html" rel="noopener">Random</a>
                        |
                        <a href="https://unofficehours.com/next.html" rel="noopener">Next</a>
                    </li>
                    <li>
                        <!-- Webmaster Webring -->
                        <strong>
                            <a href="https://webmasterwebring.netlify.app">Webmaster Webring</a>
                        </strong>
                        →
                        <a href="https://webmasterwebring.netlify.app?zacharykai-previous" rel="noopener">Previous</a>
                        |
                        <a href="https://webmasterwebring.netlify.app?zacharykai-random">Random</a>
                        |
                        <a href="https://webmasterwebring.netlify.app?zacharykai-next" rel="noopener">Next</a>
                    </li>
                    <li>
                        <!-- XXIIVV Webring -->
                        <strong>
                            <a href="https://webring.xxiivv.com/#zacharykai" rel="noopener">XXIIVV Webring</a>
                        </strong>
                    </li>
                </ul>

                <p>•--♡--•</p>
            </section>
           
        </main>

        <!-- Back To Top Link -->
        <p><a href="#top" class="essentials">Read again...</a></p>

        <!-- H-Card -->
        <section class="h-card vcard">
            <section id="h-card-image">
                <picture>
                    <source srcset="/assets/icon.webp" type="image/webp">
                    <img class="u-photo" loading="lazy" src="/assets/icon.png" alt="Zachary Kai's digital drawing: 5 stacked books (blue/teal/green/purple, black spine designs), green plant behind top book, purple heart on either side.">
                </picture>
            </section>
            <section id="h-card-content">
                <p><strong><a class="u-url u-id p-name" href="https://zacharykai.net" rel="me"><span class="fn">Zachary Kai</span></a></strong> — <span class="p-pronouns">he/him</span> | <a class="u-email email" href="mailto:hi@zacharykai.net" rel="me">hi@zacharykai.net</a></p>
                <p class="p-note">Zachary Kai is a space fantasy writer, offbeat queer, traveler, zinester, and avowed generalist. The internet is his livelihood and lifeline.</p>
            </section>
        </section>

        <!-- Footer -->
        <footer>

            <!-- Acknowledgement Of Country -->
            <p style="margin-top: 0.25em;"><strong>Acknowledgement Of Country</strong>: I owe my existence to the <a href="https://kht.org.au/" rel="noopener">Koori people's</a> lands: tended for millennia by the traditional owners and storytellers. What a legacy. May it prevail.</p>

            <!-- Reply Via -->
            <p>
                <strong>Reply Via</strong>:
                <a href="/contact">Email</a> | 
                <a href="/guestbook">Guestbook</a> |
                <a href="/unoffice-hours">UnOffice Hours</a> | 
                <a href="/webmention" rel="noopener">Webmention</a>
            </p>

            <!-- Footer Menu -->
            <p>
                <strong>Est. 2024</strong> || 
                <a href="/about">About</a> | 
                <a href="/art">Art</a> | 
                <a href="/colophon">Accessibility & Colophon</a> | 
                <a href="/changelog">Changelog</a> |
                <a href="/jots">Jots</a> | 
                <a href="/now">Now</a> |
                <a href="/random">Random</a> | 
                <a href="/assets/rss.xml">RSS</a> |  
                <a href="/sitemap">Sitemap</a> | 
                <a href="/uses">Uses</a>
            </p>

            <!-- Elsewhere Links -->
            <p style="margin-bottom: 0;">
                <strong>Elsewhere</strong>:
                <a href="https://roadlessread.com" rel="noopener">Blog</a> |
                <a href="https://lunaseeker.com/catalog/" rel="noopener">Books</a> |
                <a href="https://lunaseeker.com/cv" rel="noopener">CV</a> |
                <a href="/github" rel="noopener">Github</a> | 
                <a href="/linkedin" rel="noopener">Linkedin</a> |
                <a href="https://lunaseeker.com/newsletter/" rel="noopener">Newsletter</a> |
                <a href="https://lunaseeker.com/offerings" rel="noopener">Offerings</a> |
                <a href="https://lunaseeker.com" rel="noopener">Portfolio</a>
            </p>
        </footer>
        
    </body>
</html>