<?php

// Site Index
$pages = [];

// Path To Sitemap XML File
$sitemapPath = $_SERVER['DOCUMENT_ROOT'] . '/sitemap.xml';

// Check If Sitemap File Exists
if (file_exists($sitemapPath)) {
    $dom = new DOMDocument();

    // Error Handling
    if ($dom->load($sitemapPath)) {
        $xpath = new DOMXPath($dom);

        // Register the custom namespace for Zachary Kai's sitemap extensions
        $xpath->registerNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $xpath->registerNamespace('zk', 'https://zacharykai.net/sitemap-ext');

        // Query all 'url' elements
        $urlNodes = $xpath->query('//s:url');

        foreach ($urlNodes as $urlNode) {
            $url = '';
            $title = '';
            $content = '';

            // Get the URL (loc)
            $locNode = $xpath->query('s:loc', $urlNode)->item(0);
            if ($locNode) {
                $rawUrl = trim($locNode->nodeValue);
                // Ensure the URL starts with / relative to the domain
                $url = str_replace('https://zacharykai.net', '', $rawUrl);

                // Handle the root URL specifically to ensure it's just '/'
                if (empty($url)) { // If str_replace results in empty, it was the base domain
                    $url = '/';
                } elseif (substr($url, -1) === '/' && $url !== '/') {
                    // Remove trailing slash for non-root directories like /zines/ -> /zines
                    $url = rtrim($url, '/');
                }
            }

            // Get the custom title (zk:title)
            $titleNode = $xpath->query('zk:title', $urlNode)->item(0);
            if ($titleNode) {
                $title = trim($titleNode->nodeValue);
            } else {
                // Fallback: derive title from URL if zk:title is not present
                if (!empty($url)) {
                    $cleanedUrl = ltrim($url, '/'); // Remove leading slash
                    $cleanedUrl = preg_replace('/\.(php|html|htm)$/i', '', $cleanedUrl); // Remove file extensions
                    $parts = explode('/', $cleanedUrl);
                    $lastPart = end($parts);
                    if (empty($lastPart) || $lastPart === 'index') {
                        // Handle cases like /zines/ or /zines/index
                        if (count($parts) > 1) {
                            $title = str_replace('-', ' ', prev($parts)); // Get parent folder name
                        } else {
                            $title = 'Homepage'; // Or whatever you prefer for the root
                        }
                    } else {
                        $title = str_replace('-', ' ', $lastPart);
                    }
                    $title = ucwords($title); // Capitalize first letter of each word
                    if ($title === '') $title = 'Homepage'; // Ensure homepage has a title
                }
            }

            // Get the custom content (zk:content)
            $contentNode = $xpath->query('zk:content', $urlNode)->item(0);
            if ($contentNode) {
                $content = trim($contentNode->nodeValue);
            }

            // Only add if URL and title are available and it's not the search page itself
            if (!empty($url) && !empty($title) && $url !== '/search') {
                $pages[] = [
                    'title' => $title,
                    'url' => $url,
                    'content' => $content
                ];
            }
        }
    } else {
        // Error loading sitemap
        error_log("Failed to load sitemap.xml from $sitemapPath");
    }
} else {
    // Sitemap file not found
    error_log("Sitemap.xml not found at $sitemapPath");
}

$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if ($searchTerm !== '') {
    foreach ($pages as $page) {
        // Check if the search term exists in the title or content.
        if (stripos($page['title'], $searchTerm) !== false || stripos(strip_tags($page['content']), $searchTerm) !== false) {
            $results[] = $page;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
        <title>Search | Zachary Kai</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="/assets/icon.ico" type="image/x-icon">
        <link rel="stylesheet" href="/assets/style.css">
        <link rel="stylesheet" href="/assets/print.css" media="print">
        <link href="/assets/manifest.json" rel="manifest"/>
        <link rel="alternate" type="application/rss+xml" title="Zachary Kai" href="/assets/rss.xml">
        <link rel="webmention" href="https://webmention.io/zacharykai.net/webmention" />
        <link rel="canonical" href="https://zacharykai.net/search">
        <meta name="date" content="2025-02-17">
        <meta name="last-modified" content="2025-04-14">
        <meta name="description" content="Enter your search term, and I'll see if I can find what you're looking for!">
    </head>
    <body>
        <p><a href="#top" class="essentials">Begin reading...</a></p>
        <header><nav><a href="/">Zachary Kai</a></nav></header>
        <main class="h-entry e-content">
            <header>
                <p class="breadcrumbs"><a href="/">Homepage</a> • <a href="/sitemap#pages">Pages</a></p>
                <h1 class="p-name">Search The Site</h1>
                <p class="postmeta">
                    <strong>Published</strong>: <time class="dt-published" datetime="2025-02-17">17 Feb 2025</time> | 
                    <strong>Updated</strong>: <time class="dt-modified" datetime="2025-04-14">14 Apr 2025</time>
                </p>
            </header>
            <section class="e-content">

            </section>
            <p id="top" class="p-summary">Use this form to enter your search term, and I'll see if I can find what you're looking for!</p>
            <form action="search.php" method="get">
                <label for="q">Type in what you're looking for below:</label>
                <br>
                <input type="text" id="q" name="q" value="<?php echo htmlspecialchars($searchTerm); ?>" required>
                <br>
                <input class="button" type="submit" value="Search!">
            </form>
            <?php if ($searchTerm !== ''): ?>
                <h2>Here's What I've Found For "<?php echo htmlspecialchars($searchTerm); ?>"</h2>
                <?php if (!empty($results)): ?>
                    <?php foreach ($results as $result): ?>
                        <div class="result">
                            <h3><a href="<?php echo htmlspecialchars($result['url']); ?>"><?php echo htmlspecialchars($result['title']); ?></a></h3>
                            <p><?php echo htmlspecialchars($result['content']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <p>No results found.</p>
                <?php endif; ?>
            <?php endif; ?>
            <p>•--♡--•</p>
            <section class="essentials">
                <p><strong>Copy & Share</strong>: <a href="/search" class="u-url">zacharykai.net/search</a></p>
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
                    <a href="/contact" rel="noopener">Suggest An Edit</a> |
                    <a href="/source" rel="noopener">View Source Code</a>
                </p>
            </section>
        </main>
        <section class="h-card vcard">
            <section class="h-card-image">
                <picture>
                    <source srcset="/assets/zk_icon.webp" type="image/webp">
                    <img class="u-photo" loading="lazy" src="/assets/zk_icon.png" alt="Zachary Kai's digital drawing: 5 stacked books (blue/teal/green/purple, black spine designs), green plant behind top book, purple heart on either side.">
                </picture>
            </section>
            <section class="h-card-content">
                <p><strong><a class="u-url u-id p-name" href="https://zacharykai.net" rel="me"><span class="fn">Zachary Kai</span></a></strong> — <span class="p-pronouns">he/him</span> | <a class="u-email email" href="mailto:hi@zacharykai.net" rel="me">hi@zacharykai.net</a></p>
                <p class="p-note">Zachary Kai is a space fantasy writer, offbeat queer, traveler, zinester, and avowed generalist.</p>
            </section>
        </section>
        <section class="acknowledgement">
            <h2>Acknowledgement Of Country</h2>
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