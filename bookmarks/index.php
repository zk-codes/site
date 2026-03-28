<?php
// Parse YAML front matter from a markdown file
function parse_bookmark($filePath) {
    $content = file_get_contents($filePath);
    if (preg_match('/^---\s*\n(.*?)\n---/s', $content, $matches)) {
        $yaml = $matches[1];
        $bookmark = [];
        foreach (explode("\n", $yaml) as $line) {
            if (preg_match('/^(\w+):\s*"?(.*?)"?\s*$/', $line, $m)) {
                $bookmark[$m[1]] = $m[2];
            }
        }
        // Parse any body content after the front matter
        $body = trim(preg_replace('/^---\s*\n.*?\n---\s*/s', '', $content));
        if (!empty($body)) {
            $bookmark['body'] = $body;
        }
        return $bookmark;
    }
    return null;
}

// Load all bookmarks
$bookmarksDir = __DIR__ . '/';
$files = glob($bookmarksDir . '*.md');
$bookmarks = [];

foreach ($files as $file) {
    $bookmark = parse_bookmark($file);
    if ($bookmark && !empty($bookmark['title']) && !empty($bookmark['url'])) {
        $bookmarks[] = $bookmark;
    }
}

// Determine view mode
$view = isset($_GET['view']) ? $_GET['view'] : 'chronological';

// Sort bookmarks
if ($view === 'category') {
    usort($bookmarks, function($a, $b) {
        $catCmp = strcasecmp($a['category'] ?? '', $b['category'] ?? '');
        if ($catCmp !== 0) return $catCmp;
        $subCmp = strcasecmp($a['subcategory'] ?? '', $b['subcategory'] ?? '');
        if ($subCmp !== 0) return $subCmp;
        return strcasecmp($a['title'], $b['title']);
    });
} else {
    // Reverse chronological
    usort($bookmarks, function($a, $b) {
        return strtotime($b['date'] ?? '1970-01-01') - strtotime($a['date'] ?? '1970-01-01');
    });
}
?>
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
        <link rel="alternate" type="application/rss+xml" title="Zachary Kai" href="/assets/rss.xml">
        <link rel="webmention" href="https://webmention.io/zacharykai.net/webmention" />
        <link rel="canonical" href="https://zacharykai.net/bookmarks">
        <!-- Page Info -->
        <title>Bookmarks | Zachary Kai</title>
        <meta name="date" content="2024-11-15">
        <meta name="last-modified" content="2026-03-22">
        <meta name="description" content="Here you'll find a collection of links I've enjoyed/found fascinating/worth reading/delightful, and their archive link for posterity's sake.">
        <style>
            .bookmark-entry {
                border-bottom: var(--border-style);
                padding: 0.75em 0;
            }
            .bookmark-entry:last-child {
                border-bottom: none;
            }
            .bookmark-meta {
                font-size: 0.78em;
                line-height: 1.4;
            }
            .bookmark-commentary {
                margin-top: 0.35em;
                font-style: italic;
            }
            .bookmark-tags {
                margin-top: 0.25em;
            }
            .bookmark-tag {
                font-size: 0.75em;
                padding: 0.15em 0.45em;
                border: 1px solid var(--main-color);
                border-radius: 3px;
                white-space: nowrap;
            }
            .view-toggle {
                text-align: center;
                margin: 0.5em 0;
            }
            .view-toggle a {
                padding: 0.25em 0.75em;
                border: 1px solid var(--main-color);
                border-radius: 3px;
                text-decoration: none;
                margin: 0 0.25em;
            }
            .view-toggle a.active {
                background-color: var(--main-color);
                color: var(--bg-color);
            }
            @media (prefers-color-scheme: dark) {
                .view-toggle a.active {
                    color: #350046;
                }
            }
        </style>
    </head>

    <!-- Body -->
    <body>

        <!-- Skip Link -->
        <p><a href="#top" class="essentials">Begin reading...</a></p>

        <!-- Site Header -->
        <header><nav><a href="/">Zachary Kai</a></nav></header>

        <!-- Main Content -->
        <main class="h-entry e-content">

            <!-- Page Header -->
            <header>
                <p class="essentials"><a href="/">Homepage</a> • <a href="/sitemap#pages">Pages</a> ↴</p>
                <h1 class="p-name">Bookmarks</h1>
                <p class="essentials">
                    <strong>Written By</strong>: <a href="/about">Zachary Kai</a> »
                    <strong>Published</strong>: <time class="dt-published" datetime="2024-11-15">15 Nov 2024</time> |
                    <strong>Updated</strong>: <time class="dt-modified" datetime="2026-03-22">22 Mar 2026</time>
                </p>
            </header>

            <!-- Page Body -->
            <section class="e-content">

                <section>
                    <!-- Introduction -->
                    <p id="top" class="p-summary">Here you'll find links I've enjoyed/found fascinating. For bookish-specific ones, visit <a href="https://roadlessread.com/links">the page I've compiled on Road Less Read.</a> For more, check out my <a href="/blogroll">blogroll</a> and <a href="/buttons">buttons page</a>.</p>

                    <!-- View Toggle -->
                    <p class="view-toggle">
                        <strong>View</strong>:
                        <a href="/bookmarks?view=chronological"<?php if ($view === 'chronological') echo ' class="active"'; ?>>Chronological</a>
                        <a href="/bookmarks?view=category"<?php if ($view === 'category') echo ' class="active"'; ?>>By Category</a>
                    </p>
                    <p class="essentials" style="text-align: center;"><?php echo count($bookmarks); ?> bookmarks</p>
                </section>

                <hr>

<?php if ($view === 'category'): ?>
                <!-- Category View -->
<?php
    $currentCategory = '';
    $currentSubcategory = '';
    $directoriesNote = false;
    foreach ($bookmarks as $bm):
        $cat = $bm['category'] ?? 'Uncategorized';
        $sub = $bm['subcategory'] ?? '';

        if ($cat !== $currentCategory):
            if ($currentCategory !== '') echo "                </section>\n\n                <hr>\n\n";
            $currentCategory = $cat;
            $currentSubcategory = '';
            $catId = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $cat));
?>
                <!-- <?php echo htmlspecialchars($cat); ?> -->
                <section>
                    <h2 id="<?php echo $catId; ?>"><?php echo htmlspecialchars($cat); ?></h2>
<?php
            if ($cat === 'Directories' && !$directoriesNote):
                $directoriesNote = true;
                echo "                    <p>(They're marked with an asterisk if I've joined them.)</p>\n";
            endif;
        endif;

        if ($sub !== $currentSubcategory && !empty($sub)):
            $currentSubcategory = $sub;
            $subId = $catId . '-' . strtolower(preg_replace('/[^a-z0-9]+/i', '-', $sub));
?>

                    <h3 id="<?php echo $subId; ?>"><?php echo htmlspecialchars($sub); ?></h3>
<?php   endif; ?>

                    <article class="bookmark-entry">
                        <p>
                            <a href="<?php echo htmlspecialchars($bm['url']); ?>" rel="noopener"><?php echo htmlspecialchars($bm['title']); ?></a><?php if (!empty($bm['joined']) && $bm['joined'] === 'true') echo '*'; ?>

                        </p>
<?php if (!empty($bm['commentary'])): ?>
                        <p class="bookmark-commentary"><?php echo htmlspecialchars($bm['commentary']); ?></p>
<?php endif; ?>
                        <p class="bookmark-meta">
                            <time datetime="<?php echo htmlspecialchars($bm['date']); ?>"><?php echo date('j M Y', strtotime($bm['date'])); ?></time>
<?php if (!empty($sub)): ?>
                            · <span class="bookmark-tag"><?php echo htmlspecialchars($cat); ?> / <?php echo htmlspecialchars($sub); ?></span>
<?php else: ?>
                            · <span class="bookmark-tag"><?php echo htmlspecialchars($cat); ?></span>
<?php endif; ?>
                        </p>
                    </article>
<?php endforeach; ?>
                </section>

<?php else: ?>
                <!-- Chronological View -->
                <section>
<?php foreach ($bookmarks as $bm):
        $cat = $bm['category'] ?? '';
        $sub = $bm['subcategory'] ?? '';
?>
                    <article class="bookmark-entry">
                        <p>
                            <a href="<?php echo htmlspecialchars($bm['url']); ?>" rel="noopener"><?php echo htmlspecialchars($bm['title']); ?></a><?php if (!empty($bm['joined']) && $bm['joined'] === 'true') echo '*'; ?>

                        </p>
<?php if (!empty($bm['commentary'])): ?>
                        <p class="bookmark-commentary"><?php echo htmlspecialchars($bm['commentary']); ?></p>
<?php endif; ?>
                        <p class="bookmark-meta">
                            <time datetime="<?php echo htmlspecialchars($bm['date']); ?>"><?php echo date('j M Y', strtotime($bm['date'])); ?></time>
<?php if (!empty($sub)): ?>
                            · <span class="bookmark-tag"><?php echo htmlspecialchars($cat); ?> / <?php echo htmlspecialchars($sub); ?></span>
<?php else: ?>
                            · <span class="bookmark-tag"><?php echo htmlspecialchars($cat); ?></span>
<?php endif; ?>
                        </p>
                    </article>
<?php endforeach; ?>
                </section>

<?php endif; ?>

                <!-- Closing -->
                <p>•--♡--•</p>

            </section>

            <section>
                <!-- Copy + Share -->
                <p><strong>Copy + Share</strong>: <a href="/bookmarks">zacharykai.net/bookmarks</a></p>
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