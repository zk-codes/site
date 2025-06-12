<?php

$base_url = 'https://zacharykai.net';

// Page Index
$pages = [
    '/about',
    '/art',
    '/blogroll',
    '/bookmarklets',
    '/bookmarks',
    '/books',
    '/changelog',
    '/colophon',
    '/cv',
    '/defaults',
    '/faq',
    '/guestbook',
    '/hello',
    '/ideas',
    '/interests',
    '/mentions',
    '/music',
    '/now',
    '/photos',
    '/press',
    '/recipes',
    '/search',
    '/sitemap',
    '/trades',
    '/unoffice-hours',
    '/uses',
    '/webrings',
    '/workshops',
    '/lists/fanfiction',
    '/lists/oceania',
    '/lists/queer',
    '/newsletter/',
    '/newsletter/jan25',
    '/newsletter/feb25',
    '/notes/24list',
    '/notes/25goals',
    '/notes/blogqna25',
    '/notes/colbert',
    '/notes/icmar25',
    '/notes/iwmapr25',
    '/notes/lf24',
    '/notes/musicqna',
    '/notes/mwf24',
    '/notes/mwm',
    '/notes/pp',
    '/notes/proust',
    '/notes/rdec24',
    '/notes/site-ideas',
    '/notes/sparks',
    '/notes/tooeasily',
    '/notes/waiting',
    '/stories/aes',
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
    '/stories/zenith',
    '/zines/',
    '/zines/bb',
    '/zines/de',
    'zines/dtaoya',
    '/zines/ff',
    '/zines/gg',
    '/zines/im',
    '/zines/lfl',
    '/zines/nn',
    '/zines/nwn',
    '/zines/ptcl',
    '/zines/rr',
    '/zines/ss',
    '/zines/vv',
    '/zines/yy'
];

// Select Random Page
$random_page = $pages[array_rand($pages)];

// Construct Full URL
$redirect_url = $base_url . $random_page;

// Perform Redirect
header("Location: $redirect_url");
exit;

?>