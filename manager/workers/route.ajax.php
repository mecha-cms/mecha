<?php


/**
 * AJAX Preview
 * ------------
 */

Route::accept($config->manager->slug . '/ajax/preview:(:any)', function($kind = "") use($config, $speak) {
    $P = array('data' => Request::post());
    if($kind == 'article' || $kind == 'page') {
        Weapon::fire('preview_content_before', array($P, $P));
        echo '<div class="inner">';
        if(Request::post()) {
            $title = Request::post('title');
            $title = Filter::apply('title', $title);
            $title = Filter::apply($kind . ':title', $title);
            $content = Request::post('content', '<p class="empty">' . Config::speak('notify_empty', array(strtolower($speak->contents))) . '</p>');
            $content = Filter::apply('shortcode', $content);
            $content = Filter::apply($kind . ':shortcode', $content);
            $content = Filter::apply('content', Text::parse($content)->to_html);
            $content = Filter::apply($kind . ':content', $content);
            echo '<h2 class="preview-title">' . $title . '</h2>';
            echo '<div class="preview-content p">' . $content . '</div>';
        }
        echo '</div>';
        Weapon::fire('preview_content_after', array($P, $P));
    }
    exit;
});