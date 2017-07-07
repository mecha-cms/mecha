<?php

function fn_art_css($content) {
    $content = trim($content);
    if ($content && stripos($content, '</style>') === false && stripos($content, '<link ') === false) {
        return '<style media="screen">' . N . $content . N . '</style>';
    }
    return $content;
}

function fn_art_js($content) {
    $content = trim($content);
    if ($content && stripos($content, '</script>') === false && stripos($content, '<script ') === false) {
        return '<script>' . N . $content . N . '</script>';
    }
    return $content;
}

function fn_art_replace($content) {
    if (!$page = Lot::get('page')) {
        return $content;
    }
    // Append custom CSS before `</head>`…
    $content = str_ireplace('</head>', $page->css . '</head>', $content);
    // Append custom JS before `</body>`…
    $content = str_ireplace('</body>', $page->js . '</body>', $content);
    return $content;
}

Hook::set('page.css', 'fn_art_css');
Hook::set('page.js', 'fn_art_js');
Hook::set('shield.output', 'fn_art_replace', 1);