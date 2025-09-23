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
        <link rel="canonical" href="https://zacharykai.net/search">
        <!-- Page Info -->
        <title>Search | Zachary Kai</title>
        <meta name="date" content="2025-02-17">
        <meta name="last-modified" content="2025-04-14">
        <meta name="description" content="Enter your search term, and I'll see if I can find what you're looking for!">
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
                <p class="essentials"><a href="/">Homepage</a> • <a href="/sitemap#pages">Pages</a></p>
                <h1 class="p-name">Search The Site</h1>
                <p class="essentials">
                    <strong>Published</strong>: <time class="dt-published" datetime="2025-02-17">17 Feb 2025</time> | 
                    <strong>Updated</strong>: <time class="dt-modified" datetime="2025-04-14">14 Apr 2025</time>
                </p>
            </header>

            <!-- Page Body -->
            <section class="e-content">

                <!-- Introduction -->
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