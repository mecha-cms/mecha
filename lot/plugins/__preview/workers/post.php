<?php

Weapon::fire('preview_before', array($P, $P));
$s = File::N($_SERVER['REQUEST_URI']);
$post = Page::header(array(
    'Title' => Request::post('title', $speak->untitled . ' ' . date('Y/m/d H:i:s'), false),
    'Content Type' => Request::post('content_type', 'HTML', false)
))->content(Request::post('content', Config::speak('notify_empty', strtolower($speak->contents)), false))->read('content', $s . ':');
Weapon::fire('preview_title_before', array($P, $P));
echo '<h2 class="preview-title preview-' . $s . '-title">' . $post['title'] . '</h2>';
Weapon::fire('preview_title_after', array($P, $P));
Weapon::fire('preview_content_before', array($P, $P));
echo '<div class="preview-content preview-' . $s . '-content p">' . $post['content'] . '</div>';
Weapon::fire('preview_content_after', array($P, $P));
echo '</div>';
Weapon::fire('preview_after', array($P, $P));