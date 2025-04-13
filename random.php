<?php

$base_url = 'https://zacharykai.net';

// Page Index
$pages = [
    '/about',
    '/avatars',
    '/blogroll',
    '/bookmarklets',
    '/bookmarks',
    '/books',
    '/changelog',
    '/collages',
    '/colophon',
    '/contact',
    '/cv',
    '/defaults',
    '/digital-art',
    '/drawings',
    '/faq',
    '/gods-eyes',
    '/guestbook',
    '/hello',
    '/ideas',
    '/interests',
    '/mentions',
    '/music',
    '/now',
    '/quotes',
    '/photos',
    '/press',
    '/principles',
    '/recipes',
    '/resume',
    '/save',
    '/search',
    '/sitemap',
    '/spotify',
    '/statistics',
    '/trades',
    '/uses',
    '/wants',
    '/webmentions',
    '/webrings',
    '/words',
    '/workshops',
    '/zines',
    '/gifts/rdec24',
    '/newsletter/',
    '/newsletter/jan25',
    '/notes/',
    '/notes/24list',
    '/notes/25goals',
    '/notes/agwdec24',
    '/notes/aw824',
    '/notes/blogqna25',
    '/notes/icfeb25',
    '/notes/icmar25',
    '/notes/iwmapr25',
    '/notes/lf24',
    '/notes/mwf24',
    '/notes/mwm',
    '/notes/mzf24',
    '/notes/pp',
    '/notes/proust',
    '/notes/site-ideas',
    '/notes/sparks',
    '/stories/',
    '/stories/break',
    '/stories/gate',
    '/stories/options',
    '/stories/poetry',
    '/stories/pull',
    '/stories/seed',
    '/stories/truth',
    '/stories/undoing',
    '/stories/why',
    '/stories/wild',
    '/stories/wired',
    '/stories/worlds',
    '/stories/zenith'
];

// Select Random Page
$random_page = $pages[array_rand($pages)];

// Construct Full URL
$redirect_url = $base_url . $random_page;

// Perform Redirect
header("Location: $redirect_url");
exit;
?>