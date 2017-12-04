<?php

function fn_page_css($content) {
    $content = trim($content);
    if ($content && strpos($content, '</style>') === false && strpos($content, '<link ') === false) {
        return '<style media="screen">' . N . $content . N . '</style>';
    }
    return $content;
}

function fn_page_js($content) {
    $content = trim($content);
    if ($content && strpos($content, '</script>') === false && strpos($content, '<script ') === false) {
        return '<script>' . N . $content . N . '</script>';
    }
    return $content;
}

function fn_art($content) {
    if (!$page = Lot::get('page')) {
        return $content;
    }
    // Append custom CSS before `</head>`…
    $content = str_replace('</head>', $page->css . '</head>', $content);
    // Append custom JS before `</body>`…
    $content = str_replace('</body>', $page->js . '</body>', $content);
    return $content;
}

Hook::set('page.css', 'fn_page_css', 2);
Hook::set('page.js', 'fn_page_js', 2);
Hook::set('shield.output', 'fn_art', 1);