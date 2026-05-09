<?php
$jotsDir = __DIR__ . '/';
$id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['id'] ?? '');

if (empty($id)) {
    header('Location: /jots');
    exit;
}

$file = $jotsDir . $id . '.md';

if (!file_exists($file)) {
    header('HTTP/1.0 404 Not Found');
    $notFound = true;
    $post = null;
} else {
    $notFound = false;
    $raw = file_get_contents($file);
    $post = null;

    if (preg_match('/\A---\s*\n(.+?)\n---\s*\n(.*)\z/s', $raw, $matches)) {
        $frontmatter = $matches[1];
        $body = trim($matches[2]);
        $date = '';

        if (preg_match('/^date:\s*(.+)$/m', $frontmatter, $m)) {
            $date = trim($m[1]);
        }

        if (!empty($date)) {
            $tags = [];
        if (preg_match('/^tags:\s*(.+)$/m', $frontmatter, $mt)) {
            $tags = array_map('trim', explode(',', $mt[1]));
        }

        $post = [
                'date' => $date,
                'id' => $id,
                'body' => $body,
                'tags' => $tags,
            ];
        }
    }

    if (!$post) {
        header('HTTP/1.0 404 Not Found');
        $notFound = true;
    }
}

$displayDate = $post ? date('j M Y', strtotime($post['date'])) : '';
$pageTitle = $post ? 'Jot: ' . $displayDate . ' | Zachary Kai' : '404 Not Found | Zachary Kai';
$canonicalUrl = $post ? 'https://zacharykai.net/jots/' . htmlspecialchars($id) : '';
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
        <?php if ($canonicalUrl): ?>
        <link rel="canonical" href="<?php echo $canonicalUrl; ?>">
        <?php endif; ?>
        <!-- Page Info -->
        <title><?php echo htmlspecialchars($pageTitle); ?></title>
        <?php if ($post): ?>
        <meta name="date" content="<?php echo htmlspecialchars($post['date']); ?>">
        <meta name="description" content="A jot by Zachary Kai, written <?php echo htmlspecialchars($displayDate); ?>.">
        <?php endif; ?>
    </head>

    <!-- Body -->
    <body>

        <!-- Skip Link -->
        <p><a href="#top" class="essentials">Begin reading...</a></p>

        <!-- Site Header -->
        <header><nav><a href="/">Zachary Kai</a></nav></header>

        <!-- Main Content -->
        <main>

            <!-- Page Header -->
            <header>
                <!-- Breadcrumbs -->
                <p class="essentials"><a href="/">Homepage</a> • <a href="/jots">Jots</a> ↴</p>
                <!-- Page Title -->
                <h1 id="top"><?php echo $post ? htmlspecialchars($displayDate) : 'Jot'; ?></h1>
                <?php if ($post): ?>
                <p class="essentials">
                    <strong>Written By</strong>: <a href="/about">Zachary Kai</a> »
                    <strong>Published</strong>: <time class="dt-published" datetime="<?php echo htmlspecialchars($post['date']); ?>"><?php echo $displayDate; ?></time>
                </p>
                <?php endif; ?>
            </header>

            <!-- Page Body -->
            <section class="e-content">

                <?php if ($notFound): ?>
                <p>That jot couldn't be found. <a href="/jots">Back to all jots.</a></p>
                <?php else: ?>
                <article class="h-entry">
                    <section class="e-content p-name">
                        <?php echo $post['body']; ?>
                        <p>•--♡--•</p>
                    </section>
                    <p class="essentials">
                        <a href="/jots">← Back to Jots</a>
                    </p>
                </article>
                <?php endif; ?>

            </section>

            <section>
                <!-- Closing Metadata -->

                <section>
                    <!-- Tags -->
                    <?php if ($post && !empty($post['tags'])): ?>
                    <p class="essentials"><strong>Tags</strong>: <?php echo implode(' · ', array_map(fn($t) => '<a href="/tags?tag=' . urlencode($t) . '">' . htmlspecialchars($t) . '</a>', $post['tags'])); ?></p>
                    <?php else: ?>
                    <p class="essentials"><strong>Tags</strong>: <a href="/tags?tag=personal">personal</a></p>
                    <?php endif; ?>
                </section>

                <section>
                    <!-- Copy + Share -->
                    <?php if ($post): ?>
                    <p><strong>Copy + Share</strong>: <a href="/jots/<?php echo htmlspecialchars($id); ?>" class="u-url">zacharykai.net/jots/<?php echo htmlspecialchars($id); ?></a></p>
                    <?php endif; ?>
                </section>

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
