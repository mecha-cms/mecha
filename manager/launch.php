<?php


/**
 * Login
 */

Route::accept($config->manager->slug . '/login', function() use($config, $speak) {

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->login . $config->title_separator . $config->manager->title,
        'cargo' => DECK . '/workers/login.php'
    ));

    if(Request::post()) {
        Guardian::authorize()->kick($config->manager->slug . '/article');
    } else {
        Guardian::reject();
    }

    Shield::attach('manager', false);

});


/**
 * Logout
 */

Route::accept($config->manager->slug . '/logout', function() use($config, $speak) {
    Notify::success($speak->logged_out . '.');
    Guardian::reject()->kick($config->manager->slug . '/login');
});


/**
 * Configuration Manager
 */

Route::accept($config->manager->slug . '/config', function() use($config, $speak) {

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->config . $config->title_separator . $config->manager->title,
        'cargo' => DECK . '/workers/config.php'
    ));

    if( ! Guardian::happy() || Guardian::get('status') != 'pilot') {
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

        if( ! empty($request['author_email']) && ! Guardian::check($request['author_email'])->this_is_email) {
            Notify::error($speak->notify_invalid_email);
            Guardian::memorize($request);
        }

        // Remove token from request array
        unset($request['token']);

        if( ! Notify::errors()) {
            File::open(STATE . '/config.txt')->write(serialize($request))->save();
            Weapon::fire('on_config_update');
            Notify::success(Config::speak('notify_success_updated', array($speak->config)));
            Guardian::kick($request['manager']['slug'] . '/config');
        }

    } else {

        Guardian::memorize(Config::get());

    }

    Shield::attach('manager', false);

});


/**
 * Article and Page Manager
 */

Route::accept(array($config->manager->slug . '/(article|page)', $config->manager->slug . '/(article|page)/(:num)'), function($path = "", $offset = 1) use($config, $speak) {

    if( ! Guardian::happy()) {
        Shield::abort();
    }

    $pages = array();

    if($files = Mecha::eat($path == 'article' ? Get::articles() : Get::pages())->chunk($offset, $config->per_page)->vomit()) {
        foreach($files as $file_path) {
            $pages[] = Get::page($file_path, array('content', 'tags', 'css', 'js', 'comments', 'fields'), ($path == 'article' ? ARTICLE : PAGE));
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
        'cargo' => DECK . '/workers/page.php',
        'editor_type' => $path
    ));

    Shield::attach('manager', false);

});


/**
 * Article or Page Composer or Updater
 */

Route::accept(array($config->manager->slug . '/(article|page)/ignite', $config->manager->slug . '/(article|page)/repair/(:num)'), function($path = "", $id = "") use($config, $speak) {

    if( ! Guardian::happy()) {
        Shield::abort();
    }

    Weapon::add('sword_after', function() use($config) {
        echo Asset::script('manager/sword/editor.js');
    }, 9);

    Config::set(array(
        'page_type' => 'manager',
        'editor_mode' => $config->url_current == $config->url . '/' . $config->manager->slug . '/' . $path . '/ignite' ? 'ignite' : 'repair',
        'editor_type' => $path,
        'cargo' => DECK . '/workers/compose.php'
    ));

    // Set default fields value...
    if($page = Get::page($id, array('content', 'tags', 'comments'), ($path == 'article' ? ARTICLE : PAGE))) {

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
            'fields' => isset($page->fields) ? $page->fields : array()
        );

        Config::set('page_title', $speak->editing . ' &ldquo;' . $fields['title'] . '&rdquo;' . $config->title_separator . $config->manager->title);

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

    Guardian::memorize($fields);

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
                $slugs[] = $_slug;
            }
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
        $field = Mecha::O(Request::post('fields', array()));

        /**
         * Handling for page without tags
         */
        if($tags === false) {
            $request['tags'] = array('0');
            $tags = array('0');
        }

        /**
         * Check for invalid time pattern
         */
        if( ! preg_match('#^[0-9]{4,}\-[0-9]{2}\-[0-9]{2}T[0-9]{2}\:[0-9]{2}\:[0-9]{2}\+[0-9]{2}\:[0-9]{2}$#', $date)) {
            Notify::error($speak->notify_invalid_time_pattern);
            Guardian::memorize($request);
        }

        /**
         * Check for duplicate slug
         */
        if(Config::get('editor_mode') == 'ignite' && in_array($slug, $slugs)) {
            Notify::error(Config::speak('notify_error_slug_exist', array($slug)));
            Guardian::memorize($request);
        }

        /**
         * Slug must contain at least one letter. This validation added to
         * prevent the user from inputting a page offset instead of article slug.
         * Because the URL pattern of article's index page is `article/1` and the
         * URL pattern of article's single page is `article/article-slug`
         */
        if( ! preg_match('#[a-z]#i', $slug)) {
            Notify::error($speak->notify_error_slug_missing_letter);
            Guardian::memorize($request);
        }

        /**
         * Check for empty post content
         */
        if(empty($content)) {
            Notify::error($speak->notify_error_post_content_empty);
            Guardian::memorize($request);
        }

        $data  = 'Title: ' . $title . "\n";
        $data .= ! empty($description) ? 'Description: ' . trim(Text::parse($description)->to_encoded_json) . "\n" : "";
        $data .= 'Author: ' . $author . "\n";
        $data .= 'Fields: ' . json_encode($field) . "\n";
        $data .= "\n" . SEPARATOR . "\n\n" . $content;

        /**
         * New article/page
         */
        if(Config::get('editor_mode') == 'ignite') {

            if( ! Notify::errors()) {

                File::write($data)->saveTo(($path == 'article' ? ARTICLE : PAGE) . '/' . Date::format($date, 'Y-m-d-H-i-s') . '_' . implode(',', $tags) . '_' . $slug . '.txt');

                if(( ! empty($css) && $css != $config->defaults->page_custom_css) || ( ! empty($js) && $js != $config->defaults->page_custom_js)) {
                    File::write($css . "\n\n" . SEPARATOR . "\n\n" . $js)->saveTo(CUSTOM . '/' . Date::format($date, 'Y-m-d-H-i-s') . '.txt');
                }

                Notify::success(Config::speak('notify_success_created', array($title)) . ' <a class="pull-right" href="' . $config->url . '/' . ($path == 'article' ? $config->index->slug . '/' : "") . $slug . '" target="_blank"><i class="fa fa-eye"></i> ' . $speak->view . '</a>');

                // Deleting caches ...
                Weapon::fire('on_page_update');

                Guardian::kick($config->manager->slug . '/' . $path . '/repair/' . Date::format($date, 'U'));

            }

        /**
         * Article/page repairs
         */
        } else {

            /**
             * Check for duplicate slug, except for the current old slug.
             * Allow users to change their post slug, but make sure they
             * do not type the slug of another post.
             */
            if(($key = array_search($fields['slug'], $slugs)) !== false) {
                unset($slugs[$key]); // Remove current slug from filter
            }

            if(in_array($slug, $slugs)) {
                Notify::error(Config::speak('notify_error_slug_exist', array($slug)));
                Guardian::memorize($request);
            }

            /**
             * Start re-writing ...
             */
            if( ! Notify::errors()) {

                File::open($page->file_path)->write($data)->save()
                    ->renameTo(Date::format($date, 'Y-m-d-H-i-s') . '_' . implode(',', $tags) . '_' . $slug . '.txt');

                $custom = CUSTOM . '/' . Date::format($fields['date'], 'Y-m-d-H-i-s') . '.txt';

                if(File::exist($custom)) {
                    if(trim(File::open($custom)->read()) === "" || trim(File::open($custom)->read()) === SEPARATOR || (empty($css) && empty($js))) {
                        // Always delete empty custom files ...
                        File::open($custom)->delete();
                    } else {
                        File::open($custom)
                            ->write($css . "\n\n" . SEPARATOR . "\n\n" . $js)
                            ->save()->renameTo(Date::format($date, 'Y-m-d-H-i-s') . '.txt');
                    }
                } else {
                    if(( ! empty($css) && $css != $config->defaults->page_custom_css) || ( ! empty($js) && $js != $config->defaults->page_custom_js)) {
                        File::write($css . "\n\n" . SEPARATOR . "\n\n" . $js)->saveTo(CUSTOM . '/' . Date::format($date, 'Y-m-d-H-i-s') . '.txt');
                    }
                }

                Notify::success(Config::speak('notify_success_updated', array($title)) . ' <a class="pull-right" href="' . $config->url . '/' . ($path == 'article' ? $config->index->slug . '/' : "") . $slug . '" target="_blank"><i class="fa fa-eye"></i> ' . $speak->view . '</a>');

                // Deleting caches ...
                Weapon::fire('on_page_update');

                // Rename comment files if article date has been changed
                if(((string) $date != $fields['date']) && $comments = Get::comments(Date::format($id, 'Y-m-d-H-i-s'))) {
                    foreach($comments as $comment) {
                        $parts = explode('_', $comment['name']);
                        $parts[0] = Date::format($date, 'Y-m-d-H-i-s');
                        File::open($comment['path'])->renameTo(implode('_', $parts));
                    }
                }

                Guardian::kick($config->manager->slug . '/' . $path . '/repair/' . Date::format($date, 'U'));

            }

        }

    }

    Shield::attach('manager', false);

});


/**
 * Page Killer
 */

Route::accept($config->manager->slug . '/(article|page)/kill/(:num)', function($path = "", $id = "") use($config, $speak) {

    if( ! Guardian::happy() || Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    if( ! $page = Get::page($id, array('comments'), ($path == 'article' ? ARTICLE : PAGE))) {
        Shield::abort(); // file not found!
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->deleting . ' &ldquo;' . $page->title . '&rdquo;' . $config->title_separator . $config->manager->title,
        'page' => $page,
        'editor_type' => $path,
        'cargo' => DECK . '/workers/kill.page.php'
    ));

    if(Request::post()) {

        Guardian::checkToken(Request::post('token'));

        File::open($page->file_path)->delete();

        // Deleting comments ...
        foreach(Get::comments(Date::format($id, 'Y-m-d-H-i-s')) as $comment) {
            File::open($comment['path'])->delete();
        }

        // Deleting custom files ...
        File::open(CUSTOM . '/' . Date::format($id, 'Y-m-d-H-i-s') . '.txt')->delete();

        // Deleting caches ...
        Weapon::fire('on_page_update');
        Weapon::fire('on_page_destruct');

        Notify::success(Config::speak('notify_success_deleted', array($page->title)));
        Guardian::kick($config->manager->slug . '/' . $path . '/ignite');

    } else {

        $word = $path == 'article' ? strtolower($speak->article) : strtolower($speak->page);
        Notify::warning($speak->notify_confirm_delete);
        Notify::warning(Config::speak('notify_confirm_delete_page', array($word)));

    }

    Shield::attach('manager', false);

});


/**
 * Tags Manager
 */

Route::accept($config->manager->slug . '/tag', function() use($config, $speak) {

    if( ! Guardian::happy() || Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->tags . $config->title_separator . $config->manager->title,
        'cargo' => DECK . '/workers/tag.php'
    ));

    Weapon::add('sword_after', function() {
        echo '<script>
(function($) {
    $(\'input[name="name[]"]\').each(function() {
        $.slugger($(this), $(this).parent().next().find(\'input\'), \'-\');
    });
})(Zepto);
</script>';
    }, 9);

    if($request = Request::post()) {

        Guardian::checkToken(Request::post('token'));

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

        File::open(STATE . '/tags.txt')->write(serialize($data))->save();

        Weapon::fire('on_tag_update');

        Notify::success(Config::speak('notify_success_updated', array($speak->tags)));
        Guardian::kick($config->url_current);

    }

    Shield::attach('manager', false);

});


/**
 * Menu Manager
 */

Route::accept($config->manager->slug . '/menu', function() use($config, $speak) {

    if( ! Guardian::happy() || Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->menu . $config->title_separator . $config->manager->title,
        'cargo' => DECK . '/workers/menu.php'
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
    }, 9);

    Guardian::memorize(array('content' => File::open(STATE . '/menus.txt')->read()));

    if($request = Request::post()) {

        Guardian::checkToken($request['token']);

        /**
         * Checks for invalid input
         */
        if(preg_match('#(^|\n)(\t| {2})(?:[^ ])#', $request['content'])) {
            Notify::error($speak->notify_invalid_indent_character);
            Guardian::memorize($request);
        }

        if( ! Notify::errors()) {
            File::open(STATE . '/menus.txt')->write(trim($request['content']))->save();
            Weapon::fire('on_menu_update');
            Notify::success(Config::speak('notify_success_updated', array($speak->menu)));
            Guardian::kick($config->url_current);
        }

    }

    Shield::attach('manager', false);

});


/**
 * Assets Manager
 */

Route::accept(array($config->manager->slug . '/asset', $config->manager->slug . '/asset/(:num)'), function($offset = 1) use($config, $speak) {

    if( ! Guardian::happy()) {
        Shield::abort();
    }

    Weapon::add('sword_after', function() use($config) {
        echo Asset::script('manager/sword/upload.js');
    }, 9);

    if(isset($_FILES) && ! empty($_FILES)) {
        Guardian::checkToken(Request::post('token'));
        File::upload($_FILES['file'], ASSET);
    }

    $pages = array();
    $take = Get::files(ASSET, '*', 'DESC', 'last_update');

    if($files = Mecha::eat($take)->chunk($offset, 200)->vomit()) {
        foreach($files as $file) $pages[] = $file;
    } else {
        $pages = false;
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->assets . $config->title_separator . $config->manager->title,
        'pages' => $pages,
        'pagination' => Navigator::extract($take, $offset, 200, $config->manager->slug . '/asset'),
        'cargo' => DECK . '/workers/asset.php'
    ));

    Shield::attach('manager', false);

});


/**
 * Asset and Cache Killer
 */

Route::accept($config->manager->slug . '/(asset|cache)/kill/(:any)', function($path = "", $name = "") use($config, $speak) {

    if( ! Guardian::happy() || Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    if(strpos($name, ',') !== false) {
        $deletes = explode(',', $name);
    } else {
        if( ! File::exist(($path == 'asset' ? ASSET : CACHE) . '/' . $name)) {
            Shield::abort(); // file not found!
        } else {
            $deletes = array($name);
        }
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->deleting . ': ' . ($path == 'asset' ? $speak->asset : $speak->cache) . $config->title_separator . $config->manager->title,
        'asset_name' => $deletes,
        'cargo' => DECK . '/workers/kill.asset.php'
    ));

    if(Request::post()) {

        Guardian::checkToken(Request::post('token'));

        foreach($deletes as $file_to_delete) {
            File::open(($path == 'asset' ? ASSET : CACHE) . '/' . $file_to_delete)->delete();
        }

        Weapon::fire('on_' . $path . '_destruct');

        Notify::success(Config::speak('notify_success_deleted', array(implode(', ', $deletes))));
        Guardian::kick($config->manager->slug . '/' . $path);

    } else {

        Notify::warning($speak->notify_confirm_delete);

    }

    Shield::attach('manager', false);

});


/**
 * Asset Renaming
 */

Route::accept($config->manager->slug . '/asset/repair/(:any)', function($old = "") use($config, $speak) {

    if( ! Guardian::happy() || Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    if( ! File::exist(ASSET . '/' . $old)) {
        Shield::abort(); // file not found!
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->rename . ': ' . $old . $config->title_separator . $config->manager->title,
        'asset_name' => $old,
        'cargo' => DECK . '/workers/repair.asset.php'
    ));

    if(Request::post()) {

        Guardian::checkToken(Request::post('token'));

        /**
         * Empty field
         */
        if( ! Request::post('name')) {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->name)));
        } else {
            /**
             * Missing file extension
             */
            if( ! preg_match('#^.*?\..*?$#', Request::post('name'))) {
                Notify::error($speak->notify_error_file_extension_missing);
            }
        }

        $name = explode('.', Request::post('name'));
        $parts = array();
        foreach($name as $part) $parts[] = Text::parse($part)->to_slug_moderate;

        if( ! Notify::errors()) {
            File::open(ASSET . '/' . $old)->renameTo(implode('.', $parts));
            Weapon::fire('on_asset_update');
            Notify::success(Config::speak('notify_success_updated', array($old)));
            Guardian::kick($config->manager->slug . '/asset');
        }

    }

    Shield::attach('manager', false);

});


/**
 * Shortcode Manager
 */

Route::accept($config->manager->slug . '/shortcode', function() use($config, $speak) {

    if( ! Guardian::happy() || Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->shortcodes . $config->title_separator . $config->manager->title,
        'cargo' => DECK . '/workers/shortcode.php'
    ));

    Guardian::memorize(array('content' => unserialize(File::open(STATE . '/shortcodes.txt')->read())));

    if($request = Request::post()) {

        Guardian::checkToken(Request::post('token'));

        $data = array();

        for($i = 0, $keys = $request['keys'], $count = count($keys); $i < $count; ++$i) {
            if( ! empty($keys[$i])) {
                $data[$keys[$i]] = $request['values'][$i];
            }
        }

        File::open(STATE . '/shortcodes.txt')->write(serialize($data))->save();

        Weapon::fire('on_shortcode_update');

        Notify::success(Config::speak('notify_success_updated', array($speak->shortcode)));
        Guardian::kick($config->url_current);

    }

    Shield::attach('manager', false);

});


/**
 * Cache Manager
 */

Route::accept($config->manager->slug . '/cache', function() use($config, $speak) {

    if( ! Guardian::happy() || Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->cache . $config->title_separator . $config->manager->title,
        'cargo' => DECK . '/workers/cache.php'
    ));

    Shield::attach('manager', false);

});


/**
 * Cache Repairs
 */

Route::accept($config->manager->slug . '/cache/repair/(:any)', function($name = "") use($config, $speak) {

    if( ! Guardian::happy() || Guardian::get('status') != 'pilot' || ! File::exist(CACHE . '/' . $name)) {
        Shield::abort();
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->editing . ': ' . $name . $config->title_separator . $speak->cache . $config->title_separator . $config->manager->title,
        'asset_name' => $name,
        'cargo' => DECK . '/workers/repair.cache.php'
    ));

    Guardian::memorize(array('content' => File::open(CACHE . '/' . $name)->read()));

    if($request = Request::post()) {

        Guardian::checkToken(Request::post('token'));

        File::open(CACHE . '/' . $name)->write(Request::post('content'))->save();

        Weapon::fire('on_cache_update');

        Notify::success(Config::speak('notify_success_updated', array($speak->cache)));
        Guardian::kick($config->manager->slug . '/cache');

    }

    Shield::attach('manager', false);

});


/**
 * Multiple Asset/Cache Killer
 */

Route::accept($config->manager->slug . '/(asset|cache)/kill', function($path = "") use($config, $speak) {

    if(Request::post()) {

        Guardian::checkToken(Request::post('token'));

        if( ! Request::post('selected')) {
            Notify::error($speak->notify_error_no_files_selected);
            Guardian::kick($config->manager->slug . '/' . $path);
        }

        Guardian::kick($config->manager->slug . '/' . $path . '/kill/' . implode(',', Request::post('selected')));

    }

});


/**
 * Comments Manager
 */

Route::accept(array($config->manager->slug . '/comment', $config->manager->slug . '/comment/(:num)'), function($offset = 1) use($config, $speak) {

    if( ! Guardian::happy()) {
        Shield::abort();
    }

    Session::set('mecha_total_comments_diff', $config->total_comments);

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
        'cargo' => DECK . '/workers/comment.php'
    ));

    Shield::attach('manager', false);

});


/**
 * Comment Killer
 */

Route::accept($config->manager->slug . '/comment/kill/(:num)', function($id = "") use($config, $speak) {

    if( ! Guardian::happy() || Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    if( ! $comment = Get::comment($id)) {
        Shield::abort(); // file not found!
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->deleting . ': ' . $speak->comment . $config->title_separator . $speak->comment . $config->title_separator . $config->manager->title,
        'page' => $comment,
        'cargo' => DECK . '/workers/kill.comment.php'
    ));

    if(Request::post()) {

        Guardian::checkToken(Request::post('token'));

        File::open($comment->file_path)->delete();

        Weapon::fire('on_comment_update');
        Weapon::fire('on_comment_destruct');

        Notify::success(Config::speak('notify_success_deleted', array($speak->comment)));
        Session::set('mecha_total_comments_diff', $config->total_comments);
        Guardian::kick($config->manager->slug . '/comment');

    } else {

        Notify::warning($speak->notify_confirm_delete);
    
    }

    Shield::attach('manager', false);

});


/**
 * Comment Repairs
 */

Route::accept($config->manager->slug . '/comment/repair/(:num)', function($id = "") use($config, $speak) {

    if( ! $comment = Get::comment($id)) {
        Shield::abort(); // file not found!
    }

    Weapon::add('cargo_after', function() {
        echo '<script>
(function($) {
    new MTE($(\'textarea[name="content"]\')[0]);
})(Zepto);
</script>';
    }, 9);

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->editing . ': ' . $speak->comment . $config->title_separator . $config->manager->title,
        'page' => $comment,
        'cargo' => DECK . '/workers/repair.comment.php'
    ));

    Guardian::memorize($comment);

    if($request = Request::post()) {

        Guardian::checkToken($request['token']);

        if(empty($request['name'])) {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->comment_name)));
        }

        if( ! empty($request['email']) && ! Guardian::check($request['email'])->this_is_email) {
            Notify::error($speak->notify_invalid_email);
            Guardian::memorize();
        }

        if( ! Notify::errors()) {

            $data  = 'Name: ' . $request['name'] . "\n";
            $data .= 'Email: ' . Text::parse($request['email'])->to_ascii . "\n";
            $data .= 'URL: ' . Request::post('url', '#') . "\n";
            $data .= 'Status: ' . $request['status'] . "\n";
            $data .= "\n" . SEPARATOR . "\n\n" . $request['message'];

            File::open($comment->file_path)->write($data)->save();

            Weapon::fire('on_comment_update');

            Notify::success(Config::speak('notify_success_updated', array($speak->comment)));
            Guardian::kick($config->manager->slug . '/comment');

        }

    }

    Shield::attach('manager', false);

});


/**
 * Field Manager
 */

Route::accept($config->manager->slug . '/field', function() use($config, $speak) {

    if( ! Guardian::happy() || Guardian::get('status') != 'pilot') {
        Shield::abort();
    }

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->fields . $config->title_separator . $config->manager->title,
        'cargo' => DECK . '/workers/field.php'
    ));

    Weapon::add('sword_after', function() {
        echo '<script>
(function($) {
    $(\'input[name="title[]"]\').each(function() {
        $.slugger($(this), $(this).parent().next().find(\'input\'), \'_\');
    });
})(Zepto);
</script>';
    }, 9);

    if($request = Request::post()) {

        Guardian::checkToken($request['token']);

        unset($request['token']); // remove token from fields

        $fields = array();

        for($i = 0, $count = count($request['key']); $i < $count; ++$i) {
            if( ! empty($request['key'][$i])) {
                $fields[Text::parse($request['key'][$i])->to_array_key] = array(
                    'title' => $request['title'][$i],
                    'type' => $request['type'][$i]
                );
            }
        }

        File::open(STATE . '/fields.txt')->write(serialize($fields))->save();

        Weapon::fire('on_field_update');

        Notify::success(Config::speak('notify_success_updated', array($speak->fields)));
        Guardian::kick($config->url_current);

    }

    Shield::attach('manager', false);

});


/**
 * Plugins Manager
 */

Route::accept(array($config->manager->slug . '/plugin', $config->manager->slug . '/plugin/(:num)'), function($offset = 1) use($config, $speak) {

    if( ! Guardian::happy()) {
        Shield::abort();
    }

    $pages = array();
    $take = glob(PLUGIN . '/*', GLOB_ONLYDIR);

    if($files = Mecha::eat($take)->chunk($offset, $config->per_page)->vomit()) {
        for($i = 0, $count = count($files); $i < $count; ++$i) {
            $about = File::exist($files[$i] . '/about.txt') ? Text::toPage(File::open($files[$i] . '/about.txt')->read()) : array(
                'title' => $speak->unknown,
                'author' => $speak->unknown,
                'version' => $speak->unknown,
                'content' => Config::speak('notify_not_available', array($speak->description))
            );
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
        'cargo' => DECK . '/workers/plugin.php'
    ));

    Shield::attach('manager', false);

});


/**
 * Plugin Configurator Page
 */

Route::accept($config->manager->slug . '/plugin/(:any)', function($slug = "") use($config, $speak) {

    if( ! Guardian::happy() || ! File::exist(PLUGIN . '/' . $slug . '/launch.php')) {
        Shield::abort();
    }

    $about = File::exist(PLUGIN . '/' . $slug . '/about.txt') ? Text::toPage(File::open(PLUGIN . '/' . $slug . '/about.txt')->read()) : array(
        'title' => $speak->unknown,
        'author' => $speak->unknown,
        'version' => $speak->unknown,
        'content' => Config::speak('notify_not_available', array($speak->description))
    );

    $about['configurator'] = File::exist(PLUGIN . '/' . $slug . '/configurator.php');

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->managing . ': ' . $about['title'] . $config->title_separator . $speak->plugin . $config->title_separator . $config->manager->title,
        'page' => $about,
        'cargo' => DECK . '/workers/repair.plugin.php'
    ));

    Shield::attach('manager', false);

});


/**
 * Plugin Freezer/Igniter
 */

Route::accept($config->manager->slug . '/plugin/(freeze|fire)/(:any)', function($path = "", $slug = "") use($config, $speak) {

    if( ! Guardian::happy()) {
        Shield::abort();
    }

    /**
     * Toggle file naming from `launch.php` to `pending.php` or ... you know.
     */
    File::open(PLUGIN . '/' . $slug . '/' . ($path == 'freeze' ? 'launch' : 'pending') . '.php')
        ->renameTo(($path == 'freeze' ? 'pending' : 'launch') . '.php');

    Weapon::fire('on_plugin_' . ($path == 'freeze' ? 'eject' : 'mounted'));

    Notify::success(Config::speak('notify_success_updated', array($speak->plugin)));
    Guardian::kick($config->manager->slug . '/plugin');

});


/**
 * Plugin Killer
 */

Route::accept($config->manager->slug . '/plugin/kill/(:any)', function($slug = "") use($config, $speak) {

    if( ! Guardian::happy()) {
        Shield::abort();
    }

    $about = File::exist(PLUGIN . '/' . $slug . '/about.txt') ? Text::toPage(File::open(PLUGIN . '/' . $slug . '/about.txt')->read()) : array(
        'title' => $speak->unknown,
        'author' => $speak->unknown,
        'version' => $speak->unknown,
        'content' => Config::speak('notify_not_available', array($speak->description))
    );

    $about['slug'] = $slug;

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->deleting . ' &ldquo;' . $about['title'] . '&rdquo;' . $config->title_separator . $config->manager->title,
        'page' => $about,
        'cargo' => DECK . '/workers/kill.plugin.php'
    ));

    if(Request::post()) {

        Guardian::checkToken(Request::post('token'));

        File::open(PLUGIN . '/' . $slug)->delete();

        Weapon::fire('on_plugin_destruct');

        Notify::success(Config::speak('notify_success_deleted', array($speak->plugin)));
        Guardian::kick($config->manager->slug . '/plugin');

    } else {

        Notify::warning($speak->notify_confirm_delete);

    }

    Shield::attach('manager', false);

});