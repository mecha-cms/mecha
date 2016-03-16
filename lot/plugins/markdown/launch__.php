<?php

if($config->html_parser->active === 'Markdown') {
    // Re-write `comment_wizard` value
    Config::set('speak.comment_wizard', $speak->__comment_wizard);
    // Create a new comment
    if(Request::method('post') && ! Guardian::happy()) {
        Route::over($config->index->slug . '/(:any)', function() {
            $is_mkd = ! isset($_POST['content_type']) || $_POST['content_type'] === 'Markdown';
            if($is_mkd && isset($_POST['message'])) {
                // **Parsedown Extra** does not support syntax to generate `ins` and `mark` tag
                $_POST['message'] = Text::parse($_POST['message'], '->text', '<br><img><ins><mark>', false);
                // Temporarily disallow image(s) in comment to prevent XSS
                if(strpos($_POST['message'], '![') !== false) {
                    $_POST['message'] = preg_replace('#(\!\[.*?\](?:\(.*?\)|\[.*?\]))#','`$1`', $_POST['message']);
                }
            }
        });
    }
}