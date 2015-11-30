<?php

// Check for invalid time pattern
if(isset($request['date']) && trim($request['date']) !== "" && ! preg_match('#^[0-9]{4,}\-[0-9]{2}\-[0-9]{2}T[0-9]{2}\:[0-9]{2}\:[0-9]{2}\+[0-9]{2}\:[0-9]{2}$#', $request['date'])) {
    Notify::error($speak->notify_invalid_time_pattern);
    Guardian::memorize($request);
}
$request['id'] = (int) date('U', isset($request['date']) && trim($request['date']) !== "" ? strtotime($request['date']) : time());
$request['path'] = $post->path;
$request['state'] = $request['action'] === 'publish' ? 'published' : 'drafted';
// Set post date by submitted time, or by input value if available
$date = date('c', $request['id']);
// General field(s)
$title = trim(strip_tags(Request::post('title', $speak->untitled . ' ' . Date::format($date, 'Y/m/d H:i:s'), false), '<abbr><b><code><del><dfn><em><i><ins><span><strong><sub><sup><time><u><var>'));
$link = false;
if(isset($request['link']) && trim($request['link']) !== "") {
    if( ! Guardian::check($request['link'], '->url')) {
        Notify::error($speak->notify_invalid_url);
    } else {
        $link = $request['link'];
    }
}
if(strpos($request['slug'], '://') !== false) {
    $slug = Text::parse($title, '->slug');
    if( ! Guardian::check($request['slug'], '->url')) {
        Notify::error($speak->notify_invalid_url);
    } else {
        $link = $request['slug'];
    }
} else {
    $slug = Text::parse(Request::post('slug', $title, false), '->slug');
}
$slug = $slug === '--' ? 'post-' . time() : $slug;
$content = $request['content'];
$description = $request['description'];
$author = strip_tags($request['author']);
$css = trim(Request::post('css', "", false));
$js = trim(Request::post('js', "", false));
$field = Request::post('fields', array());
// Slug must contains at least one letter or one `-`. This validation added
// to prevent user(s) from inputting a page offset instead of post slug.
// Because the URL pattern of post's index page is `$post/1` and the
// URL pattern of post's single page is `$post/post-slug`
if(is_numeric($slug)) {
    Notify::error($speak->notify_error_slug_missing_letter);
    Guardian::memorize($request);
}
// Check for empty post content
if(trim($content) === "") {
    Notify::error(Config::speak('notify_error__content_empty', strpos($speak->notify_error__content_empty, '%s') === 0 ? $speak->{$segment} : strtolower($speak->{$segment})));
    Guardian::memorize($request);
}