<?php

// Check for invalid time pattern
if(isset($request['date']) && trim($request['date']) !== "" && ! preg_match('#^[0-9]{4,}\-[0-9]{2}\-[0-9]{2}T[0-9]{2}\:[0-9]{2}\:[0-9]{2}\+[0-9]{2}\:[0-9]{2}$#', $request['date'])) {
    Notify::error($speak->notify_invalid_time_pattern);
    Guardian::memorize($request);
}
$request['id'] = (int) date('U', isset($request['date']) && trim($request['date']) !== "" ? strtotime($request['date']) : time());
$request['path'] = $task_connect->path;
$request['state'] = $request['action'] === 'publish' ? 'published' : 'draft';
// Set post date by submitted time, or by input value if available
$date = date('c', $request['id']);
// General field(s)
$title = trim(strip_tags(Request::post('title', $speak->untitled . ' ' . Date::format($date, 'Y/m/d H:i:s'), false), '<abbr><b><code><del><dfn><em><i><ins><span><strong><sub><sup><time><u><var>'));
$slug = Text::parse(Request::post('slug', $title, false), '->slug');
$slug = $slug === '--' ? Text::parse($title, '->slug') : $slug;
$content = $request['content'];
$description = $request['description'];
$author = strip_tags($request['author']);
$css = trim(Request::post('css', "", false));
$js = trim(Request::post('js', "", false));
$field = Request::post('fields', array());