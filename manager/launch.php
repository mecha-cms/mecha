<?php


/**
 * Inject the Required Assets for Manager
 * --------------------------------------
 */

Weapon::add('shell_after', function() use($config) {
    echo Asset::stylesheet(array(
        $config->protocol . 'netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css',
        'manager/shell/editor.css',
        'manager/shell/check.css',
        'manager/shell/upload.css',
        'manager/shell/tab.css',
        'manager/shell/modal.css',
        'manager/shell/tooltip.css',
        'manager/shell/sortable.css',
        'manager/shell/accordion.css',
        'shell/manager.css'
    ));
}, 10);

Weapon::add('cargo_before', function() use($config, $speak) {
    echo '<div class="author-banner">' . $speak->welcome . ' <strong>' . Guardian::get('author') . '!</strong> &bull; <a href="' . $config->url . '/' . $config->manager->slug . '/logout">' . $speak->logout . '</a></div>';
}, 10);

Weapon::add('sword_before', function() {
    echo Asset::script('manager/sword/dashboard.js');
}, 10);

Weapon::add('sword_after', function() use($config) {
    echo Asset::script(array(
        $config->protocol . 'cdnjs.cloudflare.com/ajax/libs/zepto/1.1.3/zepto.min.js',
        'manager/sword/editor/editor.min.js',
        'manager/sword/editor/mte.min.js',
        'manager/sword/row.js',
        'manager/sword/slug.js',
        'manager/sword/check.js',
        'manager/sword/upload.js',
        'manager/sword/tab.js',
        'manager/sword/modal.js',
        'manager/sword/tooltip.js',
        'manager/sword/sortable.js',
        'manager/sword/accordion.js'
    ));
}, 10);


/**
 * Add Global Cache Killer for Articles and Pages
 * ----------------------------------------------
 */

function kill_cache() {
    global $config;
    $root = ( ! empty($config->base) ? str_replace('/', '.', $config->base) . '.' : "");
    File::open(CACHE . DS . $root . 'sitemap.cache.txt')->delete();
    File::open(CACHE . DS . $root . 'feeds.cache.txt')->delete();
    File::open(CACHE . DS . $root . 'feeds.rss.cache.txt')->delete();
}

Weapon::add('on_article_update', 'kill_cache', 10);
Weapon::add('on_page_update', 'kill_cache', 10);


/**
 * Add Default Article and Page Footer Links
 * -----------------------------------------
 */

Weapon::add('article_footer', function($article) {
    $config = Config::get();
    $speak = Config::speak();
    if($config->page_type == 'manager') {
        echo '<a href="' . $config->url . '/' . $config->manager->slug . '/article/repair/id:' . $article->id . '">' . $speak->edit . '</a> / <a href="' . $config->url . '/' . $config->manager->slug . '/article/kill/id:' . $article->id . '">' . $speak->delete . '</a>';
    }
}, 20);

Weapon::add('page_footer', function($page) {
    $config = Config::get();
    $speak = Config::speak();
    if($config->page_type == 'manager') {
        echo '<a href="' . $config->url . '/' . $config->manager->slug . '/page/repair/id:' . $page->id . '">' . $speak->edit . '</a> / <a href="' . $config->url . '/' . $config->manager->slug . '/page/kill/id:' . $page->id . '">' . $speak->delete . '</a>';
    }
}, 20);


/**
 * Add Default Comment Footer Links
 * --------------------------------
 */

Weapon::add('comment_footer', function($comment, $article) {
    $config = Config::get();
    $speak = Config::speak();
    echo '<a href="' . $config->url . '/' . $config->manager->slug . '/comment/repair/id:' . $comment->id . '">' . $speak->edit . '</a> / <a href="' . $config->url . '/' . $config->manager->slug . '/comment/kill/id:' . $comment->id . '">' . $speak->delete . '</a>';
}, 20);


/**
 * Configuration Manager
 * ---------------------
 */

Route::accept($config->manager->slug . '/config', function() use($config, $speak) {

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->config . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'config.php'
    ));

    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    if($request = Request::post()) {

        Guardian::checkToken($request['token']);

        // Fixes for checkbox input
        $request['widget_year_first'] = Request::post('widget_year_first') ? true : false;
        $request['comments'] = Request::post('comments') ? true : false;
        $request['email_notification'] = Request::post('email_notification') ? true : false;
        $request['resource_versioning'] = Request::post('resource_versioning') ? true : false;

        // Fixes for slug pattern input
        $request['index']['slug'] = Text::parse(Request::post('index.slug', 'article'))->to_slug;
        $request['tag']['slug'] = Text::parse(Request::post('tag.slug', 'tagged'))->to_slug;
        $request['archive']['slug'] = Text::parse(Request::post('archive.slug', 'archive'))->to_slug;
        $request['search']['slug'] = Text::parse(Request::post('search.slug', 'search'))->to_slug;
        $request['manager']['slug'] = Text::parse(Request::post('manager.slug', 'manager'))->to_slug;

        if( // If ...

            // Should be greater than 0
            (int) Request::post('per_page') < 1 ||
            (int) Request::post('index.per_page') < 1 ||
            (int) Request::post('tag.per_page') < 1 ||
            (int) Request::post('archive.per_page') < 1 ||
            (int) Request::post('search.per_page') < 1 ||
            (int) Request::post('manager.per_page') < 1 ||

            // Should be a fixed number
            floor(Request::post('per_page')) != Request::post('per_page') ||
            floor(Request::post('index.per_page')) != Request::post('index.per_page') ||
            floor(Request::post('tag.per_page')) != Request::post('tag.per_page') ||
            floor(Request::post('archive.per_page')) != Request::post('archive.per_page') ||
            floor(Request::post('search.per_page')) != Request::post('search.per_page') ||
            floor(Request::post('manager.per_page')) != Request::post('manager.per_page')

        ) {
            Notify::error($speak->notify_invalid_per_page_number);
            Guardian::memorize($request);
        }

        // Check if slug already exist on static pages
        $slugs = array();
        if($files = Get::pages()) {
            foreach($files as $file) {
                list($time, $kind, $slug) = explode('_', basename($file, '.txt'));
                $slugs[$slug] = 1;
            }
        }

        if(isset($slugs[$request['index']['slug']])) {
            Notify::error(Config::speak('notify_error_slug_exist', array($request['index']['slug'])));
            Guardian::memorize($request);
        }

        if(isset($slugs[$request['tag']['slug']])) {
            Notify::error(Config::speak('notify_error_slug_exist', array($request['tag']['slug'])));
            Guardian::memorize($request);
        }

        if(isset($slugs[$request['archive']['slug']])) {
            Notify::error(Config::speak('notify_error_slug_exist', array($request['archive']['slug'])));
            Guardian::memorize($request);
        }

        if(isset($slugs[$request['search']['slug']])) {
            Notify::error(Config::speak('notify_error_slug_exist', array($request['search']['slug'])));
            Guardian::memorize($request);
        }

        if(isset($slugs[$request['manager']['slug']])) {
            Notify::error(Config::speak('notify_error_slug_exist', array($request['manager']['slug'])));
            Guardian::memorize($request);
        }

        // Checks for invalid email address
        if( ! empty($request['author_email']) && ! Guardian::check($request['author_email'])->this_is_email) {
            Notify::error($speak->notify_invalid_email);
            Guardian::memorize($request);
        }

        unset($request['token']); // Remove token from request array

        $info = array(
            'data' => $request,
            'execution_time' => time(),
            'error' => Notify::errors()
        );

        if( ! Notify::errors()) {
            File::write(serialize($request))->saveTo(STATE . DS . 'config.txt');
            Config::load(); // Refresh the configuration data ...
            Notify::success(Config::speak('notify_success_updated', array(Config::speak('config'))));
            Weapon::fire('on_config_update', array($info));
            Guardian::kick($request['manager']['slug'] . '/config');
        }

    } else {

        Guardian::memorize(Config::get());

    }

    Shield::attach('manager', false);

});


/**
 * Article and Page Manager
 * ------------------------
 */

Route::accept(array($config->manager->slug . '/(article|page)', $config->manager->slug . '/(article|page)/(:num)'), function($path = "", $offset = 1) use($config, $speak) {

    $pages = array();

    if($files = Mecha::eat($path == 'article' ? Get::articles() : Get::pages())->chunk($offset, $config->per_page)->vomit()) {
        foreach($files as $file_path) {
            $pages[] = $path == 'article' ? Get::articleHeader($file_path) : Get::pageHeader($file_path);
        }
    } else {
        if($offset !== 1) {
            Shield::abort();
        } else {
            $pages = false;
        }
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => ($path == 'article' ? $speak->articles : $speak->pages) . $config->title_separator . $config->manager->title,
        'offset' => $offset,
        'pages' => $pages,
        'pagination' => Navigator::extract(($path == 'article' ? Get::articles() : Get::pages()), $offset, $config->per_page, $config->manager->slug . '/' . $path),
        'cargo' => DECK . DS . 'workers' . DS . 'page.php',
        'editor_type' => $path
    ));

    Shield::attach('manager', false);

});


/**
 * Article/Page Composer/Updater
 * -----------------------------
 */

Route::accept(array($config->manager->slug . '/(article|page)/ignite', $config->manager->slug . '/(article|page)/repair/id:(:num)'), function($path = "", $id = "") use($config, $speak) {

    Weapon::add('sword_after', function() {
        echo Asset::script('manager/sword/editor.js');
    }, 11);

    Config::set(array(
        'page_type' => 'manager',
        'cargo' => DECK . DS . 'workers' . DS . 'compose.php',
        'editor_mode' => $config->url_current == $config->url . '/' . $config->manager->slug . '/' . $path . '/ignite' ? 'ignite' : 'repair',
        'editor_type' => $path
    ));

    // Set default fields value ...
    if($page = $path == 'article' ? Get::article($id, array('content', 'tags', 'comments')) : Get::page($id, array('content', 'tags', 'comments'))) {

        $fields = array(
            'id' => $id,
            'file_path' => $page->file_path,
            'date' => $page->date->W3C,
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => $page->content_raw,
            'description' => $page->description,
            'tags' => (array) $page->kind,
            'author' => isset($page->author) ? $page->author : Guardian::get('author'),
            'css' => isset($page->css) ? $page->css_raw : "",
            'js' => isset($page->js) ? $page->js_raw : "",
            'fields' => isset($page->fields) ? Mecha::A($page->fields) : array()
        );

        Config::set('page_title', $speak->editing . ': ' . $page->title . $config->title_separator . $config->manager->title);

    } else {

        if(Config::get('editor_mode') == 'repair') {
            Shield::abort(); // file not found!
        }

        $fields = array(
            'id' => "",
            'date' => "",
            'title' => $config->defaults->page_title,
            'slug' => "",
            'content' => $config->defaults->page_content,
            'description' => "",
            'tags' => array(),
            'author' => Guardian::get('author'),
            'css' => $config->defaults->page_custom_css,
            'js' => $config->defaults->page_custom_js,
            'fields' => array()
        );

        Config::set('page_title', ($path == 'article' ? $speak->manager->title_new_article : $speak->manager->title_new_page) . $config->title_separator . $config->manager->title);

    }

    if($request = Request::post()) {

        Guardian::checkToken($request['token']);

        $request['id'] = $fields['id'];

        /**
         * Collect all available slug to prevent duplicate
         */

        $slugs = array();
        if($files = $path == 'article' ? Get::articles() : Get::pages()) {
            foreach($files as $file) {
                list($_time, $_kind, $_slug) = explode('_', basename($file, '.txt'));
                $slugs[$_slug] = 1;
            }
        }

        if($path == 'page') {
            $slugs[$config->index->slug] = 1;
            $slugs[$config->tag->slug] = 1;
            $slugs[$config->archive->slug] = 1;
            $slugs[$config->search->slug] = 1;
            $slugs[$config->manager->slug] = 1;
        }

        /**
         * Set post date by submitted time, or by input value if available
         */

        $date = Request::post('date', date('c'));

        /**
         * General fields
         */

        $title = trim(strip_tags(Request::post('title', $speak->untitled . ' ' . Date::format($date, 'Y/m/d H:i:s')), '<code>,<em>,<i>,<span>'));
        $slug = Text::parse(Request::post('slug', $speak->untitled . '-' . Date::format($date, 'Y-m-d-H-i-s')))->to_slug;
        $content = Request::post('content', "");
        $description = $request['description'];
        $author = strip_tags($request['author']);
        $tags = Request::post('tags', false);
        $css = rtrim(Request::post('css', ""));
        $js = rtrim(Request::post('js', ""));
        $field = Request::post('fields', array());

        /**
         * Handling for page without tags
         */

        if($tags === false) {
            $request['tags'] = array('0');
            $tags = array('0');
        }

        /**
         * Checks for invalid time pattern
         */

        if( ! preg_match('#^[0-9]{4,}\-[0-9]{2}\-[0-9]{2}T[0-9]{2}\:[0-9]{2}\:[0-9]{2}\+[0-9]{2}\:[0-9]{2}$#', $date)) {
            Notify::error($speak->notify_invalid_time_pattern);
            Guardian::memorize($request);
        }

        /**
         * Checks for duplicate slug
         */

        if(Config::get('editor_mode') == 'ignite' && isset($slugs[$slug])) {
            Notify::error(Config::speak('notify_error_slug_exist', array($slug)));
            Guardian::memorize($request);
        }

        /**
         * Slug must contains at least one letter. This validation added to
         * prevent users from inputting a page offset instead of article slug.
         * Because the URL pattern of article's index page is `article/1` and the
         * URL pattern of article's single page is `article/article-slug`
         */

        if( ! preg_match('#[a-z]#i', $slug)) {
            Notify::error($speak->notify_error_slug_missing_letter);
            Guardian::memorize($request);
        }

        /**
         * Checks for empty post content
         */

        if(empty($content)) {
            Notify::error($speak->notify_error_post_content_empty);
            Guardian::memorize($request);
        }

        $data  = 'Title: ' . $title . "\n";
        $data .= ! empty($description) ? 'Description: ' . trim(Text::parse($description)->to_encoded_json) . "\n" : "";
        $data .= 'Author: ' . $author . "\n";
        $data .= ! empty($field) ? 'Fields: ' . Text::parse($field)->to_encoded_json . "\n" : "";
        $data .= "\n" . SEPARATOR . "\n\n" . $content;

        $info = array(
            'data' => array(
                'id' => ! empty($id) ? $id : (int) Date::format($date, 'U'),
                'type' => $path,
                'date' => $date,
                'title' => $title,
                'slug' => $slug,
                'content_raw' => $content,
                'content' => Text::parse($content)->to_html,
                'description' => $description,
                'author' => $author,
                'tags' => Converter::strEval($tags),
                'css' => $css,
                'js' => $js,
                'fields' => Text::parse($field)->to_encoded_json
            ),
            'execution_time' => time(),
            'error' => Notify::errors(),
            'editor_mode' => Config::get('editor_mode')
        );

        /**
         * New article/page
         */

        if(Config::get('editor_mode') == 'ignite') {

            if( ! Notify::errors()) {

                File::write($data)->saveTo(($path == 'article' ? ARTICLE : PAGE) . DS . Date::format($date, 'Y-m-d-H-i-s') . '_' . implode(',', $tags) . '_' . $slug . '.txt');

                if(( ! empty($css) && $css != $config->defaults->page_custom_css) || ( ! empty($js) && $js != $config->defaults->page_custom_js)) {
                    File::write($css . "\n\n" . SEPARATOR . "\n\n" . $js)->saveTo(CUSTOM . DS . Date::format($date, 'Y-m-d-H-i-s') . '.txt');
                }

                Notify::success(Config::speak('notify_success_created', array($title)) . ' <a class="pull-right" href="' . $config->url . '/' . ($path == 'article' ? $config->index->slug . '/' : "") . $slug . '" target="_blank"><i class="fa fa-eye"></i> ' . $speak->view . '</a>');

                Weapon::fire('on_' . $path . '_update', array($info));
                Weapon::fire('on_' . $path . '_construct', array($info));

                Guardian::kick($config->manager->slug . '/' . $path . '/repair/id:' . Date::format($date, 'U'));

            }

        /**
         * Article/page repair
         */

        } else {

            /**
             * Checks for duplicate slug, except for the current old slug.
             * Allow users to change their post slug, but make sure they
             * do not type the slug of another post.
             */

            unset($slugs[$fields['slug']]); // Remove current slug from filter

            if(isset($slugs[$slug])) {
                Notify::error(Config::speak('notify_error_slug_exist', array($slug)));
                Guardian::memorize($request);
            }

            /**
             * Start re-writing ...
             */

            if( ! Notify::errors()) {

                File::open($page->file_path)
                    ->write($data)
                    ->save()
                    ->renameTo(Date::format($date, 'Y-m-d-H-i-s') . '_' . implode(',', $tags) . '_' . $slug . '.txt');

                $custom = CUSTOM . DS . Date::format($fields['date'], 'Y-m-d-H-i-s') . '.txt';

                if(File::exist($custom)) {
                    if(trim(File::open($custom)->read()) === "" || trim(File::open($custom)->read()) === SEPARATOR || (empty($css) && empty($js))) {
                        // Always delete empty custom CSS and JavaScript files ...
                        File::open($custom)->delete();
                    } else {
                        File::open($custom)
                            ->write($css . "\n\n" . SEPARATOR . "\n\n" . $js)
                            ->save()
                            ->renameTo(Date::format($date, 'Y-m-d-H-i-s') . '.txt');
                    }
                } else {
                    if(( ! empty($css) && $css != $config->defaults->page_custom_css) || ( ! empty($js) && $js != $config->defaults->page_custom_js)) {
                        File::write($css . "\n\n" . SEPARATOR . "\n\n" . $js)->saveTo(CUSTOM . DS . Date::format($date, 'Y-m-d-H-i-s') . '.txt');
                    }
                }

                if($page->slug != $slug && $php_file = File::exist(dirname($page->file_path) . DS . $page->slug . '.php')) {
                    File::open($php_file)->renameTo($slug . '.php');
                }

                Notify::success(Config::speak('notify_success_updated', array($title)) . ' <a class="pull-right" href="' . $config->url . '/' . ($path == 'article' ? $config->index->slug . '/' : "") . $slug . '" target="_blank"><i class="fa fa-eye"></i> ' . $speak->view . '</a>');

                Weapon::fire('on_' . $path . '_update', array($info));
                Weapon::fire('on_' . $path . '_repair', array($info));

                // Rename all comment files related to article if article date has been changed
                if(((string) $date !== (string) $fields['date']) && $comments = Get::comments($id)) {
                    foreach($comments as $comment) {
                        $parts = explode('_', $comment['file_name']);
                        $parts[0] = Date::format($date, 'Y-m-d-H-i-s');
                        File::open($comment['file_path'])->renameTo(implode('_', $parts));
                    }
                }

                Guardian::kick($config->manager->slug . '/' . $path . '/repair/id:' . Date::format($date, 'U'));

            }

        }

    } else {

        Guardian::memorize($fields);

    }

    Shield::attach('manager', false);

});


/**
 * Article/Page Killer
 * -------------------
 */

Route::accept($config->manager->slug . '/(article|page)/kill/id:(:num)', function($path = "", $id = "") use($config, $speak) {

    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    if( ! $page = $path == 'article' ? Get::article($id, array('comments')) : Get::page($id, array('comments'))) {
        Shield::abort(); // file not found!
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->deleting . ': ' . $page->title . $config->title_separator . $config->manager->title,
        'page' => $page,
        'editor_type' => $path,
        'cargo' => DECK . DS . 'workers' . DS . 'kill.page.php'
    ));

    $info = array(
        'data' => array(
            'id' => $page->id,
            'type' => $path,
            'date' => $page->date->W3C,
            'title' => $page->title,
            'slug' => $page->slug,
            'content_raw' => $page->content_raw,
            'content' => $page->content,
            'description' => $page->description,
            'author' => $page->author,
            'tags' => (array) $page->kind,
            'css' => $page->css,
            'js' => $page->js,
            'fields' => Text::parse($page->fields)->to_encoded_json
        ),
        'execution_time' => time(),
        'error' => Notify::errors()
    );

    if(Request::post()) {

        Guardian::checkToken(Request::post('token'));

        File::open($page->file_path)->delete();

        // Deleting comments ...
        foreach(Get::comments($id) as $comment) {
            File::open($comment['file_path'])->delete();
        }

        // Deleting custom CSS and JavaScript files ...
        File::open(CUSTOM . DS . Date::format($id, 'Y-m-d-H-i-s') . '.txt')->delete();

        // Deleting custom PHP file of article/page ...
        File::open(dirname($page->file_path) . DS . $page->slug . '.php')->delete();

        Notify::success(Config::speak('notify_success_deleted', array($page->title)));

        Weapon::fire('on_' . $path . '_update', array($info));
        Weapon::fire('on_' . $path . '_destruct', array($info));

        Guardian::kick($config->manager->slug . '/' . $path);

    } else {

        $word = $path == 'article' ? strtolower($speak->article) : strtolower($speak->page);
        Notify::warning($speak->notify_confirm_delete);
        Notify::warning(Config::speak('notify_confirm_delete_page', array($word)));

    }

    Shield::attach('manager', false);

});


/**
 * Tags Manager
 * ------------
 */

Route::accept($config->manager->slug . '/tag', function() use($config, $speak) {

    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->tags . $config->title_separator . $config->manager->title,
        'pages' => Get::rawTags('ASC', 'id'),
        'cargo' => DECK . DS . 'workers' . DS . 'tag.php'
    ));

    Weapon::add('sword_after', function() {
        echo Asset::script('manager/sword/row-tag.js');
        echo '<script>
(function($, base) {
    base.add(\'on_row_increase\', function() {
        $(\'input[name="name[]"]\').each(function() {
            $.slugger($(this), $(this).parent().next().find(\'input\'), \'-\');
        });
    });
    base.fire(\'on_row_increase\');
})(Zepto, DASHBOARD);
</script>';
    }, 11);

    if($request = Request::post()) {

        Guardian::checkToken($request['token']);

        $data = array();

        for($i = 0, $keys = $request['id'], $count = count($keys); $i < $count; ++$i) {
            if( ! empty($request['name'][$i])) {
                $slug = ! empty($request['slug'][$i]) ? $request['slug'][$i] : $request['name'][$i];
                $data[] = array(
                    'id' => (int) $keys[$i],
                    'name' => $request['name'][$i],
                    'slug' => Text::parse($slug)->to_slug,
                    'description' => $request['description'][$i]
                );
            }
        }

        $info = array(
            'data' => $data,
            'execution_time' => time(),
            'error' => Notify::errors()
        );

        File::write(serialize($data))->saveTo(STATE . DS . 'tags.txt');
        Notify::success(Config::speak('notify_success_updated', array($speak->tags)));
        Weapon::fire('on_tag_update', array($info));
        Guardian::kick($config->url_current);

    }

    Shield::attach('manager', false);

});


/**
 * Menu Manager
 * ------------
 */

Route::accept($config->manager->slug . '/menu', function() use($config, $speak) {

    if($file = File::exist(STATE . DS . 'menus.txt')) {
        $menus = File::open($file)->read();
    } else {
        $menus = $speak->home . ": /\n" . $speak->about . ": /about";
    }

    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->menu . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'menu.php'
    ));

    Weapon::add('sword_after', function() {
        echo '<script>
(function($) {
    new MTE($(\'textarea[name="content"]\')[0], {
        tabSize: \'    \',
        toolbar: false
    });
})(Zepto);
</script>';
    }, 11);

    if($request = Request::post()) {

        Guardian::checkToken($request['token']);

        /**
         * Checks for invalid input
         */

        if(preg_match('#(^|\n)(\t| {1,3})(?:[^ ])#', $request['content'])) {
            Notify::error($speak->notify_invalid_indent_character);
            Guardian::memorize($request);
        }

        $info = array(
            'data' => array(
                'content' => trim($request['content'])
            ),
            'execution_time' => time(),
            'error' => Notify::errors()
        );

        if( ! Notify::errors()) {
            File::write($info['data']['content'])->saveTo(STATE . DS . 'menus.txt');
            Notify::success(Config::speak('notify_success_updated', array($speak->menu)));
            Weapon::fire('on_menu_update', array($info));
            Guardian::kick($config->url_current);
        }

    } else {

        Guardian::memorize(array('content' => $menus));

    }

    Shield::attach('manager', false);

});


/**
 * Assets Manager
 * --------------
 */

Route::accept(array($config->manager->slug . '/asset', $config->manager->slug . '/asset/(:num)'), function($offset = 1) use($config, $speak) {

    if(isset($_FILES) && ! empty($_FILES)) {
        Guardian::checkToken(Request::post('token'));
        File::upload($_FILES['file'], ASSET);
        $info = array(
            'data' => $_FILES,
            'execution_time' => time(),
            'error' => Notify::errors()
        );
        Weapon::fire('on_asset_update', array($info));
        Weapon::fire('on_asset_construct', array($info));
    }

    $pages = array();
    $take = Get::files(ASSET, '*', 'DESC', 'last_update');

    if($files = Mecha::eat($take)->chunk($offset, 50)->vomit()) {
        foreach($files as $file) $pages[] = $file;
    } else {
        $pages = false;
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->assets . $config->title_separator . $config->manager->title,
        'pages' => $pages,
        'pagination' => Navigator::extract($take, $offset, 50, $config->manager->slug . '/asset'),
        'cargo' => DECK . DS . 'workers' . DS . 'asset.php'
    ));

    Shield::attach('manager', false);

});


/**
 * Cache Manager
 * -------------
 */

Route::accept(array($config->manager->slug . '/cache', $config->manager->slug . '/cache/(:num)'), function($offset = 1) use($config, $speak) {

    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    $pages = array();
    $take = Get::files(CACHE, '*', 'DESC', 'last_update');

    if($files = Mecha::eat($take)->chunk($offset, 50)->vomit()) {
        foreach($files as $file) $pages[] = $file;
    } else {
        $pages = false;
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->cache . $config->title_separator . $config->manager->title,
        'pages' => $pages,
        'pagination' => Navigator::extract($take, $offset, 50, $config->manager->slug . '/cache'),
        'cargo' => DECK . DS . 'workers' . DS . 'cache.php'
    ));

    Shield::attach('manager', false);

});


/**
 * Asset and Cache Killer
 * ----------------------
 */

Route::accept($config->manager->slug . '/(asset|cache)/kill/files?:(.*?)', function($path = "", $name = "") use($config, $speak) {

    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    if(strpos($name, ',') !== false) {
        $deletes = explode(',', $name);
    } else {
        if( ! File::exist(($path == 'asset' ? ASSET : CACHE) . DS . str_replace(array('\\', '/'), DS, $name))) {
            Shield::abort(); // file not found!
        } else {
            $deletes = array($name);
        }
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->deleting . ': ' . (count($deletes) === 1 ? basename($deletes[0]) : ($path == 'asset' ? $speak->assets : $speak->caches)) . $config->title_separator . $config->manager->title,
        'asset_name' => $deletes,
        'cargo' => DECK . DS . 'workers' . DS . 'kill.asset.php',
        'editor_type' => $path
    ));

    if(Request::post()) {

        Guardian::checkToken(Request::post('token'));

        $info_path = array();
        foreach($deletes as $file_to_delete) {
            $file_path = ($path == 'asset' ? ASSET : CACHE) . DS . str_replace(array('\\', '/'), DS, $file_to_delete);
            $info_path[] = $file_path;
            File::open($file_path)->delete();
        }

        $info = array(
            'data' => array(
                'type' => $path,
                'file' => $info_path[0],
                'files' => $info_path
            ),
            'execution_time' => time(),
            'error' => Notify::errors()
        );

        Notify::success(Config::speak('notify_file_deleted', array('<code>' . implode('</code>, <code>', $deletes) . '</code>')));
        Weapon::fire('on_' . $path . '_update', array($info));
        Weapon::fire('on_' . $path . '_destruct', array($info));
        Guardian::kick($config->manager->slug . '/' . $path);

    } else {

        Notify::warning($speak->notify_confirm_delete);

    }

    Shield::attach('manager', false);

});


/**
 * Asset Renamer
 * -------------
 */

Route::accept($config->manager->slug . '/asset/repair/files?:(.*?)', function($old = "") use($config, $speak) {

    $dirname = rtrim(dirname(str_replace(array('\\', '/'), DS, $old)), '\\/');
    $basename = ltrim(basename($old), '\\/');

    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    if( ! $file = File::exist(ASSET . DS . $dirname . DS . $basename)) {
        Shield::abort(); // file not found!
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->renaming . ': ' . $basename . $config->title_separator . $config->manager->title,
        'asset_name' => $basename,
        'cargo' => DECK . DS . 'workers' . DS . 'repair.asset.php'
    ));

    if(Request::post()) {

        Guardian::checkToken(Request::post('token'));

        // Empty field
        if( ! Request::post('name')) {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->name)));
        } else {
            // Missing file extension
            if( ! preg_match('#^.*?\.(.+?)$#', Request::post('name'))) {
                Notify::error($speak->notify_error_file_extension_missing);
            }
            $name = explode('.', Request::post('name'));
            $parts = array();
            foreach($name as $part) {
                $parts[] = Text::parse($part)->to_slug_moderate;
            }
            $new_name = implode('.', $parts);
            // File name already exist
            if($basename !== $new_name && File::exist(dirname($file) . DS . $new_name)) {
                Notify::error(Config::speak('notify_file_exist', array('<code>' . $new_name . '</code>')));
            }
            $info = array(
                'data' => array(
                    'file_path' => $file,
                    'old_name' => dirname($file) . DS . $basename,
                    'new_name' => dirname($file) . DS . $new_name
                ),
                'execution_time' => time(),
                'error' => Notify::errors()
            );
            if( ! Notify::errors()) {
                File::open($file)->renameTo($new_name);
                Notify::success(Config::speak('notify_file_updated', array('<code>' . $basename . '</code>')));
                Weapon::fire('on_asset_update', array($info));
                Weapon::fire('on_asset_repair', array($info));
                Guardian::kick($config->manager->slug . '/asset');
            }
        }

    } else {

        Guardian::memorize(array('name' => $basename));

    }

    Shield::attach('manager', false);

});


/**
 * Shortcode Manager
 * -----------------
 */

Route::accept($config->manager->slug . '/shortcode', function() use($config, $speak) {

    if($file = File::exist(STATE . DS . 'shortcodes.txt')) {
        $shortcodes = unserialize(File::open($file)->read());
    } else {
        $shortcodes = include STATE . DS . 'repair.shortcodes.php';
    }

    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->shortcodes . $config->title_separator . $config->manager->title,
        'pages' => $shortcodes,
        'cargo' => DECK . DS . 'workers' . DS . 'shortcode.php'
    ));

    if($request = Request::post()) {

        Guardian::checkToken($request['token']);

        $data = array();

        for($i = 0, $keys = $request['keys'], $count = count($keys); $i < $count; ++$i) {
            if( ! empty($keys[$i])) {
                $data[$keys[$i]] = $request['values'][$i];
            }
        }

        $info = array(
            'data' => $data,
            'execution_time' => time(),
            'error' => Notify::errors()
        );

        File::write(serialize($data))->saveTo(STATE . DS . 'shortcodes.txt');
        Notify::success(Config::speak('notify_success_updated', array($speak->shortcode)));
        Weapon::fire('on_shortcode_update', array($info));
        Guardian::kick($config->url_current);

    }

    Shield::attach('manager', false);

});


/**
 * Cache Repair
 * ------------
 */

Route::accept($config->manager->slug . '/cache/repair/files?:(:any)', function($name = "") use($config, $speak) {

    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    if( ! $file = File::exist(CACHE . DS . $name)) {
        Shield::abort(); // file not found!
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->editing . ' &ldquo;' . $name . '&rdquo;' . $config->title_separator . $speak->cache . $config->title_separator . $config->manager->title,
        'cache_name' => $name,
        'cargo' => DECK . DS . 'workers' . DS . 'repair.cache.php'
    ));

    if($request = Request::post()) {

        Guardian::checkToken($request['token']);

        $info = array(
            'data' => array(
                'file_path' => $file,
                'content' => $request['content']
            ),
            'execution_time' => time(),
            'error' => Notify::errors()
        );

        File::open($file)->write($request['content'])->save();
        Notify::success(Config::speak('notify_success_updated', array($speak->cache)));
        Weapon::fire('on_cache_update', array($info));
        Weapon::fire('on_cache_repair', array($info));
        Guardian::kick($config->manager->slug . '/cache/repair/file:' . $name);

    } else {

        Guardian::memorize(array('content' => File::open($file)->read()));

    }

    Shield::attach('manager', false);

});


/**
 * Multiple Asset/Cache Killer
 * ---------------------------
 */

Route::accept($config->manager->slug . '/(asset|cache)/kill', function($path = "") use($config, $speak) {

    if(Request::post()) {

        Guardian::checkToken(Request::post('token'));

        if( ! Request::post('selected')) {
            Notify::error($speak->notify_error_no_files_selected);
            Guardian::kick($config->manager->slug . '/' . $path);
        }

        Guardian::kick($config->manager->slug . '/' . $path . '/kill/files:' . implode(',', Request::post('selected')));

    }

});


/**
 * Comments Manager
 * ----------------
 */

Route::accept(array($config->manager->slug . '/comment', $config->manager->slug . '/comment/(:num)'), function($offset = 1) use($config, $speak) {

    File::write($config->total_comments)->saveTo(SYSTEM . DS . 'log' . DS . 'comments.total.txt', 0600);

    $pages = array();

    if($files = Mecha::eat(Get::comments(null, 'DESC'))->chunk($offset, $config->per_page)->vomit()) {
        foreach($files as $comment) {
            $pages[] = Get::comment($comment['id']);
        }
    } else {
        $pages = false;
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->comments . $config->title_separator . $config->manager->title,
        'offset' => $offset,
        'pages' => $pages,
        'pagination' => Navigator::extract(Get::comments(), $offset, $config->per_page, $config->manager->slug . '/comment'),
        'cargo' => DECK . DS . 'workers' . DS . 'comment.php'
    ));

    Shield::attach('manager', false);

});


/**
 * Comment Killer
 * --------------
 */

Route::accept($config->manager->slug . '/comment/kill/id:(:num)', function($id = "") use($config, $speak) {

    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    if( ! $comment = Get::comment($id)) {
        Shield::abort(); // file not found!
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->deleting . ': ' . $speak->comment . $config->title_separator . $config->manager->title,
        'page' => $comment,
        'cargo' => DECK . DS . 'workers' . DS . 'kill.comment.php'
    ));

    if(Request::post()) {

        Guardian::checkToken(Request::post('token'));

        $info = array(
            'data' => Mecha::A($comment),
            'execution_time' => time(),
            'error' => Notify::errors()
        );

        File::open($comment->file_path)->delete();
        File::write($config->total_comments)->saveTo(SYSTEM . DS . 'log' . DS . 'comments.total.txt', 0600); 
        Notify::success(Config::speak('notify_success_deleted', array($speak->comment)));
        Weapon::fire('on_comment_update', array($info));
        Weapon::fire('on_comment_destruct', array($info));
        Guardian::kick($config->manager->slug . '/comment');

    } else {

        Notify::warning($speak->notify_confirm_delete);
    
    }

    Shield::attach('manager', false);

});


/**
 * Comment Repair
 * --------------
 */

Route::accept($config->manager->slug . '/comment/repair/id:(:num)', function($id = "") use($config, $speak) {

    if( ! $comment = Get::comment($id)) {
        Shield::abort(); // file not found!
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->editing . ': ' . $speak->comment . $config->title_separator . $config->manager->title,
        'page' => $comment,
        'cargo' => DECK . DS . 'workers' . DS . 'repair.comment.php'
    ));

    Weapon::add('sword_after', function() {
        echo '<script>
(function($) {
    var $area = $(\'textarea[name="message"]\'), languages = $area.data(\'mteLanguages\');
    new MTE($area[0], {
        tabSize: \'    \',
        shortcut: true,
        buttons: languages.buttons,
        prompt: languages.prompt,
        placeholder: languages.placeholder
    });
})(Zepto);
</script>';
    }, 11);

    if($request = Request::post()) {

        $request['id'] = $id;
        $request['message_raw'] = $request['message'];

        Guardian::checkToken($request['token']);

        if(empty($request['name'])) {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->comment_name)));
            Guardian::memorize($request);
        }

        if( ! empty($request['email']) && ! Guardian::check($request['email'])->this_is_email) {
            Notify::error($speak->notify_invalid_email);
            Guardian::memorize($request);
        }

        $info = array(
            'data' => $request,
            'execution_time' => time(),
            'error' => Notify::errors()
        );

        $info['data']['url'] = Request::post('url', '#');

        if( ! Notify::errors()) {

            $data  = 'Name: ' . $request['name'] . "\n";
            $data .= 'Email: ' . Text::parse($request['email'])->to_ascii . "\n";
            $data .= 'URL: ' . Request::post('url', '#') . "\n";
            $data .= 'Status: ' . $request['status'] . "\n";
            $data .= "\n" . SEPARATOR . "\n\n" . $request['message'];

            File::open($comment->file_path)->write($data)->save();

            Notify::success(Config::speak('notify_success_updated', array($speak->comment)));
            Weapon::fire('on_comment_update', array($info));
            Weapon::fire('on_comment_repair', array($info));
            Guardian::kick($config->manager->slug . '/comment/repair/id:' . $id);

        }

    } else {

        Guardian::memorize($comment);

    }

    Shield::attach('manager', false);

});


/**
 * Fields Manager
 * --------------
 */

Route::accept($config->manager->slug . '/field', function() use($config, $speak) {

    if($file = File::exist(STATE . DS . 'fields.txt')) {
        $fields = unserialize(File::open($file)->read());
        ksort($fields);
    } else {
        $fields = array();
    }

    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->fields . $config->title_separator . $config->manager->title,
        'pages' => ! empty($fields) ? $fields : false,
        'cargo' => DECK . DS . 'workers' . DS . 'field.php'
    ));

    Shield::attach('manager', false);

});


/**
 * Field Repair/Igniter
 * --------------------
 */

Route::accept(array($config->manager->slug . '/field/ignite', $config->manager->slug . '/field/repair/key:(:any)'), function($key = false) use($config, $speak) {

    if($file = File::exist(STATE . DS . 'fields.txt')) {
        $fields = unserialize(File::open($file)->read());
    } else {
        $fields = array();
    }

    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    if($key === false) {

        $data = array(
            'title' => "",
            'type' => "",
            'value' => "",
            'scope' => 'article'
        );

        Config::set('page_title', $speak->manager->title_new_field . $config->title_separator . $config->manager->title);

    } else {

        if( ! isset($fields[$key])) {
            Shield::abort();
        }

        $data = $fields[$key];

        Config::set('page_title', $speak->editing . ': ' . $data['title'] . $config->title_separator . $config->manager->title);

    }

    Config::set(array(
        'page_type' => 'manager',
        'page' => $data,
        'key' => $key,
        'cargo' => DECK . DS . 'workers' . DS . 'repair.field.php'
    ));

    Weapon::add('sword_after', function() {
        echo '<script>
(function($) {
    $.slugger($(\'input[name="title"]\'), $(\'input[name="key"]\'), \'_\');
})(Zepto);
</script>';
    }, 11);

    if($request = Request::post()) {

        Guardian::checkToken($request['token']);

        if(empty($request['title'])) {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->title)));
        }

        if(empty($request['key'])) {
            $request['key'] = $request['title'];
        }

        $fields[Text::parse(strtolower($request['key']))->to_array_key] = array(
            'title' => $request['title'],
            'type' => $request['type'],
            'value' => $request['value'],
            'scope' => $request['scope']
        );

        $info = array(
            'data' => array(
                'key' => $key,
                'title' => $request['title'],
                'type' => $request['type'],
                'value' => $request['value'],
                'scope' => $request['scope']
            ),
            'execution_time' => time(),
            'error' => Notify::errors()
        );

        if( ! Notify::errors()) {
            File::write(serialize($fields))->saveTo(STATE . DS . 'fields.txt');
            Notify::success(Config::speak('notify_success_' . ($key === false ? 'created' : 'updated'), array($request['title'])));
            Weapon::fire('on_field_update', array($info));
            Weapon::fire('on_field_' . ($key === false ? 'construct' : 'repair'), array($info));
            Guardian::kick($config->manager->slug . '/field');
        }

    }

    Shield::attach('manager', false);

});


/**
 * Field Killer
 * ------------
 */

Route::accept($config->manager->slug . '/field/kill/key:(:any)', function($key = "") use($config, $speak) {

    if($file = File::exist(STATE . DS . 'fields.txt')) {
        $fields = unserialize(File::open($file)->read());
    } else {
        $fields = array();
    }

    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    if( ! isset($fields[$key])) {
        Shield::abort();
    } else {
        $data = $fields[$key];
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->deleting . ': ' . $data['title'] . $config->title_separator . $config->manager->title,
        'page' => $data,
        'key' => $key,
        'cargo' => DECK . DS . 'workers' . DS . 'kill.field.php'
    ));

    if($request = Request::post()) {

        Guardian::checkToken($request['token']);

        $info = array(
            'data' => array(
                'key' => $key,
                'title' => $fields[$key]['title'],
                'type' => $fields[$key]['type'],
                'value' => $fields[$key]['value'],
                'scope' => $fields[$key]['scope']
            ),
            'execution_time' => time(),
            'error' => Notify::errors()
        );

        $deleted_field = $fields[$key]['title'];

        unset($fields[$key]);

        File::write(serialize($fields))->saveTo(STATE . DS . 'fields.txt');

        Notify::success(Config::speak('notify_success_deleted', array($deleted_field)));
        Weapon::fire('on_field_update', array($info));
        Weapon::fire('on_field_destruct', array($info));
        Guardian::kick($config->manager->slug . '/field');

    } else {

        Notify::warning($speak->notify_confirm_delete);

    }

    Shield::attach('manager', false);

});


/**
 * Shields Manager
 * ---------------
 */

Route::accept($config->manager->slug . '/shield', function() use($config, $speak) {

    if(isset($_FILES) && ! empty($_FILES)) {
        Guardian::checkToken(Request::post('token'));
        $accepted_mimes = array(
            'application/download',
            'application/octet-stream',
            'application/x-compressed',
            'application/x-zip-compressed',
            'application/zip',
            'multipart/x-zip',
        );
        $accepted_extensions = array(
            'zip'
        );
        $name = $_FILES['file']['name'];
        $type = $_FILES['file']['type'];
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $path = basename($name, '.' . $extension);
        if( ! empty($name)) {
            if(File::exist(SHIELD . DS . $path)) {
                Notify::error(Config::speak('notify_file_exist', array('<code>' . $path . '/&hellip;</code>')));
            } else {
                if( ! in_array($type, $accepted_mimes) || ! in_array($extension, $accepted_extensions)) {
                    Notify::error(Config::speak('notify_invalid_file_extension', array('ZIP')));
                }
            }
        } else {
            Notify::error($speak->notify_error_no_file_selected);
        }
        if( ! Notify::errors()) {
            File::upload($_FILES['file'], SHIELD, Config::speak('notify_success_uploaded', array($speak->shield)));
            $info = array(
                'data' => $_FILES,
                'execution_time' => time(),
                'error' => Notify::errors()
            );
            Weapon::fire('on_shield_update', array($info));
            Weapon::fire('on_shield_construct', array($info));
            if($uploaded = File::exist(SHIELD . DS . $name)) {
                Package::take($uploaded)->extract(); // Extract the ZIP file
                File::open($uploaded)->delete(); // Delete the ZIP file
                Guardian::kick($config->manager->slug . '/shield');
            }
        } else {
            Weapon::add('sword_after', function() {
                echo '<script>
(function($) {
    $(\'.tab-area .tab[href$="#tab-content-2"]\').trigger("click");
})(Zepto);
</script>';
            }, 11);
        }
    }

    $pages = array();

    if($files = Get::files(SHIELD . DS . $config->shield, 'css,html,js,php,txt', 'ASC', 'name')) {
        foreach($files as $file) {
            $pages[] = $file;
        }
    } else {
        $pages = false;
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->shields . $config->title_separator . $config->manager->title,
        'pages' => $pages,
        'cargo' => DECK . DS . 'workers' . DS . 'shield.php'
    ));

    Shield::attach('manager', false);

});


/**
 * Shield Repair
 * -------------
 */

Route::accept($config->manager->slug . '/shield/repair/file:(.*?)', function($name = "") use($config, $speak) {

    $name = str_replace('/', DS, $name);

    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    if( ! $file = File::exist(SHIELD . DS . $config->shield . DS . $name)) {
        Shield::abort(); // file not found!
    }

    $info = pathinfo($file);

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->editing . ': ' . $speak->shield . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'repair.shield.php'
    ));

    Weapon::add('sword_after', function() use($info) {
        echo '<script>
(function($) {
    new MTE($(\'textarea[name="content"]\')[0], {
        tabSize: \'' . (strtolower($info['extension']) == 'js' ? '    ' : '  ') . '\',
        toolbar: false
    });
})(Zepto);
</script>';
    }, 11);

    if(Request::post()) {

        Guardian::checkToken(Request::post('token'));

        $info = array(
            'data' => array(
                'file_path' => $file,
                'content' => Request::post('content')
            ),
            'execution_time' => time(),
            'error' => Notify::errors()
        );

        if( ! Notify::errors()) {
            File::open($file)->write($info['data']['content'])->save();
            Notify::success(Config::speak('notify_file_updated', array('<code>' . basename($name) . '</code>')));
            Weapon::fire('on_shield_update', array($info));
            Weapon::fire('on_shield_repair', array($info));
            Guardian::kick($config->url_current);
        }

    } else {

        Guardian::memorize(array(
            'name' => basename($name),
            'content' => File::open($file)->read()
        ));

    }

    Shield::attach('manager', false);

});


/**
 * Empty Plugin Page
 * -----------------
 */

$e_plugin_page = "Title: " . $speak->unknown . "\n" .
     "Author: " . $speak->unknown . "\n" .
     "URL: #\n" .
     "Version: " . $speak->unknown . "\n" .
     "\n" . SEPARATOR . "\n" .
     "\n" . Config::speak('notify_not_available', array($speak->description));


/**
 * Plugins Manager
 * ---------------
 */

Route::accept(array($config->manager->slug . '/plugin', $config->manager->slug . '/plugin/(:num)'), function($offset = 1) use($config, $speak, $e_plugin_page) {

    if(isset($_FILES) && ! empty($_FILES)) {
        Guardian::checkToken(Request::post('token'));
        $accepted_mimes = array(
            'application/download',
            'application/octet-stream',
            'application/x-compressed',
            'application/x-zip-compressed',
            'application/zip',
            'multipart/x-zip',
        );
        $accepted_extensions = array(
            'zip'
        );
        $name = $_FILES['file']['name'];
        $type = $_FILES['file']['type'];
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $path = basename($name, '.' . $extension);
        if( ! empty($name)) {
            if(File::exist(PLUGIN . DS . $path)) {
                Notify::error(Config::speak('notify_file_exist', array('<code>' . $path . '/&hellip;</code>')));
            } else {
                if( ! in_array($type, $accepted_mimes) || ! in_array($extension, $accepted_extensions)) {
                    Notify::error(Config::speak('notify_invalid_file_extension', array('ZIP')));
                }
            }
        } else {
            Notify::error($speak->notify_error_no_file_selected);
        }
        if( ! Notify::errors()) {
            File::upload($_FILES['file'], PLUGIN, Config::speak('notify_success_uploaded', array($speak->plugin)));
            $info = array(
                'data' => $_FILES,
                'execution_time' => time(),
                'error' => Notify::errors()
            );
            Weapon::fire('on_plugin_update', array($info));
            Weapon::fire('on_plugin_construct', array($info));
            Weapon::fire('on_plugin_' . md5($path) . '_update', array($info));
            Weapon::fire('on_plugin_' . md5($path) . '_construct', array($info));
            if($uploaded = File::exist(PLUGIN . DS . $name)) {
                Package::take($uploaded)->extract(); // Extract the ZIP file
                File::open($uploaded)->delete(); // Delete the ZIP file
                if(File::exist(PLUGIN . DS . $path . DS . 'launch.php')) {
                    Weapon::fire('on_plugin_mounted', array($info));
                    Weapon::fire('on_plugin_' . md5($path) . '_mounted', array($info));
                    Guardian::kick($config->manager->slug . '/plugin/' . $path); // Redirect to the plugin manager page
                } else {
                    Guardian::kick($config->manager->slug . '/plugin#plugin:' . $path);
                }
            }
        } else {
            Weapon::add('sword_after', function() {
                echo '<script>
(function($) {
    $(\'.tab-area .tab[href$="#tab-content-2"]\').trigger("click");
})(Zepto);
</script>';
            }, 11);
        }
    }

    $pages = array();
    $take = glob(PLUGIN . DS . '*', GLOB_ONLYDIR);

    if($files = Mecha::eat($take)->chunk($offset, $config->per_page)->vomit()) {
        for($i = 0, $count = count($files); $i < $count; ++$i) {

            // Check whether the localized "about" file is available
            if( ! $file = File::exist($files[$i] . DS . 'about.' . $config->language . '.txt')) {
                $file = $files[$i] . DS . 'about.txt';
            }

            $about = File::exist($file) ? Text::toPage(File::open($file)->read(), true, 'plugin:') : Text::toPage($e_plugin_page, true, 'plugin:');

            $pages[$i]['about'] = $about;
            $pages[$i]['slug'] = basename($files[$i]);

        }
    } else {
        $pages = false;
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->plugins . $config->title_separator . $config->manager->title,
        'pages' => $pages,
        'pagination' => Navigator::extract($take, $offset, $config->per_page, $config->manager->slug . '/plugin'),
        'cargo' => DECK . DS . 'workers' . DS . 'plugin.php'
    ));

    Shield::attach('manager', false);

});


/**
 * Plugin Configuration Page
 * -------------------------
 */

Route::accept($config->manager->slug . '/plugin/(:any)', function($slug = "") use($config, $speak, $e_plugin_page) {

    if( ! File::exist(PLUGIN . DS . $slug . DS . 'launch.php')) {
        Shield::abort();
    }

    // Check whether the localized "about" file is available
    if( ! $file = File::exist(PLUGIN . DS . $slug . DS . 'about.' . $config->language . '.txt')) {
        $file = PLUGIN . DS . $slug . DS . 'about.txt';
    }

    $about = File::exist($file) ? Text::toPage(File::open($file)->read(), true, 'plugin:') : Text::toPage($e_plugin_page, true, 'plugin:');

    if( ! isset($about['url']) && preg_match('#(.*?) *\<(https?\:\/\/)(.*?)\>#i', $about['author'], $matches)) {
        $about['author'] = $matches[1];
        $about['url'] = $matches[2] . $matches[3];
    }

    $about['configurator'] = File::exist(PLUGIN . DS . $slug . DS . 'configurator.php');

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->managing . ': ' . $about['title'] . $config->title_separator . $config->manager->title,
        'page' => $about,
        'cargo' => DECK . DS . 'workers' . DS . 'repair.plugin.php'
    ));

    Shield::attach('manager', false);

});


/**
 * Plugin Freezer/Igniter
 * ----------------------
 */

Route::accept($config->manager->slug . '/plugin/(freeze|fire)/id:(:any)', function($path = "", $slug = "") use($config, $speak) {

    /**
     * Toggle file name from `launch.php` to `pending.php` or ... you know.
     */

    File::open(PLUGIN . DS . $slug . DS . ($path == 'freeze' ? 'launch' : 'pending') . '.php')
        ->renameTo(($path == 'freeze' ? 'pending' : 'launch') . '.php');

    $info = array(
        'data' => array(
            'id' => $slug
        ),
        'execution_time' => time(),
        'error' => Notify::errors()
    );

    $mode = $path == 'freeze' ? 'eject' : 'mounted';

    Notify::success(Config::speak('notify_success_updated', array($speak->plugin)));
    Weapon::fire('on_plugin_update', array($info));
    Weapon::fire('on_plugin_' . $mode, array($info));
    Weapon::fire('on_plugin_' . md5($slug) . '_update', array($info));
    Weapon::fire('on_plugin_' . md5($slug) . '_' . $mode, array($info));
    Guardian::kick($config->manager->slug . '/plugin');

});


/**
 * Plugin Killer
 * -------------
 */

Route::accept($config->manager->slug . '/plugin/kill/id:(:any)', function($slug = "") use($config, $speak, $e_plugin_page) {

    // Check whether the localized "about" file is available
    if( ! $file = File::exist(PLUGIN . DS . $slug . DS . 'about.' . $config->language . '.txt')) {
        $file = PLUGIN . DS . $slug . DS . 'about.txt';
    }

    $about = File::exist($file) ? Text::toPage(File::open($file)->read(), true, 'plugin:') : Text::toPage($e_plugin_page, true, 'plugin:');

    $about['slug'] = $slug;

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->deleting . ': ' . $about['title'] . $config->title_separator . $config->manager->title,
        'page' => $about,
        'cargo' => DECK . DS . 'workers' . DS . 'kill.plugin.php'
    ));

    if(Request::post()) {

        Guardian::checkToken(Request::post('token'));

        File::open(PLUGIN . DS . $slug)->delete();

        $info = array(
            'data' => array(
                'id' => $slug
            ),
            'execution_time' => time(),
            'error' => Notify::errors()
        );

        Notify::success(Config::speak('notify_success_deleted', array($speak->plugin)));
        Weapon::fire('on_plugin_update', array($info));
        Weapon::fire('on_plugin_destruct', array($info));
        Weapon::fire('on_plugin_' . md5($slug) . '_update', array($info));
        Weapon::fire('on_plugin_' . md5($slug) . '_destruct', array($info));
        Guardian::kick($config->manager->slug . '/plugin');

    } else {

        Notify::warning($speak->notify_confirm_delete);

    }

    Shield::attach('manager', false);

});


/**
 * Backup/Restore Manager
 * ----------------------
 */

Route::accept($config->manager->slug . '/(backup|restore)', function() use($config, $speak) {

    if(isset($_FILES) && ! empty($_FILES)) {
        Guardian::checkToken(Request::post('token'));
        $destination = Request::post('destination', ROOT);
        $title = Request::post('title', $speak->files);
        $accepted_mimes = array(
            'application/download',
            'application/octet-stream',
            'application/x-compressed',
            'application/x-zip-compressed',
            'application/zip',
            'multipart/x-zip',
        );
        $accepted_extensions = array(
            'zip'
        );
        $name = $_FILES['file']['name'];
        $type = $_FILES['file']['type'];
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        if( ! empty($name)) {
            if( ! in_array($type, $accepted_mimes) || ! in_array($extension, $accepted_extensions)) {
                Notify::error(Config::speak('notify_invalid_file_extension', array('ZIP')));
            }
        } else {
            Notify::error($speak->notify_error_no_file_selected);
        }
        if( ! Notify::errors()) {
            File::upload($_FILES['file'], $destination, Config::speak('notify_success_uploaded', array($title)));
            $info = array(
                'data' => $_FILES,
                'execution_time' => time(),
                'error' => Notify::errors()
            );
            Weapon::fire('on_restore_construct', array($info));
            if($uploaded = File::exist($destination . DS . $name)) {
                Package::take($uploaded)->extract(); // Extract the ZIP file
                File::open($uploaded)->delete(); // Delete the ZIP file
                Config::load(); // Refresh the configuration data ...
                Guardian::kick(Config::get('manager')->slug . '/backup');
            }
        } else {
            Weapon::add('sword_after', function() {
                echo '<script>
(function($) {
    $(\'.tab-area .tab[href$="#tab-content-2"]\').trigger("click");
})(Zepto);
</script>';
            }, 11);
        }
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->backup . '/' . $speak->restore . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'backup.php'
    ));

    Shield::attach('manager', false);

});


/**
 * Backup Actions
 * --------------
 */

Route::accept($config->manager->slug . '/backup/origin:(:any)', function($origin = "") use($config, $speak) {

    $time = date('Y-m-d-H-i-s');
    $site = Text::parse($config->title)->to_slug;

    if($origin == 'root') {
        $name = $site . '_' . $time . '.zip';
        Package::take(ROOT)->pack(ROOT . DS . $name);
    } else {
        $name = $site . '.' . $origin . '_' . $time . '.zip';
        Package::take(ROOT . DS . 'cabinet' . DS . $origin)->pack(ROOT . DS . $name);
        if($origin == 'states') {
            Package::take(ROOT . DS . $name)->deleteFiles(array(
                'repair.config.php',
                'repair.shortcodes.php',
                'repair.tags.php'
            ));
        }
        if($origin == 'shields') {
            Package::take(ROOT . DS . $name)->deleteFiles(array(
                'rss.php',
                'sitemap.php',
                'widgets.css',
                'widgets.js'
            ));
        }
    }

    $info = array(
        'data' => array(
            'file_path' => ROOT . DS . $name
        ),
        'execution_time' => time(),
        'error' => Notify::errors()
    );

    Weapon::fire('on_backup_construct', array($info));

    Guardian::kick($config->manager->slug . '/backup/send:' . $name);

});


/**
 * Downloading Backup Files
 * ------------------------
 */

Route::accept($config->manager->slug . '/backup/send:(:any)', function($file = "") use($config, $speak) {

    if($backup = File::exist(ROOT . DS . $file)) {
        header('Content-Type: application/zip');
        header('Content-Length: ' . filesize($backup));
        header('Content-Disposition: attachment; filename=' . $file);
        readfile($backup);
        $info = array(
            'data' => array(
                'file_path' => $backup
            ),
            'execution_time' => time(),
            'error' => Notify::errors()
        );
        ignore_user_abort(true);
        File::open($backup)->delete();
        Weapon::fire('on_backup_destruct', array($info));
    } else {
        Shield::abort();
    }

    exit;

});


/**
 * Page/Article Preview
 * --------------------
 */

Route::accept($config->manager->slug . '/(article|page)/preview', function($path = "") {

    $info = array(
        'data' => array(
            'mode' => $path
        ),
        'execution_time' => time(),
        'error' => Notify::errors()
    );

    Weapon::fire('preview_content_before', array($info));
    echo '<div class="inner">';

    if(Request::post()) {
        $title = Request::post('title');
        $title = Filter::apply('title', $title);
        $title = Filter::apply($path . ':title', $title);
        $content = Request::post('content');
        $content = Filter::apply('shortcode', $content);
        $content = Filter::apply($path . ':shortcode', $content);
        $content = Filter::apply('content', Text::parse($content)->to_html);
        $content = Filter::apply($path . ':content', $content);
        echo '<h2 class="preview-title">' . $title . '</h2>';
        echo '<div class="p">' . $content . '</div>';
    }

    echo '</div>';
    Weapon::fire('preview_content_after', array($info));

    exit;

});