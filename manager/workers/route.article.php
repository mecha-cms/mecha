<?php


/**
 * Article Manager
 * ---------------
 */

Route::accept(array($config->manager->slug . '/article', $config->manager->slug . '/article/(:num)'), function($offset = 1) use($config, $speak) {
    $articles = false;
    if($files = Mecha::eat(Get::articles('DESC', "", 'txt,draft'))->chunk($offset, $config->per_page)->vomit()) {
        $articles = array();
        foreach($files as $file) {
            $articles[] = Get::articleHeader($file);
        }
    } else {
        if($offset !== 1) Shield::abort();
    }
    Config::set(array(
        'page_title' => $speak->articles . $config->title_separator . $config->manager->title,
        'offset' => $offset,
        'articles' => $articles,
        'pagination' => Navigator::extract(Get::articles('DESC', "", 'txt,draft'), $offset, $config->per_page, $config->manager->slug . '/article'),
        'cargo' => DECK . DS . 'workers' . DS . 'article.php'
    ));
    Shield::attach('manager', false);
});


/**
 * Article Composer/Updater
 * ------------------------
 */

Route::accept(array($config->manager->slug . '/article/ignite', $config->manager->slug . '/article/repair/id:(:num)'), function($id = false) use($config, $speak) {
    Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
        echo Asset::javascript('manager/sword/editor.js');
    }, 11);
    Config::set('cargo', DECK . DS . 'workers' . DS . 'repair.article.php');
    if($id && $article = Get::article($id, array('content', 'tags', 'comments'))) {
        $fields = Mecha::A($article);
        $fields['date'] = $article->date->W3C;
        $fields['tags'] = (array) $article->kind;
        $fields['content'] = $fields['content_raw'];
        $fields['css'] = $fields['css_raw'];
        $fields['js'] = $fields['js_raw'];
        if( ! isset($article->fields)) {
            $fields['fields'] = array();
        }
        Config::set('page_title', $speak->editing . ': ' . $article->title . $config->title_separator . $config->manager->title);
    } else {
        if($id !== false) {
            Shield::abort(); // file not found!
        }
        $fields = array(
            'id' => "",
            'path' => "",
            'state' => 'draft',
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
        Config::set('page_title', $speak->manager->title_new_article . $config->title_separator . $config->manager->title);
    }
    $G = array('data' => $fields);
    $G['data']['fields'] = Text::parse($G['data']['fields'])->to_encoded_json;
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $request['id'] = $fields['id'];
        $request['path'] = $fields['path'];
        $request['state'] = $fields['state'];
        $extension = $request['action'] == 'publish' ? '.txt' : '.draft';
        // Collect all available slug to prevent duplicate
        $slugs = array();
        if($files = Get::articles('DESC', "", 'txt,draft')) {
            foreach($files as $file) {
                list($_time, $_kind, $_slug) = explode('_', basename($file, '.' . pathinfo($file, PATHINFO_EXTENSION)));
                $slugs[$_slug] = 1;
            }
        }
        // Set post date by submitted time, or by input value if available
        $date = Request::post('date', date('c'));
        // General fields
        $title = trim(strip_tags(Request::post('title', $speak->untitled . ' ' . Date::format($date, 'Y/m/d H:i:s')), '<code>,<em>,<i>,<span>'));
        $slug = Text::parse(Request::post('slug', $speak->untitled . '-' . Date::format($date, 'Y-m-d-H-i-s')))->to_slug;
        $content = Request::post('content', "");
        $description = $request['description'];
        $author = strip_tags($request['author']);
        $tags = Request::post('tags', false);
        $css = trim(Request::post('css', ""));
        $js = trim(Request::post('js', ""));
        $field = Request::post('fields', array());
        // Handling for page without tags
        if($tags === false) {
            $request['tags'] = array('0');
            $tags = array('0');
        }
        // Checks for invalid time pattern
        if( ! preg_match('#^[0-9]{4,}\-[0-9]{2}\-[0-9]{2}T[0-9]{2}\:[0-9]{2}\:[0-9]{2}\+[0-9]{2}\:[0-9]{2}$#', $date)) {
            Notify::error($speak->notify_invalid_time_pattern);
            Guardian::memorize($request);
        }
        // Checks for duplicate slug
        if( ! $id && isset($slugs[$slug])) {
            Notify::error(Config::speak('notify_error_slug_exist', array($slug)));
            Guardian::memorize($request);
        }
        // Slug must contains at least one letter. This validation added to
        // prevent users from inputting a page offset instead of article slug.
        // Because the URL pattern of article's index page is `article/1` and the
        // URL pattern of article's single page is `article/article-slug`
        if( ! preg_match('#[a-z]#i', $slug)) {
            Notify::error($speak->notify_error_slug_missing_letter);
            Guardian::memorize($request);
        }
        // Checks for empty post content
        if(trim($content) === "") {
            Notify::error($speak->notify_error_post_content_empty);
            Guardian::memorize($request);
        }
        $data  = 'Title: ' . $title . "\n";
        $data .= trim($description) !== "" ? 'Description: ' . trim(Text::parse($description)->to_encoded_json) . "\n" : "";
        $data .= 'Author: ' . $author . "\n";
        $data .= ! empty($field) ? 'Fields: ' . Text::parse($field)->to_encoded_json . "\n" : "";
        $data .= "\n" . SEPARATOR . "\n\n" . $content;
        $P = array(
            'data' => array(
                'id' => $id ? (int) $id : (int) Date::format($date, 'U'),
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
            'action' => $request['action']
        );
        // New
        if( ! $id) {
            if( ! Notify::errors()) {
                File::write($data)->saveTo(ARTICLE . DS . Date::format($date, 'Y-m-d-H-i-s') . '_' . implode(',', $tags) . '_' . $slug . $extension, 0600);
                if(( ! empty($css) && $css != $config->defaults->page_custom_css) || ( ! empty($js) && $js != $config->defaults->page_custom_js)) {
                    File::write($css . "\n\n" . SEPARATOR . "\n\n" . $js)->saveTo(CUSTOM . DS . Date::format($date, 'Y-m-d-H-i-s') . $extension, 0600);
                }
                Notify::success(Config::speak('notify_success_created', array($title)) . ($extension == '.txt' ? ' <a class="pull-right" href="' . $config->url . '/' . $config->index->slug . '/' . $slug . '" target="_blank"><i class="fa fa-eye"></i> ' . $speak->view . '</a>' : ""));
                Weapon::fire('on_article_update', array($G, $P));
                Weapon::fire('on_article_construct', array($G, $P));
                Guardian::kick($config->manager->slug . '/article/repair/id:' . Date::format($date, 'U'));
            }
        // Repair
        } else {
            // Checks for duplicate slug, except for the current old slug.
            // Allow users to change their post slug, but make sure they
            // do not type the slug of another post.
            unset($slugs[$fields['slug']]);
            if(isset($slugs[$slug])) {
                Notify::error(Config::speak('notify_error_slug_exist', array($slug)));
                Guardian::memorize($request);
            }
            // Start rewriting ...
            if( ! Notify::errors()) {
                File::open($article->path)->write($data)->save(0600)->renameTo(Date::format($date, 'Y-m-d-H-i-s') . '_' . implode(',', $tags) . '_' . $slug . $extension);
                $custom = CUSTOM . DS . Date::format($fields['date'], 'Y-m-d-H-i-s') . $extension;
                if(File::exist($custom)) {
                    if(trim(File::open($custom)->read()) === "" || trim(File::open($custom)->read()) === SEPARATOR || (empty($css) && empty($js))) {
                        // Always delete empty custom CSS and JavaScript files ...
                        File::open($custom)->delete();
                    } else {
                        File::open($custom)->write($css . "\n\n" . SEPARATOR . "\n\n" . $js)->save(0600)->renameTo(Date::format($date, 'Y-m-d-H-i-s') . $extension);
                    }
                } else {
                    if(( ! empty($css) && $css != $config->defaults->page_custom_css) || ( ! empty($js) && $js != $config->defaults->page_custom_js)) {
                        File::write($css . "\n\n" . SEPARATOR . "\n\n" . $js)->saveTo(CUSTOM . DS . Date::format($date, 'Y-m-d-H-i-s') . $extension, 0600);
                    }
                }
                if($article->slug != $slug && $php_file = File::exist(dirname($article->path) . DS . $article->slug . '.php')) {
                    File::open($php_file)->renameTo($slug . '.php');
                }
                Notify::success(Config::speak('notify_success_updated', array($title)) . ($extension == '.txt' ? ' <a class="pull-right" href="' . $config->url . '/' . $config->index->slug . '/' . $slug . '" target="_blank"><i class="fa fa-eye"></i> ' . $speak->view . '</a>' : ""));
                Weapon::fire('on_article_update', array($G, $P));
                Weapon::fire('on_article_repair', array($G, $P));
                // Rename all comment files related to article if article date has been changed
                if(((string) $date !== (string) $fields['date']) && $comments = Get::comments($id, 'DESC', 'txt,hold')) {
                    foreach($comments as $comment) {
                        $parts = explode('_', basename($comment));
                        $parts[0] = Date::format($date, 'Y-m-d-H-i-s');
                        File::open($comment)->renameTo(implode('_', $parts));
                    }
                }
                Guardian::kick($config->manager->slug . '/article/repair/id:' . Date::format($date, 'U'));
            }
        }
    } else {
        Guardian::memorize($fields);
    }
    Shield::attach('manager', false);
});


/**
 * Article Killer
 * --------------
 */

Route::accept($config->manager->slug . '/article/kill/id:(:num)', function($id = "") use($config, $speak) {
    if(Guardian::get('status') != 'pilot' || ! $article = Get::article($id, array('comments'))) {
        Shield::abort();
    }
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . $article->title . $config->title_separator . $config->manager->title,
        'article' => $article,
        'cargo' => DECK . DS . 'workers' . DS . 'kill.article.php'
    ));
    $G = array('data' => Mecha::A($article));
    $G['data']['fields'] = Text::parse($G['data']['fields'])->to_encoded_json;
    if(Request::post()) {
        Guardian::checkToken(Request::post('token'));
        File::open($article->path)->delete();
        // Deleting comments ...
        foreach(Get::comments($id, 'DESC', 'txt,hold') as $comment) {
            File::open($comment)->delete();
        }
        // Deleting custom CSS and JavaScript file of article ...
        File::open(CUSTOM . DS . Date::format($id, 'Y-m-d-H-i-s') . '.txt')->delete();
        File::open(CUSTOM . DS . Date::format($id, 'Y-m-d-H-i-s') . '.draft')->delete();
        // Deleting custom PHP file of article ...
        File::open(dirname($article->path) . DS . $article->slug . '.php')->delete();
        Notify::success(Config::speak('notify_success_deleted', array($article->title)));
        Weapon::fire('on_article_update', array($G, $G));
        Weapon::fire('on_article_destruct', array($G, $G));
        Guardian::kick($config->manager->slug . '/article');
    } else {
        Notify::warning($speak->notify_confirm_delete);
        Notify::warning(Config::speak('notify_confirm_delete_page', array($speak->article)));
    }
    Shield::attach('manager', false);
});