<?php

Hook::set('page.description', function($content) {
    // Wrap description data with paragraph tag(s) if needed
    if ($content && strpos($content, '</p>') === false) {
        return '<p>' . str_replace(["\n\n", "\n"], ['</p><p>', '<br>'], trim(n($content))) . '</p>';
    }
    return $content;
});

// Add CSS file to the `<head>` section…
Asset::set('css/document.min.css');

// Add JS file to the `<body>` section…
Asset::set('js/document.min.js');