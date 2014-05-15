<?php


/**
 * Index Page => `article`, `article/1`
 */

Route::accept(array($config->index->slug, $config->index->slug . '/(:num)'), function($offset = 1) use($config) {

    $pages = array();

    if($files = Mecha::eat(Get::articles('DESC'))->chunk($offset, $config->index->per_page)->vomit()) {
        foreach($files as $file_path) {
            $pages[] = Get::article($file_path, array('content', 'tags', 'css', 'js', 'comments'));
        }
    } else {
        if($offset !== 1) {
            Shield::abort('404-index');
        } else {
            $pages = false;
        }
    }

    Config::set(array(
        'page_type' => 'index',
        'page_title' => $config->index->title . $config->title_separator . $config->title,
        'offset' => $offset,
        'pages' => $pages,
        'pagination' => Navigator::extract(Get::articles(), $offset, $config->index->per_page, $config->index->slug)
    ));

    Shield::attach('index');

});


/**
 * Archive Page => `archive/2014`, `archive/2014/1`
 */

Route::accept(array($config->archive->slug . '/(:num)', $config->archive->slug . '/(:num)/(:num)'), function($slug = "", $offset = 1) use($config) {

    $pages = array();

    if($files = Mecha::eat(Get::articles('DESC', $slug))->chunk($offset, $config->archive->per_page)->vomit()) {
        foreach($files as $file_path) {
            $pages[] = Get::article($file_path, array('content', 'tags', 'css', 'js', 'comments'));
        }
    } else {
        Shield::abort('404-archive');
    }

    Config::set(array(
        'page_type' => 'archive',
        'page_title' => $config->archive->title . ' ' . $slug . $config->title_separator . $config->title,
        'offset' => $offset,
        'archive_query' => $slug,
        'pages' => $pages,
        'pagination' => Navigator::extract(Get::articles('DESC', $slug), $offset, $config->archive->per_page, $config->archive->slug . '/' . $slug)
    ));

    Shield::attach('index');

});


/**
 * Archive Page => `archive/2014-04`, `archive/2014-04/1`
 */

Route::accept(array($config->archive->slug . '/(:num)-(:num)', $config->archive->slug . '/(:num)-(:num)/(:num)'), function($year = "", $month = "", $offset = 1) use($config, $speak) {

    $months = (array) $speak->months;
    $slug = $year . '-' . $month;
    $pages = array();

    if($files = Mecha::eat(Get::articles('DESC', $slug))->chunk($offset, $config->archive->per_page)->vomit()) {
        foreach($files as $file_path) {
            $pages[] = Get::article($file_path, array('content', 'tags', 'css', 'js', 'comments'));
        }
    } else {
        Shield::abort('404-archive');
    }

    Config::set(array(
        'page_type' => 'archive',
        'page_title' => $config->archive->title . ' ' . ($config->widget_year_first ? $year . ', ' . $months[(int) $month - 1] : $months[(int) $month - 1] . ' ' . $year) . $config->title_separator . $config->title,
        'offset' => $offset,
        'archive_query' => $slug,
        'pages' => $pages,
        'pagination' => Navigator::extract(Get::articles('DESC', $slug), $offset, $config->archive->per_page, $config->archive->slug . '/' . $slug)
    ));

    Shield::attach('index');

});


/**
 * Tag Page => `tagged/tag-slug`, `tagged/tag-slug/1`
 */

Route::accept(array($config->tag->slug . '/(:any)', $config->tag->slug . '/(:any)/(:num)'), function($slug = "", $offset = 1) use($config) {

    if( ! $tag = Get::tagsBy($slug)) {
        Shield::abort('404-tag');
    }

    Config::set('page_type', 'tag'); // Set the page type before looping, special for tag page

    $pages = array();

    if($files = Mecha::eat(Get::articles('DESC', $tag->id))->chunk($offset, $config->tag->per_page)->vomit()) {
        foreach($files as $file_path) {
            $pages[] = Get::article($file_path, array('content', 'tags', 'css', 'js', 'comments'));
        }
    } else {
        Shield::abort('404-tag');
    }

    Config::set(array(
        'page_title' => $config->tag->title . ' ' . $tag->name . $config->title_separator . $config->title,
        'offset' => $offset,
        'tag_query' => $slug,
        'pages' => $pages,
        'pagination' => Navigator::extract(Get::articles('DESC', $tag->id), $offset, $config->tag->per_page, $config->tag->slug . '/' . $slug)
    ));

    Shield::attach('index');

});


/**
 * Search Page => `search/search-query`, `search/search-query/1`
 */

Route::accept(array($config->search->slug . '/(:any)', $config->search->slug . '/(:any)/(:num)'), function($query = "", $offset = 1) use($config) {

    $query = Text::parse($query)->to_decoded_url;
    $keywords = Text::parse($query)->to_slug;
    $pages = array();

    /**
     * Matched with all keywords combined
     */
    foreach(glob(ARTICLE . '/*.txt') as $file_path) {
        $anchor = Get::articleAnchor($file_path);
        if(strpos(strtolower(basename($file_path, '.txt')), $keywords) !== false || strpos(strtolower($anchor->title), str_replace('-', ' ', $keywords)) !== false) {
            $pages[] = $file_path;
        }
    }

    /**
     * Matched with a single keyword
     */
    foreach(explode('-', $keywords) as $keyword) {
        foreach(glob(ARTICLE . '/*.txt') as $file_path) {
            $anchor = Get::articleAnchor($file_path);
            if(strpos(strtolower(basename($file_path, '.txt')), $keyword) !== false || strpos(strtolower($anchor->title), $keyword) !== false) {
                $pages[] = $file_path;
            }
        }
    }

    if( ! empty($pages)) {
        $_pages = array();
        foreach(Mecha::eat(array_unique($pages))->chunk($offset, $config->search->per_page)->vomit() as $file_path) {
            $_pages[] = Get::article($file_path, array('content', 'tags', 'css', 'js', 'comments'));
        }
        Config::set(array(
            'page_type' => 'search',
            'page_title' => $config->search->title . ' &ldquo;' . $query . '&rdquo;' . $config->title_separator . $config->title,
            'offset' => $offset,
            'search_query' => $query,
            'pages' => $_pages,
            'pagination' => Navigator::extract($pages, $offset, $config->search->per_page, $config->search->slug . '/' . Text::parse($query)->to_encoded_url)
        ));
        Shield::attach('index');
    } else {
        Config::set(array(
            'page_type' => 'search',
            'page_title' => $config->search->title . ' &ldquo;' . $query . '&rdquo;' . $config->title_separator . $config->title,
            'search_query' => $query
        ));
        Shield::abort('404-search');
    }

});


/**
 * Ignite Search ...
 */

Route::accept($config->search->slug, function() use($config) {
    if(Request::post('q') !== false) {
        Guardian::kick($config->search->slug . '/' . strip_tags(Text::parse(Request::post('q'))->to_encoded_url));
    } else {
        Guardian::kick();
    }
});


/**
 * Article Page => `article/article-slug`
 */

Route::accept($config->index->slug . '/(:any)', function($slug = "") use($config, $speak) {

    if( ! $article = Get::article($slug)) {
        Shield::abort('404-article');
    }

    Config::set(array(
        'page_type' => 'article',
        'page_title' => $article->title . $config->title_separator . $config->index->title . $config->title_separator . $config->title,
        'page' => $article,
        'pagination' => Navigator::extract(Get::extract(Get::articles()), $slug, 1, $config->index->slug)
    ));

    /**
     * Submitting a comment...
     */
    if($request = Request::post()) {

        Guardian::checkToken($request['token']);

        if(empty($request['name'])) {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->comment_name)));
        }

        if( ! empty($request['email'])) {

            if( ! Guardian::check($request['email'])->this_is_email) {

                Notify::error($speak->notify_invalid_email);

            } else {

                /**
                 * Do not allow passengers to enter your
                 * email address in the comment email field
                 */
                if( ! Guardian::happy() && $request['email'] == $config->author_email) {
                    Notify::warning(Config::speak('notify_warning_forbidden_input', array($request['email'], strtolower($speak->email))));
                }

            }

        } else {

            Notify::error(Config::speak('notify_error_empty_field', array($speak->email)));

        }

        if( ! empty($request['url']) && ! Guardian::check($request['url'])->this_is_URL) {
            Notify::error($speak->notify_invalid_url);
        }

        if(empty($request['message'])) {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->comment_message)));
        }

        if( ! is_numeric($request['math']) || ! Guardian::check((int) $request['math'], Session::get(Guardian::$math))->this_is_correct) {
            Notify::error($speak->notify_invalid_math_answer);
        }

        if(strlen($request['name']) > 100) {
            Notify::error(Config::speak('notify_error_too_long', array($speak->comment_name)));
        }

        if(strlen($request['email']) > 100) {
            Notify::error(Config::speak('notify_error_too_long', array($speak->comment_email)));
        }

        if(strlen($request['url']) > 100) {
            Notify::error(Config::speak('notify_error_too_long', array($speak->comment_url)));
        }

        if(strlen($request['message']) > 1700) {
            Notify::error(Config::speak('notify_error_too_long', array($speak->comment_message)));
        }

        /**
         * Detect spam keywords in comment
         */
        $keywords = $request['email'] ? $request['email'] . ',' . $config->spam_keywords : $config->spam_keywords;
        foreach(explode(',', $keywords) as $spam) {
            if(trim($spam) !== "" && strpos($request['message'], trim($spam)) !== false) {
                Notify::warning($speak->notify_warning_intruder_detected . ' <mark>' . $spam . '</mark>');
                break;
            }
        }

        if( ! Notify::errors()) {

            $post = Date::format($article->time, 'Y-m-d-H-i-s');
            $id = date('Y-m-d-H-i-s');
            $parent = Request::post('parent');
            $status = Guardian::happy() ? 'pilot' : 'passenger';

            $data  = 'Name: ' . strip_tags($request['name']) . "\n";
            $data .= 'Email: ' . Text::parse($request['email'])->to_ascii . "\n";
            $data .= 'URL: ' . Request::post('url', '#') . "\n";
            $data .= 'Status: ' . $status . "\n";
            $data .= "\n" . SEPARATOR . "\n\n" . strip_tags($request['message'], '<br>');

            File::write($data)->saveTo(RESPONSE . '/' . $post . '_' . $id . '_' . ($parent ? Date::format($parent, 'Y-m-d-H-i-s') : '0000-00-00-00-00-00') . '.txt');

            Weapon::fire('on_comment_submit');
            Weapon::fire('on_comment_update');

            Notify::success(Config::speak('notify_success_submitted', array($speak->comment)));

            if($config->email_notification) {
                $header  = "From: " . $request['email'] . " \r\n";
                $header .= "Reply-To: " . $request['email'] . " \r\n";
                $header .= "Return-Path: " . $request['email'] . " \r\n";
                $header .= "X-Mailer: PHP \r\n";
                $message  = Config::speak('comment_notification', array($article->url . '#comment-' . Date::format($id, 'U'))) . "\r\n\r\n";
                $message .= $request['name'] . ": " . $request['message'] . "\r\n\r\n";
                $message .= Date::format($id, 'Y/m/d H:i:s');

                /**
                 * Sending email notification ...
                 */
                if( ! Guardian::happy()) {
                    mail($config->author_email, $speak->comment_notification_subject, $message, $header);
                }
            }

            Guardian::kick($config->url_current . '#comment-' . Date::format($id, 'U'));

        } else {

            Guardian::kick($config->url_current . '#comment-form');

        }

    }

    Shield::attach('article-' . $slug);

});


/**
 * XML Sitemap => `sitemap`
 */

Route::accept('sitemap', function() use($config) {

    header('Content-Type: text/xml; charset=UTF-8');
    Shield::attach(str_replace(ROOT, "", SHIELD) . '/sitemap', true, true);

});


/**
 * RSS Feed => `feeds/rss`
 */

Route::accept(array('feeds', 'feeds/rss', 'feeds/rss/(:num)'), function($offset = 1) use($config) {

    Config::set('offset', $offset);

    header('Content-Type: text/xml; charset=UTF-8');
    Shield::attach(str_replace(ROOT, "", SHIELD) . '/rss', true, true);

});


/**
 * Page Static
 */

Route::accept('(:any)', function($slug = "") use($config) {

    if( ! $page = Get::page($slug)) {
        Shield::abort('404-page');
    }

    Config::set(array(
        'page_type' => 'page',
        'page_title' => $page->title . $config->title_separator . $config->title,
        'page' => $page
    ));

    Shield::attach('page-' . $slug);

});


/**
 * Home Page => ``
 */

Route::accept("", function() use($config) {

    $pages = array();

    if($files = Mecha::eat(Get::articles())->chunk(1, $config->index->per_page)->vomit()) {
        foreach($files as $file_path) {
            $pages[] = Get::article($file_path, array('content', 'tags', 'css', 'js', 'comments'));
        }
    } else {
        $pages = false;
    }

    Config::set(array(
        'page_type' => 'home',
        'pages' => $pages,
        'pagination' => Navigator::extract(Get::articles(), 1, $config->index->per_page, $config->index->slug)
    ));

    Shield::attach('page-home');

});


/**
 * 404 PAGE. Fallback to 404 page if nothing matched.
 */

Shield::abort();