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
                <p class="essentials"><a href="/">Homepage</a> • <a href="/sitemap#pages">Pages</a></p>
                <h1 class="p-name">Zachary Kai</h1>
                <p class="essentials">
                    <strong>Published</strong>: <time class="dt-published" datetime="2024-03-25">25 Mar 2024</time> | 
                    <strong>Updated</strong>: <time class="dt-modified" datetime="2025-10-24">24 Oct 2025</time>
                </p>
            </header>

            <!-- Page Body -->
            <section class="e-content">

                <!-- Introduction -->
                <p id="top" class="p-summary">Hi! I'm Zachary Kai, a <a href="/books">space fantasy writer</a> (THE MARA FILES, DON'T BURN TOO BRIGHT). Also an offbeat queer, twenty-something, curiosity enthusiast, and traveler.</p>

                <!-- More About Me + Links -->
                <p><strong>I'm seeking remote work! Here's my <a href="https://lunaseeker.com/cv" rel="noopener">CV</a>. Interested in seeing how I can help? <a href="/hello">Contact me</a>!</strong></p>
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
                
                <!-- Buttons -->
                <section>
                    <h2 id="buttons">Buttons</h2>
                    <p>Buttons are little internet images depicting other folks' sites, and more. Who knows what you might discover? Here's <a href="/assets/buttons/zk.png">my 88x31 button</a>, if you'd like it!</p>

                    <!-- Folks' Sites -->
                    <section class="image-group" style="margin-bottom: 1em;">

                        <!-- 32-Bit Cafe -->
                        <a href="https://32bit.cafe/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/greenteam.jpg" alt="32-Bit Cafe">
                        </a>
                        <!-- 512 KB Club -->
                        <a href="https://32bit.cafe/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/32bitcafe.jpg" alt="512 KB Club">
                        </a>
                        <!-- 32-Bit Cafe: Discourse -->
                        <a href="https://discourse.32bit.cafe/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/discourse.jpg" alt="32-Bit Cafe Discourse">
                        </a>
                        <!-- Alex Zee -->
                        <a href="https://alexzeecomedy.com/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/alexzee.jpg" alt="Alex Zee Comedy">
                        </a>
                        <!-- Artlung -->
                        <a href="https://artlung.com/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/artlung.jpg" alt="Artlung">
                        </a>
                        <!-- Ava's Space -->
                        <a href="https://avas.space/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/avasspace.png" alt="Ava's Space">
                        </a>
                        <!-- Benji -->
                        <a href="https://benji.dog/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/benji.jpg" alt="benji.dog">
                        </a>
                        <!-- Daryl Sun -->
                        <a href="https://blog.darylsun.page/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/darylsun.jpg" alt="Daryl Sun">
                        </a>
                        <!-- Dead Garden -->
                        <a href="https://dead.garden/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/deadgarden.jpg" alt="Dead Garden">
                        </a>
                        <!-- Flamed Fury -->
                        <a href="https://flamedfury.com/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/flamedfury.jpg" alt="Flamed Fury">
                        </a>
                        <!-- Folkmoss -->
                        <a href="https://folkmoss.bearblog.dev/" rel="noopener">
                            <img class="button-images" loading="lazy" src="/assets/buttons/folkmoss.jpg" alt="Folkmoss">
                        </a>
                        <!-- Forever Like This -->
                        <a href="https://foreverliketh.is/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/foreverlikethis.png" alt="Forever Like This">
                        </a>
                        <!-- GusBus -->
                        <a href="https://gusbus.space/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/gusbus.jpg" alt="GusBus">
                        </a>
                        <!-- IndieWeb -->
                        <a href="https://indieweb.org/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/indieweb.jpg" alt="IndieWeb">
                        </a>
                        <!-- Internet Archive -->
                        <a href="https://archive.org/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/internetarchive.jpg" alt="Internet Archive">
                        </a>
                        <!-- James' Coffee Blog -->
                        <a href="https://jamesg.blog/" rel="noopener">
                            <img class="button-images" loading="lazy" src="/assets/buttons/jamescoffee.jpg" alt="James' Coffee Blog">
                        </a>
                        <!-- Kupietz -->
                        <a href="https://www.michaelkupietz.com/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/kupietz.jpg" alt="Kupietz Arts + Code">
                        </a>
                        <!-- Leilukin -->
                        <a href="https://leilukin.com/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/leilukin.png" alt="Leilukin">
                        </a>
                        <!-- Mark Sutherland -->
                        <a href="https://marksuth.dev/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/marksuth.jpg" alt="Mark Sutherland">
                        </a>
                        <!-- Mewizard -->
                        <a href="https://mewizard.nekoweb.org/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/mewizard.png" alt="Mewizard">
                        </a>
                        <!-- Messy Mind -->
                        <a href="https://messymind.me/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/messymind.jpg" alt="Messy Mind">
                        </a>
                        <!-- Microformats -->
                        <a href="https://microformats.org/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/microformats.jpg" alt="Microformats">
                        </a>
                        <!-- Museum Of Alexandra -->
                        <a href="https://xandra.cc/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/museumofalexandra.jpg" alt="Museum Of Alexandra">
                        </a>
                        <!-- Mutt -->
                        <a href="http://www.mutt.org/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/mutt.jpg" alt="Mutt">
                        </a>
                        <!-- NeoCoties -->
                        <a href="https://neocities.org/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/neocities.jpg" alt="NeoCities">
                        </a>
                        <!-- OhHelloAna -->
                        <a href="https://ohhelloana.blog/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/ohhelloana.jpg" alt="OhHelloAna">
                        </a>
                        <!-- Omg.lol -->
                        <a href="https://home.omg.lol/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/omglol.jpg" alt="omg.lol">
                        </a>
                        <!-- Pixeliana -->
                        <a href="https://pixeliana.neocities.org/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/pixeliana.jpg" alt="Pixeliana">
                        </a>
                        <!-- Ribo Zone -->
                        <a href="https://ribo.zone/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/ribose.jpg" alt="Ribo Zone">
                        </a>
                        <!-- Rob Knight -->
                        <a href="https://rknight.me/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/robbknight.jpg" alt="Robb Knight">
                        </a>
                        <!-- Sara Joy -->
                        <a href="https://sarajoy.dev/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/sarajoy.png" alt="Sara Joy">
                        </a>
                        <!-- Shellsharks -->
                        <a href="https://shellsharks.com/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/shellsharks.jpg" alt="Shellsharks">
                        </a>
                        <!-- Smallweb Subway -->
                        <a href="https://gusbus.space/smallweb-subway/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/smallway.jpg" alt="Smallweb Subway">
                        </a>
                        <!-- Status Cafe -->
                        <a href="https://status.cafe/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/statuscafe.jpg" alt="Status Cafe">
                        </a>
                        <!-- Tracy Durnell -->
                        <a href="https://tracydurnell.com/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/tracydurnell.png" alt="Tracy Durnell">
                        </a>
                        <!-- TransRats -->
                        <a href="https://transrats.neocities.org/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/transrats.jpg" alt="TransRats">
                        </a>
                        <!-- The People Pledge -->
                        <a href="https://people.pledge.party/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/peoplepledge.jpg" alt="The People Pledge">
                        </a>
                        <!-- Vander Wal -->
                        <a href="https://vanderwal.net/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/vdwal.jpg" alt="Vander Wal">
                        </a>
                        <!-- Velvet's Smoking Lounge -->
                        <a href="https://velvet-boutique.neocities.org/index2" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/velvet.jpg" alt="Velvet's Smoking Lounge">
                        </a>
                        <!-- VLC -->
                        <a href="https://www.videolan.org/vlc/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/getvlc.jpg" alt="Get VLC!">
                        </a>
                        <!-- Webmentions -->
                        <a href="https://webmention.io/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/webmentions.jpg" alt="Webmentions">
                        </a>
                        <!-- Wikipedia -->
                        <a href="https://wikipedia.org/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/wikipedia.jpg" alt="Wikipedia">
                        </a>
                        <!-- Wing Pang -->
                        <a href="https://wingpang.com/" rel="noopener">
                            <img loading="lazy" src="/assets/buttons/wingpang.jpg" alt="Wing Pang">
                        </a>
                    
                    </section>

                    <!-- Other Buttons -->
                    <section class="image-group">
                        <img loading="lazy" src="/assets/buttons/88x31.jpg" alt="88x31">
                        <img loading="lazy" src="/assets/buttons/affection.jpg" alt="Made with affection.">
                        <img loading="lazy" src="/assets/buttons/anothernet.jpg" alt="Another Internet Now">
                        <img loading="lazy" src="/assets/buttons/anybrowser.jpg" alt="Best viewed with any browser">
                        <img loading="lazy" src="/assets/buttons/bookmark.jpg" alt="Bookmark this page!">
                        <img loading="lazy" src="/assets/buttons/bdiwm.jpg" alt="Built during an IndieWeb meetup!">
                        <img loading="lazy" src="/assets/buttons/cc0.jpg" alt="cc0">
                        <img loading="lazy" src="/assets/buttons/css.jpg" alt="CSS">
                        <img loading="lazy" src="/assets/buttons/computer.jpg" alt="Get a computer!">
                        <img loading="lazy" src="/assets/buttons/firefox.jpg" alt="Get Firefox">
                        <img loading="lazy" src="/assets/buttons/ffmpeg.jpg" alt="ffmpeg now!">
                        <img loading="lazy" src="/assets/buttons/guestbook.jpg" alt="Guestbook">
                        <img loading="lazy" src="/assets/buttons/html5.jpg" alt="HTML5">
                        <img loading="lazy" src="/assets/buttons/indiemobile.jpg" alt="The IndieWeb is for everyone.">
                        <img loading="lazy" src="/assets/buttons/learnhtml.jpg" alt="HTML - Learn it today!">
                        <img loading="lazy" src="/assets/buttons/madeonlinux.jpg" alt="Made on GNU Linux">
                        <img loading="lazy" src="/assets/buttons/notepad.jpg" alt="Made with NotePad">
                        <img loading="lazy" src="/assets/buttons/mobilefriendly.jpg" alt="Mobile Friendly">
                        <img loading="lazy" src="/assets/buttons/shareware.jpg" alt="Pass the Shareware please!">
                        <img loading="lazy" src="/assets/buttons/pizza.jpg" alt="Pizza Powered">
                        <img loading="lazy" src="/assets/buttons/privacy.jpg" alt="Privacy is a human right">
                        <img loading="lazy" src="/assets/buttons/righttorepair.jpg" alt="Right to repair">
                        <img loading="lazy" src="/assets/buttons/rss.jpg" alt="RSS">
                        <img loading="lazy" src="/assets/buttons/smile.jpg" alt="Smile">
                        <img loading="lazy" src="/assets/buttons/nocookie.jpg" alt="This site is certified 100% cookie free!">
                        <img loading="lazy" src="/assets/buttons/ublockorigin.jpg" alt="UBlock Origin">
                        <img loading="lazy" src="/assets/buttons/validhtml5.jpg" alt="Valid HTML5">
                        <img loading="lazy" src="/assets/buttons/validrss.jpg" alt="Valid RSS">
                        <img loading="lazy" src="/assets/buttons/vscodium.jpg" alt="VSCodium">
                        <img loading="lazy" src="/assets/buttons/www.jpg" alt="World Wide Web: connecting people since 1991">
                        <img loading="lazy" src="/assets/buttons/queer.jpg" alt="You're telling me a queer coded this?">
                    </section>
                </section>
                
                <hr>

                <!-- Directories -->
                <h2 id="directories">Directories</h2>

                <ul>
                    <li><a href="https://indieblog.page/" rel="noopener">Indieblog Page</a></li>
                    <li><a href="https://512kb.club/" rel="noopener">The 512KB Club</a></li>
                    <li><a href="https://theforest.link/" rel="noopener">The Forest</a></li>
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