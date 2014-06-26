<?php


/**
 * Manager Page(s)
 * ---------------
 */

if(Guardian::happy() && $deck = File::exist(DECK . DS . 'launch.php')) {
    include $deck;
}


/**
 * Login Page
 * ----------
 *
 * [1]. manager/login
 *
 */

Route::accept($config->manager->slug . '/login', function() use($config, $speak) {

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->login . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'login.php'
    ));

    if(Request::post()) {
        Guardian::authorize()->kick($config->manager->slug . '/article');
    } else {
        Guardian::reject();
    }

    Shield::attach('manager');

});


/**
 * Logout Page
 * -----------
 *
 * [1]. manager/logout
 *
 */

Route::accept($config->manager->slug . '/logout', function() use($config, $speak) {
    Notify::success($speak->logged_out . '.');
    Guardian::reject()->kick($config->manager->slug . '/login');
});


/**
 * Index Page
 * ----------
 *
 * [1]. article
 * [2]. article/1
 *
 */

Route::accept(array($config->index->slug, $config->index->slug . '/(:num)'), function($offset = 1) use($config) {

    $pages = array();
    $offset = (int) $offset;

    if($files = Mecha::eat(Get::articles('DESC'))->chunk($offset, $config->index->per_page)->vomit()) {
        foreach($files as $path) {
            $pages[] = Get::article($path, array('content', 'tags', 'css', 'js', 'comments'));
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
 * Archive Page
 * ------------
 *
 * [1]. archive/2014
 * [2]. archive/2014/1
 *
 */

Route::accept(array($config->archive->slug . '/(:num)', $config->archive->slug . '/(:num)/(:num)'), function($slug = "", $offset = 1) use($config) {

    $pages = array();

    if($files = Mecha::eat(Get::articles('DESC', 'time:' . $slug))->chunk($offset, $config->archive->per_page)->vomit()) {
        foreach($files as $path) {
            $pages[] = Get::article($path, array('content', 'tags', 'css', 'js', 'comments'));
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
        'pagination' => Navigator::extract(Get::articles('DESC', 'time:' . $slug), $offset, $config->archive->per_page, $config->archive->slug . '/' . $slug)
    ));

    Shield::attach('index');

});


/**
 * Archive Page
 * ------------
 *
 * [1]. archive/2014-04
 * [2]. archive/2014-04/1
 *
 */

Route::accept(array($config->archive->slug . '/(:num)-(:num)', $config->archive->slug . '/(:num)-(:num)/(:num)'), function($year = "", $month = "", $offset = 1) use($config, $speak) {

    $months = (array) $speak->months;
    $slug = $year . '-' . $month;
    $pages = array();

    if($files = Mecha::eat(Get::articles('DESC', 'time:' . $slug))->chunk($offset, $config->archive->per_page)->vomit()) {
        foreach($files as $path) {
            $pages[] = Get::article($path, array('content', 'tags', 'css', 'js', 'comments'));
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
        'pagination' => Navigator::extract(Get::articles('DESC', 'time:' . $slug), $offset, $config->archive->per_page, $config->archive->slug . '/' . $slug)
    ));

    Shield::attach('index');

});


/**
 * Tag Page
 * --------
 *
 * [1]. tagged/tag-slug
 * [2]. tagged/tag-slug/1
 *
 */

Route::accept(array($config->tag->slug . '/(:any)', $config->tag->slug . '/(:any)/(:num)'), function($slug = "", $offset = 1) use($config) {

    if( ! $tag = Get::tagsBy($slug)) {
        Shield::abort('404-tag');
    }

    $pages = array();

    if($files = Mecha::eat(Get::articles('DESC', 'kind:' . $tag->id))->chunk($offset, $config->tag->per_page)->vomit()) {
        foreach($files as $path) {
            $pages[] = Get::article($path, array('content', 'tags', 'css', 'js', 'comments'));
        }
    } else {
        Shield::abort('404-tag');
    }

    Config::set(array(
        'page_type' => 'tag',
        'page_title' => $config->tag->title . ' ' . $tag->name . $config->title_separator . $config->title,
        'offset' => $offset,
        'tag_query' => $slug,
        'pages' => $pages,
        'pagination' => Navigator::extract(Get::articles('DESC', 'kind:' . $tag->id), $offset, $config->tag->per_page, $config->tag->slug . '/' . $slug)
    ));

    Shield::attach('index');

});


/**
 * Search Page
 * -----------
 *
 * [1]. search/search+query
 * [2]. search/search+query/1
 *
 */

Route::accept(array($config->search->slug . '/(:any)', $config->search->slug . '/(:any)/(:num)'), function($query = "", $offset = 1) use($config) {

    $pages = array();
    $query = Text::parse($query)->to_decoded_url;
    $keywords = Text::parse($query)->to_slug;

    if(Session::get('search_query') == $query) {

        $pages = Session::get('search_results');

    } else {

        /**
         * Matched with all keywords combined
         */

        foreach(Get::articles() as $path) {
            $anchor = Get::articleAnchor($path);
            if(strpos(strtolower(basename($path, '.txt')), $keywords) !== false || strpos(strtolower($anchor->title), str_replace('-', ' ', $keywords)) !== false) {
                $pages[] = $path;
            }
        }

        /**
         * Matched with a single keyword
         */

        $keywords = explode('-', $keywords);
        foreach($keywords as $keyword) {
            foreach(Get::articles() as $path) {
                $anchor = Get::articleAnchor($path);
                if(strpos(strtolower(basename($path, '.txt')), $keyword) !== false || strpos(strtolower($anchor->title), $keyword) !== false) {
                    $pages[] = $path;
                }
            }
        }

        $pages = array_unique($pages); // Remove duplicate in search results

        Session::set('search_query', $query);
        Session::set('search_results', $pages);

    }

    if( ! empty($pages)) {
        $_pages = array();
        foreach(Mecha::eat($pages)->chunk($offset, $config->search->per_page)->vomit() as $path) {
            $_pages[] = Get::article($path, array('content', 'tags', 'css', 'js', 'comments'));
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
            'page_title' => $config->search->title . ' &ldquo;' . $query . '&rdquo;' . $config->title_separator . $config->title,
            'search_query' => $query
        ));
        Session::kill('search_query');
        Session::kill('search_results');
        Shield::abort('404-search');
    }

});


/**
 * Ignite Search ...
 * -----------------
 */

Route::accept($config->search->slug, function() use($config) {
    if(Request::post('q') !== false) {
        Guardian::kick($config->search->slug . '/' . strip_tags(Text::parse(Request::post('q'))->to_encoded_url));
    } else {
        Guardian::kick();
    }
});


/**
 * Article Page
 * ------------
 *
 * [1]. article/article-slug
 *
 */

Route::accept($config->index->slug . '/(:any)', function($slug = "") use($config, $speak) {

    if( ! $article = Get::article($slug)) {
        Shield::abort('404-article');
    }

    if($article->status == 'draft') {
        Shield::abort('404-article');
    }

    Config::set(array(
        'page_type' => 'article',
        'page_title' => $article->title . $config->title_separator . $config->index->title . $config->title_separator . $config->title,
        'page' => $article,
        'pagination' => Navigator::extract(Get::extract('articles'), $slug, 1, $config->index->slug)
    ));

    /**
     * Submitting a comment ...
     */

    if($request = Request::post()) {

        Guardian::checkToken($request['token'], $config->url_current . '#comment-form');

        $extension = $config->comment_moderation ? '.hold' : '.txt';

        if(trim($request['name']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->comment_name)));
        }

        if(trim($request['email']) !== "") {

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

        if(trim($request['url']) !== "" && ! Guardian::check($request['url'])->this_is_URL) {
            Notify::error($speak->notify_invalid_url);
        }

        if(trim($request['message']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->comment_message)));
        }

        if( ! Guardian::checkMath($request['math'])) {
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
         * Checks for spam keywords in comment
         */

        $keywords = explode(',', $config->spam_keywords);
        foreach($keywords as $spam) {
            if((trim($spam) !== "" && $request['email'] == trim($spam)) || (trim($spam) !== "" && strpos($request['message'], trim($spam)) !== false)) {
                Notify::warning($speak->notify_warning_intruder_detected . ' <mark>' . $spam . '</mark>');
                break;
            }
        }

        if( ! Notify::errors()) {

            $post = Date::format($article->time, 'Y-m-d-H-i-s');
            $id = date('Y-m-d-H-i-s');
            $parent = Request::post('parent');
            $message = strip_tags($request['message'], '<br>');

            $info = array(
                'data' => array(
                    'post' => $article->id,
                    'id' => (int) Date::format($id, 'U'),
                    'parent' => $parent ? (int) Date::format($parent, 'U') : null,
                    'name' => strip_tags($request['name']),
                    'email' => $request['email'],
                    'url' => Request::post('url', '#'),
                    'status' => Guardian::happy() ? 'pilot' : 'passenger',
                    'message_raw' => $message,
                    'message' => Text::parse($message)->to_html
                ),
                'execution_time' => time(),
                'error' => Notify::errors()
            );

            $data  = 'Name: ' . $info['data']['name'] . "\n";
            $data .= 'Email: ' . Text::parse($info['data']['email'])->to_ascii . "\n";
            $data .= 'URL: ' . $info['data']['url'] . "\n";
            $data .= 'Status: ' . $info['data']['status'] . "\n";
            $data .= "\n" . SEPARATOR . "\n\n" . $message;

            File::write($data)->saveTo(RESPONSE . DS . $post . '_' . $id . '_' . ($parent ? Date::format($parent, 'Y-m-d-H-i-s') : '0000-00-00-00-00-00') . $extension, 0600);

            Notify::success(Config::speak('notify_success_submitted', array($speak->comment)));

            Weapon::fire('on_comment_update', array($info));
            Weapon::fire('on_comment_construct', array($info));

            if($config->email_notification) {
                $header  = "From: " . $request['email'] . "\r\n";
                $header .= "Reply-To: " . $request['email'] . "\r\n";
                $header .= "Return-Path: " . $request['email'] . "\r\n";
                $header .= "X-Mailer: PHP/" . phpversion();
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
 * XML Sitemap
 * -----------
 *
 * [1]. Sitemap
 *
 */

Route::accept('sitemap', function() {
    header('Content-Type: text/xml; charset=UTF-8');
    Shield::attach(SHIELD . DS . 'sitemap', true, true);
});


/**
 * RSS Feed
 * --------
 *
 * [1]. feeds
 * [2]. feeds/rss
 * [3]. feeds/rss/1
 *
 */

Route::accept(array('feeds', 'feeds/rss', 'feeds/rss/(:num)'), function($offset = 1) {
    Config::set('offset', $offset);
    header('Content-Type: text/xml; charset=UTF-8');
    Shield::attach(SHIELD . DS . 'rss', true, true);
});


/**
 * Captcha Image
 * -------------
 *
 * [1]. captcha.png
 *
 */

Route::accept('captcha.png', function() {

    header('Content-Type: image/png');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');

    $bg = Request::get('bg', '333333');
    $color = Request::get('color', 'FFFFAA');
    $width = (int) Request::get('width', 100);
    $height = (int) Request::get('height', 30);
    $padding = (int) Request::get('padding', 7);
    $size = (int) Request::get('size', 16);
    $length = (int) Request::get('length', 7);
    $font = Request::get('font', 'special-elite-regular.ttf');
    $text = Session::get(Guardian::$captcha, "");

    if($bg !== 'false' && $bg = Converter::HEX2RGB($bg)) {
        $bg = array($bg['r'], $bg['g'], $bg['b'], $bg['a']);
    } else {
        $bg = $bg !== 'false' ? array(51, 51, 51, 1) : array(0, 0, 0, 0);
    }

    if($color = Converter::HEX2RGB($color)) {
        $color = array($color['r'], $color['g'], $color['b'], $color['a']);
    } else {
        $color = array(255, 255, 170, 1);
    }

    $image = imagecreatetruecolor($width, $height);

    imagefill($image, 0, 0, 0x7fff0000);
    imagealphablending($image, true);
    imagesavealpha($image, true);

    $bg = imagecolorallocatealpha($image, $bg[0], $bg[1], $bg[2], 127 - ($bg[3] * 127));
    $color = imagecolorallocatealpha($image, $color[0], $color[1], $color[2], 127 - ($color[3] * 127));

    imagefilledrectangle($image, 0, 0, $width, $height, $bg);
    imagettftext($image, $size, 0, $padding, $size + $padding, $color, ASSET . DS . '__captcha' . DS . $font, $text);
    imagepng($image);
    imagedestroy($image);

});


/**
 * Page Static
 * -----------
 *
 * [1]. page-slug
 *
 */

Route::accept('(:any)', function($slug = "") use($config) {

    if( ! $page = Get::page($slug)) {
        Shield::abort('404-page');
    }

    if($page->status == 'draft') {
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
 * Home Page
 * ---------
 *
 * [1]. /
 *
 */

Route::accept("", function() use($config) {

    Session::kill('search_query');
    Session::kill('search_results');

    $pages = array();

    if($files = Mecha::eat(Get::articles())->chunk(1, $config->index->per_page)->vomit()) {
        foreach($files as $path) {
            $pages[] = Get::article($path, array('content', 'tags', 'css', 'js', 'comments'));
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
 * 404 Page
 * --------
 *
 * Fallback to 404 page if nothing matched.
 *
 */

Shield::abort();