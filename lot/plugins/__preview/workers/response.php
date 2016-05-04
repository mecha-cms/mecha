<?php

Weapon::fire('preview_before', array($P, $P));
$s = File::N($_SERVER['REQUEST_URI']);
$response = Page::header('Content Type', Request::post('content_type', 'HTML', false))->content(Request::post('message', Config::speak('notify_empty', strtolower($speak->messages)), false))->read('message', $s . ':');
Weapon::fire('preview_message_before', array($P, $P));
echo '<div class="preview-message preview-' . $s . '-message p">' . $response['message'] . '</div>';
Weapon::fire('preview_message_after', array($P, $P));
Weapon::fire('preview_after', array($P, $P));