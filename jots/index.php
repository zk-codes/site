<!DOCTYPE html>
<html lang="en-US">

    <!-- Head -->
    <head>
        <!-- Meta Tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- File Links -->
        <link rel="shortcut icon" href="/assets/icon.ico" type="image/x-icon">
        <link rel="stylesheet" href="/assets/styles/jots.css">
        <link rel="alternate" type="application/rss+xml" title="Zachary Kai" href="/assets/rss.xml">
        <link rel="webmention" href="https://webmention.io/zacharykai.net/webmention" />
        <link rel="canonical" href="https://zacharykai.net/jots">
        <!-- Page Info -->
        <title>Jots | Zachary Kai</title>
        <meta name="date" content="2024-12-03">
        <meta name="last-modified" content="<?php echo date('Y-m-d'); ?>">
        <meta name="description" content="Herein you'll find my 'microblog': snippets to short for a dedicated place on my site, but still worth sharing, for your interest, and for my records.">
    </head>

    <!-- Body -->
    <body>

        <!-- Skip Link -->
        <p><a href="#top" class="essentials">Begin reading...</a></p>

        <!-- Site Header -->
        <header><nav><a href="/">Zachary Kai</a></nav></header>

        <!-- Main Content -->
        <main class="h-feed">
            <a class="u-url" href="https://zacharykai.net/jots"></a>
            <section class="p-author h-card" style="display: none;">
                <a class="u-url" href="https://zacharykai.net" rel="me">Zachary Kai</a>
            </section>

            <!-- Page Header -->
            <header>
                <!-- Breadcrumbs -->
                <p class="essentials"><a href="/">Homepage</a> • <a href="/sitemap#pages">Pages</a> ↴</p>
                <!-- Page Title -->
                <h1 class="p-name">Jots</h1>
                <!-- Necessary Metadata -->
                <p class="essentials">
                    <strong>Written By</strong>: <a href="/about">Zachary Kai</a> »
                    <strong>Published</strong>: <time class="dt-published" datetime="2024-12-03">3 Dec 2024</time> |
                    <strong>Updated</strong>: <time class="dt-modified" datetime="<?php echo date('Y-m-d'); ?>"><?php echo date('j M Y'); ?></time>
                </p>
                <!-- Other Metadata -->
                <details class="essentials">
                    <summary>Expand For Other (Hopefully Useful) Metadata</summary>
                    <ul>
                        <li>Reading Time: ~1 min (at 238 WPM)
                        <li>Word Count: 6</li>
                    </ul>
                </details>
                <!-- Introduction -->
                <?php
                // Read and parse all jot markdown files
                $jotsDir = __DIR__ . '/';
                $files = glob($jotsDir . '*.md');
                $posts = [];
                $allTags = [];

                foreach ($files as $file) {
                    $raw = file_get_contents($file);

                    // Parse YAML frontmatter
                    if (preg_match('/\A---\s*\n(.+?)\n---\s*\n(.*)\z/s', $raw, $matches)) {
                        $frontmatter = $matches[1];
                        $body = trim($matches[2]);

                        $date = '';
                        $id = '';
                        $tags = [];

                        if (preg_match('/^date:\s*(.+)$/m', $frontmatter, $m)) {
                            $date = trim($m[1]);
                        }
                        if (preg_match('/^id:\s*(.+)$/m', $frontmatter, $m)) {
                            $id = trim($m[1]);
                        }
                        if (preg_match('/^tags:\s*(.+)$/m', $frontmatter, $m)) {
                            $tags = array_map('trim', explode(',', $m[1]));
                            foreach ($tags as $t) {
                                $allTags[$t] = ($allTags[$t] ?? 0) + 1;
                            }
                        }

                        if (!empty($date) && !empty($id)) {
                            $posts[] = [
                                'date' => $date,
                                'id' => $id,
                                'body' => $body,
                                'tags' => $tags,
                            ];
                        }
                    }
                }

                // Sort by date descending
                usort($posts, function ($a, $b) {
                    return strtotime($b['date']) - strtotime($a['date']);
                });

                $totalPosts = count($posts);
                arsort($allTags);
                ?>
                <p id="top" class="p-summary">Here you'll find my 'microblog': <?php echo $totalPosts; ?> snippets too short for a dedicated place on my site, but still worth sharing, for your interest, and for my records. (Ordered reverse chronologically.)</p>

                <!-- Tag Filter -->
                <nav class="tag-filter" aria-label="Filter by tag">
                    <a href="/jots" class="tag-pill<?php echo empty($_GET['tag']) ? ' active' : ''; ?>">All</a>
                    <?php foreach ($allTags as $tag => $count): ?>
                    <a href="/jots?tag=<?php echo urlencode($tag); ?>" class="tag-pill<?php echo (($_GET['tag'] ?? '') === $tag) ? ' active' : ''; ?>"><?php echo htmlspecialchars($tag); ?></a>
                    <?php endforeach; ?>
                </nav>
            </header>

            <!-- Page Body -->
            <section class="e-content">

                <section class="jots-grid">
                <?php
                // Filter by tag if set
                $activeTag = $_GET['tag'] ?? '';
                if (!empty($activeTag)) {
                    $posts = array_filter($posts, function ($post) use ($activeTag) {
                        return in_array($activeTag, $post['tags']);
                    });
                    $posts = array_values($posts);
                    $totalPosts = count($posts);
                }

                // Pagination
                $perPage = 50;
                $totalPages = max(1, (int) ceil($totalPosts / $perPage));
                $currentPage = max(1, min($totalPages, (int) ($_GET['page'] ?? 1)));
                $offset = ($currentPage - 1) * $perPage;
                $pagePosts = array_slice($posts, $offset, $perPage);

                // Group by year
                $currentYear = '';

                foreach ($pagePosts as $i => $post) {
                    $year = date('Y', strtotime($post['date']));
                    $displayDate = date('j M Y', strtotime($post['date']));

                    // Year heading
                    if ($year !== $currentYear) {
                        $currentYear = $year;
                        echo "\n                <!-- $year -->\n";
                        echo "                <h2 id=\"$year\">$year</h2>\n\n";
                    }

                    // Article
                    echo "                <article class=\"h-entry\" id=\"" . htmlspecialchars($post['id']) . "\">\n";
                    echo "                    <p class=\"jot-date\"><time class=\"dt-published\" datetime=\"" . htmlspecialchars($post['date']) . "\">$displayDate</time></p>\n";
                    echo "                    <section class=\"e-content p-name\">\n";
                    echo "                        " . $post['body'] . "\n";
                    echo "                    </section>\n";

                    // Tags
                    if (!empty($post['tags'])) {
                        echo "                    <p class=\"jot-tags\">";
                        foreach ($post['tags'] as $j => $tag) {
                            if ($j > 0) echo ' ';
                            echo '<a href="/jots?tag=' . urlencode($tag) . '" class="tag-pill small">' . htmlspecialchars($tag) . '</a>';
                        }
                        echo "</p>\n";
                    }

                    echo "                    <a href=\"/jots/" . htmlspecialchars($post['id']) . "\" class=\"u-url\" title=\"Permalink\">&rarr;</a>\n";
                    echo "                </article>\n";
                }
                ?>
                </section>

                <?php if ($totalPages > 1): ?>
                <nav class="pagination essentials">
                    <?php if ($currentPage > 1): ?>
                        <a href="/jots?page=<?php echo $currentPage - 1; ?><?php echo !empty($activeTag) ? '&tag=' . urlencode($activeTag) : ''; ?>">← Newer</a>
                    <?php endif; ?>
                    <span>Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?></span>
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="/jots?page=<?php echo $currentPage + 1; ?><?php echo !empty($activeTag) ? '&tag=' . urlencode($activeTag) : ''; ?>">Older →</a>
                    <?php endif; ?>
                </nav>
                <?php endif; ?>

            </section>

            <section>
                <!-- Closing Metadata -->

                <section>
                    <!-- Tags -->
                    <p class="essentials"><strong>Tags</strong>: <a href="/tags?tag=personal">personal</a> · <a href="/tags?tag=indieweb">indieweb</a></p>
                </section>

                <section>
                    <!-- Copy + Share -->
                    <p><strong>Copy + Share</strong>: <a href="/jots">zacharykai.net/jots</a></p>
                </section>

            </section>

        </main>

        <!-- Back To Top Link -->
        <p><a href="#top" class="essentials">Read again...</a></p>

        <section class="support">
            <h2>Enjoy What I Do? Find It (Hopefully) Helpful?</h2>
            <p>I'm so glad! If you feel moved to <a href="/support">support me</a> in making things, I'd most appreciate it!</p>
        </section>

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
                <a href="/subscribe">Subscribe</a> |
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
                <a href="https://lunaseeker.com/portfolio" rel="noopener">Portfolio</a>
            </p>
        </footer>

    </body>
</html>
