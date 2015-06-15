<?php

/**
 * Custom Function(s)
 * ------------------
 *
 * Add your own custom function(s) here. You can do something like
 * making custom widget(s), custom route(s), custom filter(s),
 * custom weapon(s), loading custom asset(s), etc. So that you can
 * manipulate the site output without having to touch the CMS core.
 *
 */

// Link generator for the current article tag(s)
Widget::add('tagLinks', function($connect = ', ') use($speak) {
    $config = Config::get();
    $links = array();
    $source = $config->article->tags;
    if( ! isset($source) || ! is_object($source)) return "";
    foreach($source as $tag) {
        if($tag && $tag->id !== 0) {
            $links[] = '<a href="' . $config->url . '/' . $config->tag->slug . '/' . $tag->slug . '" rel="tag">' . $tag->name . '</a>';
        }
    }
    $text = count($links) > 1 ? $speak->tags : $speak->tag;
    return ! empty($links) ? $text . ': ' . implode($connect, $links) : "";
});

// Add an arrow to the older and newer link text
$speak->older = $speak->older . ' <i class="fa fa-angle-right"></i>';
$speak->newer = '<i class="fa fa-angle-left"></i> ' . $speak->newer;
Config::set(array(
    'speak.older' => $speak->older,
    'speak.newer' => $speak->newer
));