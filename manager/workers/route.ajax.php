<?php


/**
 * AJAX Comment Preview
 * --------------------
 */

Route::accept($config->manager->slug . '/ajax/preview:(comment)', function($kind = "") use($config, $speak) {
    $P = array('data' => Request::post());
    $P['kind'] = $kind;
    Weapon::fire('preview_before', array($P, $P));
    echo '<div class="preview">';
    if($request = Request::post()) {
        $file  = 'Content Type: ' . (isset($request['content_type']) ? $request['content_type'] : 'HTML') . "\n";
        $file .= "\n" . SEPARATOR . "\n";
        $file .= "\n" . (trim($request['message']) !== "" ? $request['message'] : Config::speak('notify_empty', array(strtolower($speak->messages))));
        $file = Text::toPage($file, true, 'comment:', 'message');
        Weapon::fire('preview_content_before', array($P, $P));
        echo '<div class="preview-content preview-' . $kind . '-content p">' . $file['message'] . '</div>';
        Weapon::fire('preview_content_after', array($P, $P));
    }
    echo '</div>';
    Weapon::fire('preview_after', array($P, $P));
    exit;
});


/**
 * AJAX Preview
 * ------------
 */

Route::accept($config->manager->slug . '/ajax/preview:(:any)', function($kind = "") use($config, $speak) {
    $P = array('data' => Request::post());
    $P['kind'] = $kind;
    Weapon::fire('preview_before', array($P, $P));
    echo '<div class="preview">';
    if($request = Request::post()) {
        $file = 'Title: ' . (trim($request['title']) !== "" ? $request['title'] : $speak->untitled . ' ' . date('Y/m/d H:i:s')) . "\n";
        $file .= 'Content Type: ' . (isset($request['content_type']) ? $request['content_type'] : 'HTML') . "\n";
        $file .= "\n" . SEPARATOR . "\n";
        $file .= "\n" . (trim($request['content']) !== "" ? $request['content'] : Config::speak('notify_empty', array(strtolower($speak->contents))));
        $file = Text::toPage($file);
        Weapon::fire('preview_title_before', array($P, $P));
        echo '<h2 class="preview-title preview-' . $kind . '-title">' . $file['title'] . '</h2>';
        Weapon::fire('preview_title_after', array($P, $P));
        Weapon::fire('preview_content_before', array($P, $P));
        echo '<div class="preview-content preview-' . $kind . '-content p">' . $file['content'] . '</div>';
        Weapon::fire('preview_content_after', array($P, $P));
    }
    echo '</div>';
    Weapon::fire('preview_after', array($P, $P));
    exit;
});