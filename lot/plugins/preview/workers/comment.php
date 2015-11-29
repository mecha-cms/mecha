<?php

Weapon::fire('preview_before', array($P, $P));
$comment = Page::header('Content Type', Request::post('content_type', 'HTML', false))->content(Request::post('message', Config::speak('notify_empty', strtolower($speak->contents)), false))->read('message', 'comment:');
Weapon::fire('preview_content_before', array($P, $P));
echo '<div class="preview-content preview-comment-content p">' . $comment['message'] . '</div>';
Weapon::fire('preview_content_after', array($P, $P));
Weapon::fire('preview_after', array($P, $P));