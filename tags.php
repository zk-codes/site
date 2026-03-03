<?php

// Parse sitemap.xml to build tag index
$sitemap_path = __DIR__ . '/sitemap.xml';
$xml = simplexml_load_file($sitemap_path);
$xml->registerXPathNamespace('zk', 'https://zacharykai.net/sitemap-ext');

$all_tags   = [];
$tag_pages  = [];

foreach ($xml->url as $url) {
    $zk       = $url->children('https://zacharykai.net/sitemap-ext');
    $title    = (string) $zk->title;
    $tags_raw = (string) $zk->tags;
    $loc      = (string) $url->loc;

    if (!$tags_raw) continue;

    $tags = array_map('trim', explode(',', $tags_raw));
    $path = rtrim(parse_url($loc, PHP_URL_PATH), '/') ?: '/';

    foreach ($tags as $tag) {
        if (!$tag) continue;
        if (!isset($tag_pages[$tag])) $tag_pages[$tag] = [];
        $tag_pages[$tag][] = ['title' => $title, 'url' => $path];
        $all_tags[$tag] = ($all_tags[$tag] ?? 0) + 1;
    }
}

ksort($all_tags);
foreach ($tag_pages as $tag => &$pages) {
    usort($pages, fn($a, $b) => strcmp($a['title'], $b['title']));
}
unset($pages);

// Determine current view
$current_tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';
$valid_tag   = $current_tag && isset($tag_pages[$current_tag]);

if ($current_tag && !$valid_tag) {
    header('HTTP/1.1 404 Not Found');
}

?><!DOCTYPE html>
<html lang="en-US">

    <!-- Head -->
    <head>
        <!-- Meta Tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- File Links -->
        <link rel="shortcut icon" href="/assets/icon.ico" type="image/x-icon">
        <link rel="stylesheet" href="/assets/style.css">
        <link rel="alternate" type="application/rss+xml" title="Zachary Kai" href="/assets/rss.xml">
        <link rel="webmention" href="https://webmention.io/zacharykai.net/webmention" />
        <link rel="canonical" href="https://zacharykai.net/tags<?= $valid_tag ? '?tag=' . htmlspecialchars($current_tag) : '' ?>">
        <!-- Page Info -->
        <title><?= $valid_tag ? htmlspecialchars(ucfirst($current_tag)) . ' — ' : '' ?>Tags | Zachary Kai</title>
        <meta name="description" content="<?= $valid_tag ? 'Pages tagged &ldquo;' . htmlspecialchars($current_tag) . '&rdquo; on zacharykai.net.' : 'A browsable index of tags across zacharykai.net.' ?>">
    </head>

    <!-- Body -->
    <body>

        <!-- Skip Link -->
        <p><a href="#top" class="essentials">Begin reading...</a></p>

        <!-- Site Header -->
        <header>
            <nav>
                <a href="/">Zachary Kai</a>
            </nav>
        </header>

        <!-- Main Content -->
        <main>

            <!-- Page Header -->
            <header>
                <p class="essentials"><a href="/">Homepage</a> • <a href="/tags">Tags</a><?= $valid_tag ? ' • ' . htmlspecialchars(ucfirst($current_tag)) : '' ?> ↴</p>
                <h1><?= $valid_tag ? htmlspecialchars(ucfirst($current_tag)) : 'Tags' ?></h1>
            </header>

            <!-- Page Body -->
            <section>

<?php if ($valid_tag): ?>

                <p id="top">All pages tagged <strong><?= htmlspecialchars($current_tag) ?></strong>. <?= count($tag_pages[$current_tag]) ?> <?= count($tag_pages[$current_tag]) === 1 ? 'entry' : 'entries' ?>.</p>

                <ul>
<?php foreach ($tag_pages[$current_tag] as $page): ?>
                    <li><a href="<?= htmlspecialchars($page['url']) ?>"><?= htmlspecialchars($page['title']) ?></a></li>
<?php endforeach; ?>
                </ul>

                <p>•--♡--•</p>


            </section>

            <section>
                <!-- Closing Metadata -->

                <section>
                    <!-- Tags -->
                    <p class="essentials"><strong>Browse Tags</strong>:
<?php
    $tag_links = [];
    foreach ($all_tags as $tag => $count) {
        $tag_links[] = '<a href="/tags?tag=' . urlencode($tag) . '">' . htmlspecialchars($tag) . '</a>';
    }
    echo implode(' · ', $tag_links);
?>
                    </p>
                </section>

                <section>
                    <!-- Copy + Share -->
                    <p><strong>Copy + Share</strong>: <a href="/tags<?= $valid_tag ? '?tag=' . urlencode($current_tag) : '' ?>">zacharykai.net/tags<?= $valid_tag ? '?tag=' . htmlspecialchars($current_tag) : '' ?></a></p>
                </section>

            </section>

<?php else: ?>

            <!-- Page Body -->
            <section>

                <p id="top">Browse pages by tag. Select any tag to see what's filed under it.</p>

                <ul>
<?php foreach ($all_tags as $tag => $count): ?>
                    <li><a href="/tags?tag=<?= urlencode($tag) ?>"><?= htmlspecialchars($tag) ?></a> <span class="essentials">(<?= $count ?>)</span></li>
<?php endforeach; ?>
                </ul>

                <p>•--♡--•</p>


            </section>

            <section>
                <!-- Closing Metadata -->

                <section>
                    <!-- Tags -->
                    <p class="essentials"><strong>Browse Tags</strong>:
<?php
    $tag_links_browse = [];
    foreach ($all_tags as $tag => $count) {
        $tag_links_browse[] = '<a href="/tags?tag=' . urlencode($tag) . '">' . htmlspecialchars($tag) . '</a>';
    }
    echo implode(' · ', $tag_links_browse);
?>
                    </p>
                </section>

                <section>
                    <!-- Copy + Share -->
                    <p><strong>Copy + Share</strong>: <a href="/tags">zacharykai.net/tags</a></p>
                </section>

            </section>

<?php endif; ?>

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
