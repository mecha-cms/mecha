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
        'cargo' => 'cargo.article.php'
    ));
    Shield::lot('segment', 'article')->attach('manager');
});


/**
 * Article Composer/Updater
 * ------------------------
 */

Route::accept(array($config->manager->slug . '/article/ignite', $config->manager->slug . '/article/repair/id:(:num)'), function($id = false) use($config, $speak) {
    Config::set('cargo', 'repair.article.php');
    if($id && $article = Get::article($id, array('content', 'excerpt', 'tags', 'comments'))) {
        $extension_o = $article->state === 'published' ? '.txt' : '.draft';
        if(Guardian::get('status') !== 'pilot' && Guardian::get('author') !== $article->author) {
            Shield::abort();
        }
        if( ! isset($article->link)) $article->link = "";
        if( ! isset($article->fields)) $article->fields = array();
        if( ! isset($article->content_type)) $article->content_type = $config->html_parser;
        if( ! File::exist(CUSTOM . DS . date('Y-m-d-H-i-s', $article->date->unix) . $extension_o)) {
            $article->css_raw = $config->defaults->article_css;
            $article->js_raw = $config->defaults->article_js;
        }
        // Remove automatic article description data from article composer
        $test = explode(SEPARATOR, str_replace("\r", "", file_get_contents($article->path)), 2);
        if(strpos($test[0], "\n" . 'Description' . S . ' ') === false) {
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
            'link' => "",
            'slug' => "",
            'content_raw' => $config->defaults->article_content,
            'content_type' => $config->html_parser,
            'description' => "",
            'kind' => array(),
            'author' => Guardian::get('author'),
            'css_raw' => $config->defaults->article_css,
            'js_raw' => $config->defaults->article_js,
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
        $task_connect = $task_connect_page = $article;
        $task_connect_segment = 'article';
        $task_connect_page_css = $config->defaults->article_css;
        $task_connect_page_js = $config->defaults->article_js;
        include DECK . DS . 'workers' . DS . 'task.field.5.php';
        $extension = $request['action'] === 'publish' ? '.txt' : '.draft';
        $kind = isset($request['kind']) ? $request['kind'] : array(0);
        sort($kind);
        // Check for duplicate slug, except for the current old slug.
        // Allow user(s) to change their post slug, but make sure they
        // do not type the slug of another post.
        if(trim($slug) !== "" && $slug !== $article->slug && $files = Get::articles('DESC', "", 'txt,draft,archive')) {
            foreach($files as $file) {
                if(strpos(File::B($file), '_' . $slug . '.') !== false) {
                    Notify::error(Config::speak('notify_error_slug_exist', $slug));
                    Guardian::memorize($request);
                    break;
                }
            }
            unset($files);
        }
        $P = array('data' => $request, 'action' => $request['action']);
        if( ! Notify::errors()) {
            include DECK . DS . 'workers' . DS . 'task.field.2.php';
            include DECK . DS . 'workers' . DS . 'task.field.1.php';
            include DECK . DS . 'workers' . DS . 'task.field.4.php';
            // Ignite
            if( ! $id) {
                Page::header($header)->content($content)->saveTo(ARTICLE . DS . Date::format($date, 'Y-m-d-H-i-s') . '_' . implode(',', $kind) . '_' . $slug . $extension);
                include DECK . DS . 'workers' . DS . 'task.custom.2.php';
                Notify::success(Config::speak('notify_success_created', $title) . ($extension === '.txt' ? ' <a class="pull-right" href="' . Filter::apply('article:url', Filter::apply('url', $config->url . '/' . $config->index->slug . '/' . $slug)) . '" target="_blank"><i class="fa fa-eye"></i> ' . $speak->view . '</a>' : ""));
                Weapon::fire('on_article_update', array($G, $P));
                Weapon::fire('on_article_construct', array($G, $P));
                Guardian::kick($config->manager->slug . '/article/repair/id:' . Date::format($date, 'U'));
            // Repair
            } else {
                Page::open($article->path)->header($header)->content($content)->save();
                File::open($article->path)->renameTo(Date::format($date, 'Y-m-d-H-i-s') . '_' . implode(',', $kind) . '_' . $slug . $extension);
                include DECK . DS . 'workers' . DS . 'task.custom.1.php';
                if($article->slug !== $slug && $php_file = File::exist(File::D($article->path) . DS . $article->slug . '.php')) {
                    File::open($php_file)->renameTo($slug . '.php');
                }
                Notify::success(Config::speak('notify_success_updated', $title) . ($extension === '.txt' ? ' <a class="pull-right" href="' . Filter::apply('article:url', Filter::apply('url', $config->url . '/' . $config->index->slug . '/' . $slug)) . '" target="_blank"><i class="fa fa-eye"></i> ' . $speak->view . '</a>' : ""));
                Weapon::fire('on_article_update', array($G, $P));
                Weapon::fire('on_article_repair', array($G, $P));
                // Rename all comment file(s) related to article if article date has been changed
                if(((string) $date !== (string) $article->date->W3C) && $comments = Get::comments($id, 'DESC', 'txt,hold')) {
                    foreach($comments as $comment) {
                        $parts = explode('_', File::B($comment));
                        $parts[0] = Date::format($date, 'Y-m-d-H-i-s');
                        File::open($comment)->renameTo(implode('_', $parts));
                    }
                }
                Guardian::kick($config->manager->slug . '/article/repair/id:' . Date::format($date, 'U'));
            }
        }
    }
    Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
        echo Asset::javascript('manager/assets/sword/editor.compose.js', "", 'sword/editor.compose.min.js');
    }, 11);
    Shield::lot(array(
        'segment' => 'article',
        'default' => $article
    ))->attach('manager');
});


/**
 * Article Killer
 * --------------
 */

Route::accept($config->manager->slug . '/article/kill/id:(:num)', function($id = "") use($config, $speak) {
    if( ! $article = Get::article($id, array('comments'))) {
        Shield::abort();
    }
    if(Guardian::get('status') !== 'pilot' && Guardian::get('author') !== $article->author) {
        Shield::abort();
    }
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . $article->title . $config->title_separator . $config->manager->title,
        'article' => $article,
        'cargo' => 'kill.article.php'
    ));
    $G = array('data' => Mecha::A($article));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        File::open($article->path)->delete();
        // Deleting comment(s) ...
        if($comments = Get::comments($id, 'DESC', 'txt,hold')) {
            foreach($comments as $comment) {
                File::open($comment)->delete();
            }
        }
        $task_connect = $article;
        $P = array('data' => $request);
        include DECK . DS . 'workers' . DS . 'task.field.3.php';
        include DECK . DS . 'workers' . DS . 'task.custom.3.php';
        Notify::success(Config::speak('notify_success_deleted', $article->title));
        Weapon::fire('on_article_update', array($G, $G));
        Weapon::fire('on_article_destruct', array($G, $G));
        Guardian::kick($config->manager->slug . '/article');
    } else {
        Notify::warning(Config::speak('notify_confirm_delete_', '<strong>' . $article->title . '</strong>'));
        Notify::warning(Config::speak('notify_confirm_delete_page', strtolower($speak->article)));
    }
    Shield::lot('segment', 'article')->attach('manager');
});