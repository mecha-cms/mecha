<?php

Weapon::fire('preview_before', array($P, $P));
$page = Page::header(array(
    'Title' => Request::post('title', $speak->untitled . ' ' . date('Y/m/d H:i:s'), false),
    'Content Type' => Request::post('content_type', 'HTML')
))->content(Request::post('content', Config::speak('notify_empty', strtolower($speak->contents)), false))->read();
Weapon::fire('preview_title_before', array($P, $P));
echo '<h2 class="preview-title preview-article-title">' . $page['title'] . '</h2>';
Weapon::fire('preview_title_after', array($P, $P));
Weapon::fire('preview_content_before', array($P, $P));
echo '<div class="preview-content preview-article-content p">' . $page['content'] . '</div>';
Weapon::fire('preview_content_after', array($P, $P));
echo '</div>';
Weapon::fire('preview_after', array($P, $P));