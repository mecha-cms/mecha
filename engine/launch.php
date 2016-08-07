<?php defined('ROOT') or die;


/**
 * Route Hook: before
 * ------------------
 */

Weapon::fire('routes_before');


// Exclude these fields ...
$excludes = (array) Config::get($config->page_type . '_fields_exclude', array('content'));


/**
 * Index Page
 * ----------
 *
 * [1]. article
 * [2]. article/1
 *
 */

Route::accept(array($config->index->slug, $config->index->slug . '/(:num)'), function($offset = 1) use($config, $excludes) {
    $s = Get::articles();
    if($articles = Mecha::eat($s)->chunk($offset, $config->index->per_page)->vomit()) {
        $articles = Mecha::walk($articles, function($path) use($excludes) {
            return Get::article($path, $excludes);
        });
    } else {
        if($offset !== 1) {
            Shield::abort('404-index');
        } else {
            $articles = false;
        }
    }
    Filter::add('pager:url', function($url) {
        return Filter::apply('index:url', $url);
    });
    Config::set(array(
        'page_title' => $config->index->title . $config->title_separator . $config->title,
        'offset' => $offset,
        'articles' => $articles,
        'pagination' => Navigator::extract($s, $offset, $config->index->per_page, $config->index->slug)
    ));
    Shield::attach('index-article');
}, 30);


/**
 * Archive Page
 * ------------
 *
 * [1]. archive/2014
 * [2]. archive/2014/1
 * [3]. archive/2014-02
 * [4]. archive/2014-02/1
 * [5]. archive/2014-02-12
 * [6]. archive/2014-02-12/1
 * [7]. ...
 *
 */

Route::accept(array($config->archive->slug . '/(:any)', $config->archive->slug . '/(:any)/(:num)'), function($slug = "", $offset = 1) use($config, $speak, $excludes) {
    $s = Get::articles('DESC', 'time:' . $slug);
    $months = $speak->month_names;
    if($articles = Mecha::eat($s)->chunk($offset, $config->archive->per_page)->vomit()) {
        $articles = Mecha::walk($articles, function($path) use($excludes) {
            return Get::article($path, $excludes);
        });
    } else {
        Shield::abort('404-archive');
    }
    Filter::add('pager:url', function($url) {
        return Filter::apply('archive:url', $url);
    });
    $t = explode('-', $slug);
    $title = $t[0];
    if(isset($t[1])) {
        $title .= ', ' . $months[(int) $t[1] - 1];
    }
    if(isset($t[2])) {
        $title .= ' ' . $t[2];
    }
    $title = sprintf($config->archive->title, $title);
    Config::set(array(
        'page_title' => $title . $config->title_separator . $config->title,
        'archive_query' => $slug,
        'offset' => $offset,
        'articles' => $articles,
        'pagination' => Navigator::extract($s, $offset, $config->archive->per_page, $config->archive->slug . '/' . $slug)
    ));
    Shield::attach('index-archive');
}, 40);


/**
 * Tag Page
 * --------
 *
 * [1]. tag/tag-slug
 * [2]. tag/tag-slug/1
 *
 */

Route::accept(array($config->tag->slug . '/(:any)', $config->tag->slug . '/(:any)/(:num)'), function($slug = "", $offset = 1) use($config, $excludes) {
    if( ! $tag = Get::articleTag('slug:' . $slug)) {
        Shield::abort('404-tag');
    }
    $s = Get::articles('DESC', 'kind:' . $tag->id);
    if($articles = Mecha::eat($s)->chunk($offset, $config->tag->per_page)->vomit()) {
        $articles = Mecha::walk($articles, function($path) use($excludes) {
            return Get::article($path, $excludes);
        });
    } else {
        Shield::abort('404-tag');
    }
    Filter::add('pager:url', function($url) {
        return Filter::apply('tag:url', $url);
    });
    Config::set(array(
        'page_title' => sprintf($config->tag->title, $tag->name) . $config->title_separator . $config->title,
        'tag_query' => $slug,
        'offset' => $offset,
        'articles' => $articles,
        'pagination' => Navigator::extract($s, $offset, $config->tag->per_page, $config->tag->slug . '/' . $slug)
    ));
    // `meta` description data based on current tag description
    if($description = Text::parse($tag->description, '->text')) {
        Config::set('tag.description', $description);
    }
    Shield::attach('index-tag');
}, 50);


/**
 * Search Page
 * -----------
 *
 * [1]. search/search+query
 * [2]. search/search+query/1
 *
 */

Route::accept(array($config->search->slug . '/(:any)', $config->search->slug . '/(:any)/(:num)'), function($query = "", $offset = 1) use($config, $speak, $excludes) {
    $query = Text::parse($query, '->decoded_url');
    $keywords = Text::parse($query, '->slug');
    $articles = array();
    if(Session::get('search.query') === $query) {
        $articles = Session::get('search.results');
    } else {
        // Matched with all keyword(s) combined
        if(trim($keywords) && $files = Get::articles('DESC', 'keyword:' . $keywords)) {
            foreach($files as $file) {
                $articles[] = $file;
                $anchor = Get::articleAnchor($file);
                $kw = str_replace('-', ' ', $keywords);
                if(stripos(strip_tags($anchor->title), $kw) !== false || stripos(File::N($anchor->path), $kw) !== false) {
                    $articles[] = $file;
                }
            }
            unset($files);
        }
        // Matched with a keyword
        $keywords = explode('-', $keywords);
        foreach($keywords as $keyword) {
            if(trim($keyword) && $files = Get::articles('DESC', 'keyword:' . $keyword)) {
                foreach($files as $file) {
                    $articles[] = $file;
                    $anchor = Get::articleAnchor($file);
                    if(stripos(strip_tags($anchor->title), $keyword) !== false || stripos(File::N($anchor->path), $keyword) !== false) {
                        $articles[] = $file;
                    }
                }
                unset($files);
            }
        }
        $articles = array_unique($articles); // Remove search result(s) duplicate
        Session::set('search.query', $query);
        Session::set('search.results', $articles);
    }
    $title = sprintf($config->search->title, $query);
    if( ! empty($articles) && $results = Mecha::eat($articles)->chunk($offset, $config->search->per_page)->vomit()) {
        $_articles = Mecha::walk($results, function($path) use($excludes) {
            return Get::article($path, $excludes);
        });
        Filter::add('pager:url', function($url) {
            return Filter::apply('search:url', $url);
        });
        Config::set(array(
            'page_title' => $title . $config->title_separator . $config->title,
            'search_query' => $query,
            'offset' => $offset,
            'articles' => $_articles,
            'pagination' => Navigator::extract($articles, $offset, $config->search->per_page, $config->search->slug . '/' . Text::parse($query, '->encoded_url'))
        ));
        Shield::attach('index-search');
    } else {
        $_404 = (object) array(
            'title' => $title,
            'content' => $speak->notify_error_not_found
        );
        Config::set(array(
            'page_title' => $title . $config->title_separator . $config->title,
            'search_query' => $query,
            'offset' => $offset,
            'article' => $_404,
            'page' => $_404
        ));
        Session::kill('search.query');
        Session::kill('search.results');
        Shield::abort('404-search');
    }
}, 60);


/**
 * Ignite Search ...
 * -----------------
 */

Route::accept($config->search->slug, function() use($config) {
    if($q = strip_tags(Request::post('q', ""))) {
        Guardian::kick($config->search->slug . '/' . Text::parse($q, '->encoded_url'));
    }
    Route::execute('(:any)', array($config->search->slug));
}, 61);


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
    if($article->state === 'drafted') {
        Shield::abort('404-article');
    }
    // Collecting article slug ...
    if($articles = Get::articles('DESC', "", File::E($article->path))) {
        $articles = Mecha::walk($articles, function($path) {
            $parts = explode('_', File::N($path), 3);
            return $parts[2];
        });
    }
    Filter::add('pager:url', function($url) {
        return Filter::apply('article:url', $url);
    });
    Config::set(array(
        'page_title' => $article->title . $config->title_separator . $config->title,
        'article' => $article,
        'pagination' => Navigator::extract($articles, $slug, 1, $config->index->slug)
    ));
    Weapon::add('shell_after', function() use($article) {
        if(isset($article->css) && trim($article->css) !== "") echo O_BEGIN . $article->css . O_END;
    }, 11);
    Weapon::add('sword_after', function() use($article) {
        if(isset($article->js) && trim($article->js) !== "") echo O_BEGIN . $article->js . O_END;
    }, 11);
    Shield::attach('article-' . $slug);
}, 70);


/**
 * XML Sitemap
 * -----------
 *
 * [1]. sitemap
 *
 */

Route::accept('sitemap', function() use($config) {
    HTTP::mime('text/xml', $config->charset);
    Shield::attach(SHIELD . DS . 'sitemap.php');
}, 80);


/**
 * Feed
 * ----
 *
 * [1]. feed
 * [2]. feed/rss
 * [3]. feed/rss/1
 *
 * [1]. feed/json
 * [2]. feed/json/1
 * [3]. feed/json?callback=whatTheFunc
 * [4]. feed/json/1?callback=whatTheFunc
 *
 * [1]. feed/sitemap
 *
 */

Route::accept(array('feed', 'feed/(:any)', 'feed/(:any)/(:num)'), function($slug = 'rss', $offset = 1) use($config) {
    if($slug === 'sitemap') Route::execute('sitemap');
    $order = strtoupper(Request::get('order', 'DESC'));
    $filter = Request::get('filter', "");
    $chunk = Request::get('chunk', 25);
    $scope = Request::get('scope', 'article');
    Filter::remove($scope . ':output', 'do_comments_field');
    Filter::remove($scope . ':output', 'do_custom_field');
    if( ! file_exists(SHIELD . DS . $slug . '.php') || ! Get::kin($scope . 's', false, true)) {
        Shield::abort('404-feed');
    }
    $s = call_user_func('Get::' . $scope . 's', $order, $filter);
    if($posts = Mecha::eat($s)->chunk($offset, $chunk)->vomit()) {
        $posts = Mecha::walk($posts, function($path) use($scope) {
            $post = call_user_func('Get::' . $scope . 'Header', $path);
            // Hide sensitive data from public ...
            foreach($post as $k => $v) {
                if(substr($k, -4) === '_raw') unset($post->{$k});
            }
            unset($post->path, $post->date, $post->fields);
            return $post;
        });
    } else {
        Shield::abort('404-feed');
    }
    Filter::add('pager:url', function($url) {
        return Filter::apply('feed:url', $url);
    });
    Config::set(array(
        'offset' => $offset,
        $scope . 's' => $posts,
        'pagination' => Navigator::extract($s, $offset, $chunk, 'feed/' . $slug)
    ));
    Shield::attach(SHIELD . DS . $slug . '.php');
}, 81);


/**
 * Captcha Image
 * -------------
 *
 * [1]. captcha.png
 *
 */

Route::accept('captcha.png', function() {
    HTTP::mime('image/png')->header(array(
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        'Cache-Control' => 'post-check=0, pre-check=0',
        'Pragma' => 'no-cache'
    ));
    $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $bg = Request::get('bg', '333333', false);
    $color = Request::get('color', 'FFFFAA', false);
    $padding = Request::get('padding', 0);
    $width = Request::get('width', 100) + ($padding * 2);
    $height = Request::get('height', 30) + ($padding * 2);
    $size = Request::get('size', 16);
    $length = Request::get('length', 7);
    $font = Request::get('font', 'special-elite-regular.ttf', false);
    $text = substr(str_shuffle($str), 0, $length);
    Session::set(Guardian::$captcha, $text);
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
    $font = strpos($font, '/') === false ? ASSET . DS . '__captcha' . DS . $font : ROOT . DS . File::path($font);
    imagefill($image, 0, 0, 0x7fff0000);
    imagealphablending($image, true);
    imagesavealpha($image, true);
    $bg = imagecolorallocatealpha($image, $bg[0], $bg[1], $bg[2], 127 - ($bg[3] * 127));
    $color = imagecolorallocatealpha($image, $color[0], $color[1], $color[2], 127 - ($color[3] * 127));
    imagefilledrectangle($image, 0, 0, $width, $height, $bg);
    // center the image text ...
    $xi = imagesx($image);
    $yi = imagesy($image);
    $box = imagettfbbox($size, 0, $font, $text);
    $xr = abs(max($box[2], $box[4]));
    $yr = abs(max($box[5], $box[7]));
    $x = intval(($xi - $xr) / 2);
    $y = intval(($yi + $yr) / 2);
    imagettftext($image, $size, 0, $x, $y, $color, $font, $text);
    imagepng($image);
    imagedestroy($image);
    exit;
}, 90);


/**
 * Static Page
 * -----------
 *
 * [1]. page-slug
 *
 */

Route::accept('(:any)', function($slug = "") use($config) {
    if( ! $page = Get::page($slug)) {
        Shield::abort('404-page');
    }
    if($page->state === 'drafted') {
        Shield::abort('404-page');
    }
    Filter::add('pager:url', function($url) {
        return Filter::apply('page:url', $url);
    });
    Config::set(array(
        'page_title' => $page->title . $config->title_separator . $config->title,
        'page' => $page
    ));
    Weapon::add('shell_after', function() use($page) {
        if(isset($page->css) && trim($page->css) !== "") echo O_BEGIN . $page->css . O_END;
    }, 11);
    Weapon::add('sword_after', function() use($page) {
        if(isset($page->js) && trim($page->js) !== "") echo O_BEGIN . $page->js . O_END;
    }, 11);
    Shield::attach('page-' . $slug);
}, 100);


/**
 * Home Page
 * ---------
 *
 * [1]. /
 *
 */

Route::accept('/', function() use($config, $excludes) {
    Session::kill('search.query');
    Session::kill('search.results');
    $s = Get::articles();
    if($articles = Mecha::eat($s)->chunk(1, $config->index->per_page)->vomit()) {
        $articles = Mecha::walk($articles, function($path) use($excludes) {
            return Get::article($path, $excludes);
        });
    } else {
        $articles = false;
    }
    Filter::add('pager:url', function($url) {
        return Filter::apply('index:url', $url);
    });
    Config::set(array(
        'articles' => $articles,
        'pagination' => Navigator::extract($s, 1, $config->index->per_page, $config->index->slug)
    ));
    Shield::attach('page-home');
}, 110);


/**
 * Route Hook: after
 * -----------------
 */

Weapon::fire('routes_after');


/**
 * Do Routing
 * ----------
 */

Route::execute();


/**
 * 404 Page
 * --------
 *
 * Fallback to 404 page if nothing matched.
 *
 */

Shield::abort();