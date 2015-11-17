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
Widget::add('tagLinks', function($connect = ', ') use($config, $speak) {
    $links = array();
    $article = Shield::lot('article');
    if( ! $article || ! isset($article->tags)) return "";
    foreach($article->tags as $tag) {
        if($tag && $tag->id !== 0) {
            $url = Filter::apply('tag:url', Filter::apply('url', $config->url . '/' . $config->tag->slug . '/' . $tag->slug));
            $links[] = '<a href="' . $url . '" rel="tag">' . $tag->name . '</a>';
        }
    }
    $text = count($links) > 1 ? $speak->tags : $speak->tag;
    return ! empty($links) ? $text . ': ' . implode($connect, $links) : "";
});

// HTML output manipulation
Filter::add('chunk:output', function($content, $path) use($config, $speak) {
    $name = File::N($path);
    // Add an icon to the older and newer link text
    if($name === 'pager') {
        $content = str_replace('>' . $speak->newer . '</a>', '><i class="fa fa-angle-left"></i> ' . trim(strip_tags($speak->newer)) . '</a>', $content);
        $content = str_replace('>' . $speak->older . '</a>', '>' . trim(strip_tags($speak->older)) . ' <i class="fa fa-angle-right"></i></a>', $content);
    }
    // Add an icon to the article date
    if($name === 'article.time') {
        $content = str_replace('<time ', '<i class="fa fa-calendar"></i> <time ', $content);
    }
    // Add an icon to the comments title
    if($name === 'comments.header') {
        $content = str_replace('<h4>', '<h4><i class="fa fa-comments"></i> ', $content);
    }
    // Add an icon to the comment form button
    if($name === 'comment.form') {
        $content = str_replace('>' . $speak->publish . '</button>', '><i class="fa fa-check-circle"></i> ' . trim(strip_tags($speak->publish)) . '</button>', $content);
    }
    // Add an icon to the log in/out link
    if($name === 'block.footer.copyright') {
        $content = str_replace('>' . $speak->log_in . '</a>', '><i class="fa fa-sign-in"></i> ' . trim(strip_tags($speak->log_in)) . '</a>', $content);
        $content = str_replace('>' . $speak->log_out . '</a>', '><i class="fa fa-sign-in"></i> ' . trim(strip_tags($speak->log_out)) . '</a>', $content);
    }
    // Add an icon to the log in form button
    if(Route::is($config->manager->slug . '/login') && $name === 'page.body') {
        return str_replace('>' . $speak->login . '</button>', '><i class="fa fa-key"></i> ' . trim(strip_tags($speak->login)) . '</button>', $content);
    }
    return $content;
});

// Exclude these fields on index (index, tag, archive, search) page ...
Config::set('article_fields_exclude', array('content', 'content_raw'));