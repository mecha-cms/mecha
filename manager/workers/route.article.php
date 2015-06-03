<?php


/**
 * Article Manager
 * ---------------
 */

Route::accept(array($config->manager->slug . '/article', $config->manager->slug . '/article/(:num)'), function($offset = 1) use($config, $speak) {
    $articles = false;
    $offset = (int) $offset;
    if($files = Mecha::eat(Get::articles('DESC', "", 'txt,draft,archive'))->chunk($offset, $config->manager->per_page)->vomit()) {
        $articles = array();
        foreach($files as $file) {
            $articles[] = Get::articleHeader($file);
        }
        unset($files);
    } else {
        if($offset !== 1) Shield::abort();
    }
    Config::set(array(
        'page_title' => $speak->articles . $config->title_separator . $config->manager->title,
        'offset' => $offset,
        'articles' => $articles,
        'pagination' => Navigator::extract(Get::articles('DESC', "", 'txt,draft,archive'), $offset, $config->manager->per_page, $config->manager->slug . '/article'),
        'cargo' => DECK . DS . 'workers' . DS . 'article.php'
    ));
    Shield::attach('manager', false);
});


/**
 * Article Composer/Updater
 * ------------------------
 */

Route::accept(array($config->manager->slug . '/article/ignite', $config->manager->slug . '/article/repair/id:(:num)'), function($id = false) use($config, $speak) {
    Config::set('cargo', DECK . DS . 'workers' . DS . 'repair.article.php');
    if($id && $article = Get::article($id, array('content', 'excerpt', 'tags', 'comments'))) {
        $extension_o = $article->state == 'published' ? '.txt' : '.draft';
        if(Guardian::get('status') != 'pilot' && Guardian::get('author') != $article->author) {
            Shield::abort();
        }
        if( ! isset($article->fields)) {
            $article->fields = array();
        }
        if( ! isset($article->content_type)) {
            $article->content_type = $config->html_parser;
        }
        if( ! File::exist(CUSTOM . DS . date('Y-m-d-H-i-s', $article->date->unix) . $extension_o)) {
            $article->css_raw = $config->defaults->article_custom_css;
            $article->js_raw = $config->defaults->article_custom_js;
        }
        // Remove automatic article description data from article composer
        $test = explode(SEPARATOR, str_replace("\r", "", file_get_contents($article->path)), 2);
        if(strpos($test[0], "\n" . 'Description: ') === false) {
            $article->description = "";
        }
        unset($test);
        Config::set(array(
            'page_title' => $speak->editing . ': ' . $article->title . $config->title_separator . $config->manager->title,
            'article' => Mecha::A($article)
        ));
    } else {
        if($id !== false) {
            Shield::abort(); // File not found!
        }
        $article = Mecha::O(array(
            'id' => "",
            'path' => "",
            'state' => 'draft',
            'date' => array('W3C' => ""),
            'title' => $config->defaults->article_title,
            'slug' => "",
            'content_raw' => $config->defaults->article_content,
            'content_type' => $config->html_parser,
            'description' => "",
            'kind' => array(),
            'author' => Guardian::get('author'),
            'css_raw' => $config->defaults->article_custom_css,
            'js_raw' => $config->defaults->article_custom_js,
            'fields' => array()
        ));
        Config::set(array(
            'page_title' => Config::speak('manager.title_new_', $speak->article) . $config->title_separator . $config->manager->title,
            'article' => Mecha::A($article)
        ));
    }
    $G = array('data' => Mecha::A($article));
    Config::set('html_parser', $article->content_type);
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // Check for invalid time pattern
        if(isset($request['date']) && trim($request['date']) !== "" && ! preg_match('#^[0-9]{4,}\-[0-9]{2}\-[0-9]{2}T[0-9]{2}\:[0-9]{2}\:[0-9]{2}\+[0-9]{2}\:[0-9]{2}$#', $request['date'])) {
            Notify::error($speak->notify_invalid_time_pattern);
            Guardian::memorize($request);
        }
        $request['id'] = (int) date('U', isset($request['date']) && trim($request['date']) !== "" ? strtotime($request['date']) : time());
        $request['kind'] = isset($request['kind']) ? $request['kind'] : array(0);
        $request['path'] = $article->path;
        $request['state'] = $request['action'] == 'publish' ? 'published' : 'draft';
        $extension = $request['action'] == 'publish' ? '.txt' : '.draft';
        // Set post date by submitted time, or by input value if available
        $date = date('c', $request['id']);
        // General fields
        $title = trim(strip_tags(Request::post('title', $speak->untitled . ' ' . Date::format($date, 'Y/m/d H:i:s')), '<abbr><b><code><del><dfn><em><i><ins><span><strong><sub><sup><time><u><var>'));
        $slug = Text::parse(Request::post('slug', $title), '->slug');
        $slug = $slug == '--' ? Text::parse($title, '->slug') : $slug;
        $content = Request::post('content', "");
        $description = $request['description'];
        $author = strip_tags($request['author']);
        $kinds = $request['kind'];
        $css = trim(Request::post('css', ""));
        $js = trim(Request::post('js', ""));
        $field = Request::post('fields', array());
        // Remove empty field value
        foreach($field as $k => $v) {
            if(
                $v['type'][0] === 't' && $v['value'] === "" ||
                $v['type'][0] === 's' && $v['value'] === "" ||
                $v['type'][0] === 'b' && ! isset($v['value']) ||
                $v['type'][0] === 'o' && ! isset($v['value'])
            ) {
                unset($field[$k]);
            }
        }
        sort($kinds);
        // Slug must contains at least one letter or one `-`. This validation added
        // to prevent users from inputting a page offset instead of article slug.
        // Because the URL pattern of article's index page is `article/1` and the
        // URL pattern of article's single page is `article/article-slug`
        if( ! preg_match('#[a-z\-]#i', $slug)) {
            Notify::error($speak->notify_error_slug_missing_letter);
            Guardian::memorize($request);
        }
        // Check for empty post content
        if(trim($content) === "") {
            Notify::error($speak->notify_error_post_content_empty);
            Guardian::memorize($request);
        }
        $header = array(
            'Title' => $title,
            'Description' => trim($description) !== "" ? Text::parse(trim($description), '->encoded_json') : false,
            'Author' => $author,
            'Content Type' => Request::post('content_type', 'HTML'),
            'Fields' => ! empty($field) ? Text::parse($field, '->encoded_json') : false
        );
        $P = array('data' => $request, 'action' => $request['action']);
        // New
        if( ! $id) {
            // Check for duplicate slug
            if($files = Get::articles('DESC', "", 'txt,draft,archive')) {
                foreach($files as $file) {
                    if(strpos(basename($file), '_' . $slug . '.') !== false) {
                        Notify::error(Config::speak('notify_error_slug_exist', $slug));
                        Guardian::memorize($request);
                        break;
                    }
                }
                unset($files);
            }
            if( ! Notify::errors()) {
                Page::header($header)->content($content)->saveTo(ARTICLE . DS . Date::format($date, 'Y-m-d-H-i-s') . '_' . implode(',', $kinds) . '_' . $slug . $extension);
                if(( ! empty($css) && $css != $config->defaults->article_custom_css) || ( ! empty($js) && $js != $config->defaults->article_custom_js)) {
                    Page::content($css)->content($js)->saveTo(CUSTOM . DS . Date::format($date, 'Y-m-d-H-i-s') . $extension);
                }
                Notify::success(Config::speak('notify_success_created', $title) . ($extension == '.txt' ? ' <a class="pull-right" href="' . $config->url . '/' . $config->index->slug . '/' . $slug . '" target="_blank"><i class="fa fa-eye"></i> ' . $speak->view . '</a>' : ""));
                Weapon::fire('on_article_update', array($G, $P));
                Weapon::fire('on_article_construct', array($G, $P));
                Guardian::kick($config->manager->slug . '/article/repair/id:' . Date::format($date, 'U'));
            }
        // Repair
        } else {
            // Check for duplicate slug, except for the current old slug.
            // Allow users to change their post slug, but make sure they
            // do not type the slug of another post.
            if($files = Get::articles('DESC', "", 'txt,draft,archive') && $slug !== $article->slug) {
                foreach($files as $file) {
                    if(strpos(basename($file), '_' . $slug . '.') !== false) {
                        Notify::error(Config::speak('notify_error_slug_exist', $slug));
                        Guardian::memorize($request);
                        break;
                    }
                }
                unset($files);
            }
            // Start rewriting ...
            if( ! Notify::errors()) {
                Page::open($article->path)->header($header)->content($content)->save();
                File::open($article->path)->renameTo(Date::format($date, 'Y-m-d-H-i-s') . '_' . implode(',', $kinds) . '_' . $slug . $extension);
                $custom_ = CUSTOM . DS . Date::format($article->date->W3C, 'Y-m-d-H-i-s');
                if(File::exist($custom_ . $extension_o)) {
                    if(trim(File::open($custom_ . $extension_o)->read()) === "" || trim(File::open($custom_ . $extension_o)->read()) === SEPARATOR || (empty($css) && empty($js)) || ($css == $config->defaults->article_custom_css && $js == $config->defaults->article_custom_js)) {
                        // Always delete empty custom CSS and JavaScript files ...
                        File::open($custom_ . $extension_o)->delete();
                    } else {
                        Page::content($css)->content($js)->saveTo($custom_ . $extension_o);
                        File::open($custom_ . $extension_o)->renameTo(Date::format($date, 'Y-m-d-H-i-s') . $extension);
                    }
                } else {
                    if(( ! empty($css) && $css != $config->defaults->article_custom_css) || ( ! empty($js) && $js != $config->defaults->article_custom_js)) {
                        Page::content($css)->content($js)->saveTo(CUSTOM . DS . Date::format($date, 'Y-m-d-H-i-s') . $extension_o);
                    }
                }
                if($article->slug != $slug && $php_file = File::exist(dirname($article->path) . DS . $article->slug . '.php')) {
                    File::open($php_file)->renameTo($slug . '.php');
                }
                Notify::success(Config::speak('notify_success_updated', $title) . ($extension == '.txt' ? ' <a class="pull-right" href="' . $config->url . '/' . $config->index->slug . '/' . $slug . '" target="_blank"><i class="fa fa-eye"></i> ' . $speak->view . '</a>' : ""));
                Weapon::fire('on_article_update', array($G, $P));
                Weapon::fire('on_article_repair', array($G, $P));
                // Rename all comment files related to article if article date has been changed
                if(((string) $date !== (string) $article->date->W3C) && $comments = Get::comments($id, 'DESC', 'txt,hold')) {
                    foreach($comments as $comment) {
                        $parts = explode('_', basename($comment));
                        $parts[0] = Date::format($date, 'Y-m-d-H-i-s');
                        File::open($comment)->renameTo(implode('_', $parts));
                    }
                }
                Guardian::kick($config->manager->slug . '/article/repair/id:' . Date::format($date, 'U'));
            }
        }
    }
    Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
        echo Asset::javascript('manager/sword/editor.compose.js', "", 'editor.compose.min.js');
    }, 11);
    Shield::lot('default', $article)->attach('manager', false);
});


/**
 * Article Killer
 * --------------
 */

Route::accept($config->manager->slug . '/article/kill/id:(:num)', function($id = "") use($config, $speak) {
    if( ! $article = Get::article($id, array('comments'))) {
        Shield::abort();
    }
    if(Guardian::get('status') != 'pilot' && Guardian::get('author') != $article->author) {
        Shield::abort();
    }
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . $article->title . $config->title_separator . $config->manager->title,
        'article' => $article,
        'cargo' => DECK . DS . 'workers' . DS . 'kill.article.php'
    ));
    $G = array('data' => Mecha::A($article));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        File::open($article->path)->delete();
        // Deleting comments ...
        if($comments = Get::comments($id, 'DESC', 'txt,hold')) {
            foreach($comments as $comment) {
                File::open($comment)->delete();
            }
        }
        // Deleting custom CSS and JavaScript file of article ...
        File::open(CUSTOM . DS . Date::format($id, 'Y-m-d-H-i-s') . '.txt')->delete();
        File::open(CUSTOM . DS . Date::format($id, 'Y-m-d-H-i-s') . '.draft')->delete();
        // Deleting custom PHP file of article ...
        File::open(dirname($article->path) . DS . $article->slug . '.php')->delete();
        Notify::success(Config::speak('notify_success_deleted', $article->title));
        Weapon::fire('on_article_update', array($G, $G));
        Weapon::fire('on_article_destruct', array($G, $G));
        Guardian::kick($config->manager->slug . '/article');
    } else {
        Notify::warning(Config::speak('notify_confirm_delete_', '<strong>' . $article->title . '</strong>'));
        Notify::warning(Config::speak('notify_confirm_delete_page', strtolower($speak->article)));
    }
    Shield::attach('manager', false);
});