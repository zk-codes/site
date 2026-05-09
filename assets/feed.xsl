<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:content="http://purl.org/rss/1.0/modules/content/">
<xsl:output method="html" encoding="UTF-8" indent="yes"/>

<xsl:template match="/">
<html lang="en-US">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title><xsl:value-of select="/rss/channel/title"/> — RSS Feed</title>
    <style>
        :root {
            --main-color: #503832;
            --bg-color: #f6e6cf;
            --highlight-color: #ffd6a5;
            --border-style: 0.15em #503832 double;
            --font-family: "Palatino Linotype", serif;
        }

        body {
            background-color: var(--bg-color);
            font-family: var(--font-family);
            color: var(--main-color);
            font-size: 1.28em;
            line-height: 1.5;
            max-width: 900px;
            margin: auto;
            padding: 0.15em;
            margin-bottom: 1em;
        }

        h1, h2, h3 {
            font-weight: 600;
            line-height: 1;
        }

        h1 { font-size: 2.1em; margin-top: 0.55em; }
        h2 { font-size: 1.8em; }
        h3 { font-size: 1.5em; }

        a {
            color: inherit;
        }

        header, main, footer {
            border: var(--border-style);
            padding: 0.75em;
            margin-top: 0.25em;
        }

        .feed-banner {
            background-color: var(--highlight-color);
            border: var(--border-style);
            padding: 0.75em;
            margin-bottom: 0.5em;
        }

        .feed-banner p {
            margin: 0.25em 0;
        }

        article {
            border-bottom: var(--border-style);
            padding: 0.75em 0;
            margin-bottom: 0.5em;
        }

        article:last-child {
            border-bottom: none;
        }

        .meta {
            font-size: 0.85em;
        }

        footer p {
            margin: 0.25em 0;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --main-color: #d4c5b0;
                --bg-color: #1a1a1a;
                --highlight-color: #8b7355;
                --border-style: 0.15em #d4c5b0 double;
            }
        }

        @media (max-width: 768px) {
            body { font-size: 1.1em; padding: 0.5em; }
            h1 { font-size: 1.7em; }
        }
    </style>
</head>
<body>

    <header>
        <nav><a href="/">Zachary Kai</a></nav>
    </header>

    <main>
        <div class="feed-banner">
            <p><strong>You're viewing an RSS feed.</strong> This means you can subscribe to receive updates. Copy the URL from your address bar into your <a href="/subscribe">RSS reader</a> of choice.</p>
        </div>

        <h1><xsl:value-of select="/rss/channel/title"/></h1>
        <p><xsl:value-of select="/rss/channel/description"/></p>
        <p class="meta">
            <strong>Last Updated</strong>: <xsl:value-of select="/rss/channel/lastBuildDate"/> |
            <a href="/subscribe">All Feeds</a> |
            <a href="/assets/rss.xml">Main Feed</a>
        </p>

        <h2>Recent Items</h2>

        <xsl:for-each select="/rss/channel/item">
            <article>
                <h3>
                    <a>
                        <xsl:attribute name="href"><xsl:value-of select="link"/></xsl:attribute>
                        <xsl:value-of select="title"/>
                    </a>
                </h3>
                <p class="meta"><xsl:value-of select="pubDate"/></p>
                <p><xsl:value-of select="description"/></p>
            </article>
        </xsl:for-each>
    </main>

    <footer>
        <p><strong>Est. 2024</strong> || <a href="/about">About</a> | <a href="/subscribe">Subscribe</a> | <a href="/sitemap">Sitemap</a></p>
    </footer>

</body>
</html>
</xsl:template>
</xsl:stylesheet>
