<?php


/**
 * AJAX Article/Page Preview
 * -------------------------
 */

Route::post($config->manager->slug . '/ajax/preview:(article|page)', function($kind = "") use($config, $speak) {
    $P = array('data' => Request::post());
    $P['kind'] = $kind;
    Weapon::fire('preview_before', array($P, $P));
    $page = Page::header(array(
        'Title' => Request::post('title', $speak->untitled . ' ' . date('Y/m/d H:i:s')),
        'Content Type' => Request::post('content_type', 'HTML')
    ))->content(Request::post('content', Config::speak('notify_empty', strtolower($speak->contents))))->read();
    Weapon::fire('preview_title_before', array($P, $P));
    echo '<h2 class="preview-title preview-' . $kind . '-title">' . $page['title'] . '</h2>';
    Weapon::fire('preview_title_after', array($P, $P));
    Weapon::fire('preview_content_before', array($P, $P));
    echo '<div class="preview-content preview-' . $kind . '-content p">' . $page['content'] . '</div>';
    Weapon::fire('preview_content_after', array($P, $P));
    echo '</div>';
    Weapon::fire('preview_after', array($P, $P));
    exit;
});


/**
 * AJAX Comment Preview
 * --------------------
 */

Route::post($config->manager->slug . '/ajax/preview:comment', function() use($config, $speak) {
    $P = array('data' => Request::post());
    $P['kind'] = 'comment';
    Weapon::fire('preview_before', array($P, $P));
    echo '<div class="preview">';
    $comment = Page::header('Content Type', Request::post('content_type', 'HTML'))->content(Request::post('message', Config::speak('notify_empty', strtolower($speak->contents))))->read('message', 'comment:');
    Weapon::fire('preview_content_before', array($P, $P));
    echo '<div class="preview-content preview-comment-content p">' . $comment['message'] . '</div>';
    Weapon::fire('preview_content_after', array($P, $P));
    echo '</div>';
    Weapon::fire('preview_after', array($P, $P));
    exit;
});