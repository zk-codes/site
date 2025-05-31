<?php

$base_url = 'https://zacharykai.net';

// Page Index
$pages = [
    '/about',
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
    '/projects',
    '/recipes',
    '/resume',
    '/save',
    '/search',
    '/sitemap',
    '/trades',
    '/unoffice-hours',
    '/uses',
    '/wants',
    '/webrings',
    '/words',
    '/workshops',
    '/zines',
    '/gifts/',
    '/fanfic/aes',
    '/lists/fanfiction',
    '/lists/oceania',
    '/lists/queer',
    '/newsletter/',
    '/newsletter/jan25',
    '/newsletter/feb25',
    '/notes/24list',
    '/notes/25goals',
    '/notes/agwdec24',
    '/notes/aw824',
    '/notes/blogqna25',
    '/notes/colbert',
    '/notes/icfeb25',
    '/notes/icmar25',
    '/notes/iwmapr25',
    '/notes/lf24',
    '/notes/musicqna',
    '/notes/mwf24',
    '/notes/mwm',
    '/notes/mzf24',
    '/notes/pp',
    '/notes/proust',
    '/notes/rdec24',
    '/notes/site-ideas',
    '/notes/sparks',
    '/notes/waiting',
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