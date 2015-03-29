<?php


/**
 * AJAX Article/Page Preview
 * -------------------------
 */

Route::accept($config->manager->slug . '/ajax/preview:(article|page)', function($kind = "") use($config, $speak) {
    if( ! $request = Request::post()) exit;
    $P = array('data' => $request);
    $P['kind'] = $kind;
    Weapon::fire('preview_before', array($P, $P));
    $file = Text::toPage(Page::header(array(
        'Title' => trim($request['title']) !== "" ? $request['title'] : $speak->untitled . ' ' . date('Y/m/d H:i:s'),
        'Content Type' => isset($request['content_type']) ? $request['content_type'] : 'HTML'
    ))->content(trim($request['content']) !== "" ? $request['content'] : Config::speak('notify_empty', array(strtolower($speak->contents))))->put());
    Weapon::fire('preview_title_before', array($P, $P));
    echo '<h2 class="preview-title preview-' . $kind . '-title">' . $file['title'] . '</h2>';
    Weapon::fire('preview_title_after', array($P, $P));
    Weapon::fire('preview_content_before', array($P, $P));
    echo '<div class="preview-content preview-' . $kind . '-content p">' . $file['content'] . '</div>';
    Weapon::fire('preview_content_after', array($P, $P));
    echo '</div>';
    Weapon::fire('preview_after', array($P, $P));
    exit;
});


/**
 * AJAX Comment Preview
 * --------------------
 */

Route::accept($config->manager->slug . '/ajax/preview:comment', function() use($config, $speak) {
    if( ! $request = Request::post()) exit;
    $P = array('data' => $request);
    $P['kind'] = 'comment';
    Weapon::fire('preview_before', array($P, $P));
    echo '<div class="preview">';
    $file = Text::toPage(Page::header('Content Type', isset($request['content_type']) ? $request['content_type'] : 'HTML')->content(trim($request['message']) !== "" ? $request['message'] : Config::speak('notify_empty', array(strtolower($speak->contents))))->put(), 'message', 'comment:');
    Weapon::fire('preview_content_before', array($P, $P));
    echo '<div class="preview-content preview-comment-content p">' . $file['message'] . '</div>';
    Weapon::fire('preview_content_after', array($P, $P));
    echo '</div>';
    Weapon::fire('preview_after', array($P, $P));
    exit;
});