<?php

class Get extends __ {

    /**
     * ==========================================================================
     *  GET ALL FILE(S) RECURSIVELY
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    $files = Get::files(
     *        'some/path',
     *        'txt',
     *        'ASC',
     *        'update'
     *    );
     *
     *    $files = Get::files(
     *        'some/path',
     *        'gif,jpg,jpeg,png',
     *        'ASC',
     *        'update'
     *    );
     *
     *    $files = Get::files(
     *        'some/path',
     *        'txt',
     *        'ASC',
     *         null,
     *        'key:path' // output only the `path` data
     *    );
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type    | Desription
     *  ---------- | ------- | --------------------------------------------------
     *  $folder    | string  | Path to folder of file(s) you want to be listed
     *  $e         | string  | The file extension(s)
     *  $order     | string  | Ascending or descending? ASC/DESC?
     *  $sorter    | string  | The key of array item as sorting reference
     *  $filter    | string  | Filter the resulted array by a keyword
     *  $recursive | boolean | Get file(s) from a folder recursively?
     *  ---------- | ------- | --------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function files($folder = ASSET, $e = '*', $order = 'ASC', $sorter = 'path', $filter = "", $recursive = true) {
        $results = array();
        $folder = rtrim(File::path($folder), DS);
        $e = $e ? explode(',', str_replace(' ', "", $e)) : true;
        if($files = File::explore($folder, $recursive, true, false)) {
            if(strpos($filter, 'key:') === 0) {
                $s = explode(' ', substr($filter, 4), 2);
                $output = $s[0];
                $filter = isset($s[1]) ? $s[1] : "";
                $sorter = null;
            } else {
                $output = null;
            }
            foreach($files as $k => $v) {
                $_k = File::B($k);
                $_kk = DS . str_replace($folder . DS, "", $k);
                if( ! $filter || strpos(File::N($k), $filter) !== false) {
                    if($v === 1) {
                        if($e === true || $e === array('*') || Mecha::walk($e)->has(File::E($k))) {
                            $o = File::inspect($k, $output);
                            if(is_null($output)) {
                                $o['is']['hidden'] = File::hidden($_k);
                            }
                            $results[] = $o;
                        }
                    } else {
                        if($e === true || $e === array('/')) {
                            $results[] = File::inspect($k, $output);
                        }
                    }
                }
            }
            unset($files, $_k, $_kk);
            return ! empty($results) ? Mecha::eat($results)->order($order, $sorter)->vomit() : false;
        }
        return false;
    }

    /**
     * ==========================================================================
     *  GET CLOSEST FILE(S) FROM A FOLDER
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    $files = Get::closestFiles(
     *        'some/path',
     *        'txt',
     *        'ASC',
     *        'update'
     *    );
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function closestFiles($folder = ASSET, $e = '*', $order = 'DESC', $sorter = 'path', $filter = "") {
        return self::files($folder, $e, $order, $sorter, $filter, false);
    }

    /**
     * ==========================================================================
     *  GET ALL FOLDER(S) RECURSIVELY
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    $files = Get::folders(
     *        'some/path',
     *        'ASC',
     *        'update'
     *    );
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function folders($folder = ASSET, $order = 'DESC', $sorter = 'path', $filter = "", $recursive = true) {
        return self::files($folder, '/', $order, $sorter, $filter, $recursive);
    }

    /**
     * ==========================================================================
     *  GET CLOSEST FOLDER(S) FROM A FOLDER
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    $files = Get::closestFolders(
     *        'some/path',
     *        'ASC',
     *        'update'
     *    );
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function closestFolders($folder = ASSET, $order = 'DESC', $sorter = 'path', $filter = "") {
        return self::folders($folder, $order, $sorter, $filter, false);
    }

    // Get stored configuration data (internal only)
    public static function state_config($key = null, $fallback = array()) {
        $d = WORKER . DS . 'repair.state.config.php';
        $config = file_exists($d) ? include $d : $fallback;
        if($file = File::exist(STATE . DS . 'config.txt')) {
            Mecha::extend($config, File::open($file)->unserialize());
        }
        $config = Filter::apply('state:config', $config);
        if( ! is_null($key)) {
            return Mecha::GVR($config, $key, $fallback);
        }
        return $config;
    }

    // Get stored custom field data (internal only)
    public static function state_field($key = null, $fallback = array(), $all = true, $scope = null) {
        $config = Config::get();
        $speak = Config::speak();
        $d = WORKER . DS . 'repair.state.field.php';
        $field = file_exists($d) ? include $d : $fallback;
        if($file = File::exist(STATE . DS . 'field.txt')) {
            Mecha::extend($field, File::open($file)->unserialize());
        }
        if($all) {
            if($e = File::exist(SHIELD . DS . $config->shield . DS . 'workers' . DS . 'fields.php')) {
                $field_e = include $e;
                Mecha::extend($field, $field_e);
            }
            foreach(glob(PLUGIN . DS . '*' . DS . '{launch,launch__,__launch}.php', GLOB_NOSORT | GLOB_BRACE) as $active) {
                if($e = File::exist(File::D($active) . DS . 'workers' . DS . 'fields.php')) {
                    $field_e = include $e;
                    Mecha::extend($field, $field_e);
                }
            }
        }
        $field = Converter::strEval($field);
        $v_d = array(
            'title' => "",
            'type' => 'summary',
            'placeholder' => "",
            'value' => "",
            'description' => "",
            'attributes' => array('pattern' => null)
        );
        foreach($field as $k => $v) {
            $field[$k] = array_replace_recursive($v_d, $v);
        }
        // Filter output(s) by `scope`
        $field_alt = array();
        if( ! is_null($scope)) {
            foreach($field as $k => $v) {
                foreach(explode(',', $scope) as $s) {
                    if( ! isset($v['scope']) || strpos(',' . $v['scope'] . ',', ',' . $s . ',') !== false) {
                        $field_alt[$k] = $v;
                    }
                }
            }
            $field = $field_alt;
        }
        unset($field_alt);
        $field = Filter::apply('state:field', $field);
        // Filter output(s) by `key`
        if( ! is_null($key)) {
            return Mecha::GVR($field, $key, $fallback);
        }
        // No filter
        return $field;
    }

    // Get stored menu data (internal only)
    public static function state_menu($key = null, $fallback = array(), $all = true) {
        $config = Config::get();
        $speak = Config::speak();
        $d = WORKER . DS . 'repair.state.menu.php';
        $menu = file_exists($d) ? include $d : $fallback;
        if($file = File::exist(STATE . DS . 'menu.txt')) {
            $m = File::open($file)->read();
            if(strpos($m, 'a:') === 0 && strpos($m, "\n") === false) {
                $file = unserialize($m); // it's serialized
            } else if(strpos($m, '{"') === 0) {
                $file = json_decode($m, true); // it's an encoded JSON
            } else {
                $file = Converter::toArray($m, S, '    '); // YAML-like text format
            }
            Mecha::extend($menu, $file);
        }
        if($all) {
            if($e = File::exist(SHIELD . DS . $config->shield . DS . 'workers' . DS . 'menus.php')) {
                $menu_e = include $e;
                Mecha::extend($menu, $menu_e);
            }
            foreach(glob(PLUGIN . DS . '*' . DS . '{launch,launch__,__launch}.php', GLOB_NOSORT | GLOB_BRACE) as $active) {
                if($e = File::exist(File::D($active) . DS . 'workers' . DS . 'menus.php')) {
                    $menu_e = include $e;
                    Mecha::extend($menu, $menu_e);
                }
            }
        }
        $menu = Filter::apply('state:menu', $menu);
        // Filter output(s) by `key`
        if( ! is_null($key)) {
            return Mecha::GVR($menu, $key, $fallback);
        }
        // No filter
        return $menu;
    }

    // Get stored shortcode data (internal only)
    public static function state_shortcode($key = null, $fallback = array(), $all = true) {
        $config = Config::get();
        $speak = Config::speak();
        $d = WORKER . DS . 'repair.state.shortcode.php';
        $shortcode = file_exists($d) ? include $d : $fallback;
        if($file = File::exist(STATE . DS . 'shortcode.txt')) {
            $file = File::open($file)->unserialize();
            foreach($file as $k => $v) {
                unset($shortcode[$k]);
            }
            $shortcode = array_merge($shortcode, $file);
        }
        if($all) {
            if($e = File::exist(SHIELD . DS . $config->shield . DS . 'workers' . DS . 'shortcodes.php')) {
                $shortcode_e = include $e;
                Mecha::extend($shortcode, $shortcode_e);
            }
            foreach(glob(PLUGIN . DS . '*' . DS . '{launch,launch__,__launch}.php', GLOB_NOSORT | GLOB_BRACE) as $active) {
                if($e = File::exist(File::D($active) . DS . 'workers' . DS . 'shortcodes.php')) {
                    $shortcode_e = include $e;
                    Mecha::extend($shortcode, $shortcode_e);
                }
            }
        }
        $shortcode = Filter::apply('state:shortcode', Converter::strEval($shortcode));
        // Filter output(s) by `key`
        if( ! is_null($key)) {
            return Mecha::GVR($shortcode, $key, $fallback);
        }
        // No filter
        return $shortcode;
    }

    // Get stored tag data (internal only)
    public static function state_tag($id = null, $fallback = array(), $all = true, $scope = null) {
        $config = Config::get();
        $speak = Config::speak();
        $d = WORKER . DS . 'repair.state.tag.php';
        $tag = file_exists($d) ? include $d : $fallback;
        if($file = File::exist(STATE . DS . 'tag.txt')) {
            Mecha::extend($tag, File::open($file)->unserialize());
        }
        if($all) {
            if($e = File::exist(SHIELD . DS . $config->shield . DS . 'workers' . DS . 'tags.php')) {
                $tag_e = include $e;
                Mecha::extend($tag, $tag_e);
            }
            foreach(glob(PLUGIN . DS . '*' . DS . '{launch,launch__,__launch}.php', GLOB_NOSORT | GLOB_BRACE) as $active) {
                if($e = File::exist(File::D($active) . DS . 'workers' . DS . 'tags.php')) {
                    $tag_e = include $e;
                    Mecha::extend($tag, $tag_e);
                }
            }
        }
        // Filter output(s) by `scope`
        $tag_alt = array();
        if( ! is_null($scope)) {
            foreach($tag as $k => $v) {
                foreach(explode(',', $scope) as $s) {
                    if( ! isset($v['scope']) || strpos(',' . $v['scope'] . ',', ',' . $s . ',') !== false) {
                        $tag_alt[$k] = $v;
                    }
                }
            }
            $tag = $tag_alt;
        }
        unset($tag_alt);
        $tag = Filter::apply('state:tag', Converter::strEval($tag));
        // Filter output(s) by `id`
        if( ! is_null($id)) {
            return Mecha::GVR($tag, $id, $fallback);
        }
        // No filter
        return $tag;
    }

    /**
     * ==========================================================================
     *  GET TAG(S)
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    foreach(Get::tags() as $tag) {
     *        echo $tag->name . '<br>';
     *    }
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function tags($order = 'ASC', $sorter = 'name', $scope = null) {
        $tags = self::state_tag(null, array(), true, $scope);
        $results = array();
        foreach($tags as $k => $v) {
            $results[] = (object) array(
                'id' => Filter::colon('tag:id', $k, $tags),
                'name' => Filter::colon('tag:name', $v['name'], $tags),
                'slug' => Filter::colon('tag:slug', $v['slug'], $tags),
                'description' => Filter::colon('tag:description', $v['description'], $tags)
            );
        }
        unset($tags);
        return Mecha::eat($results)->order($order, $sorter)->vomit();
    }

    /**
     * ==========================================================================
     *  RETURN SPECIFIC TAG ITEM FILTERED BY ITS AVAILABLE DATA
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    $tag = Get::tag('lorem-ipsum');
     *    echo $tag->name . '<br>';
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function tag($filter, $output = null, $fallback = false, $scope = null) {
        $tags = self::tags('ASC', 'name', $scope);
        // alternate 2: `Get::tag('id:2', 'slug', false)`
        if(strpos($filter, ':') !== false) {
            list($key, $value) = explode(':', $filter, 2);
            $value = Converter::strEval($value);
            foreach($tags as $k => $v) {
                if(isset($v->{$key}) && $v->{$key} === $value) {
                    return is_null($output) ? $v : (isset($v->{$output}) ? $v->{$output} : $fallback);
                }
            }
        // alternate 1: `Get::tag(2, 'slug', false)
        } else {
            foreach($tags as $k => $v) {
                if(
                    (is_numeric($filter) && (int) $filter === (int) $v->id) || // by ID
                    (string) $filter === (string) $v->slug || // by slug
                    (string) $filter === (string) $v->name // by name
                ) {
                    return is_null($output) ? $v : (isset($v->{$output}) ? $v->{$output} : $fallback);
                }
            }
        }
        return $fallback;
    }

    /**
     * ==========================================================================
     *  GET POST TAG(S)
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    foreach(Get::postTags() as $tag) {
     *        echo $tag->name . '<br>';
     *    }
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function postTags($order = 'ASC', $sorter = 'name') {
        return self::tags($order, $sorter, 'post');
    }

    /**
     * ==========================================================================
     *  RETURN SPECIFIC POST TAG ITEM FILTERED BY ITS AVAILABLE DATA
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    $tag = Get::postTag('lorem-ipsum');
     *    echo $tag->name . '<br>';
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function postTag($filter, $output = null, $fallback = false) {
        return self::tag($filter, $output = null, $fallback = false, 'post');
    }

    /**
     * ==========================================================================
     *  GET POST PATH
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::postPath('lorem-ipsum'));
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type  | Description
     *  --------- | ----- | -----------------------------------------------------
     *  $detector | mixed | Slug, ID or time of the post
     *  --------- | ----- | -----------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function postPath($detector, $folder = POST) {
        foreach(Config::get('__' . File::B($folder) . 's_path', array()) as $path) {
            list($time, $kind, $slug) = explode('_', File::N($path), 3);
            if($slug === $detector || $time === Date::slug($detector)) {
                return $path;
            }
        }
        return false;
    }

    /**
     * ==========================================================================
     *  GET LIST OF POST DETAIL(S)
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::postExtract($input));
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function postExtract($input, $FP = 'post:') {
        if( ! $input) return false;
        $e = File::E($input);
        $update = File::T($input);
        $update_date = ! is_null($update) ? date('Y-m-d H:i:s', $update) : null;
        list($time, $kind, $slug) = explode('_', File::N($input), 3);
        $kind = $kind !== "" ? explode(',', $kind) : array();
        return array(
            'path' => Filter::colon($FP . 'path', $input, $input),
            'id' => Filter::colon($FP . 'id', (int) Date::format($time, 'U'), $input),
            'time' => Filter::colon($FP . 'time', Date::format($time), $input),
            'update_raw' => Filter::colon($FP . 'update_raw', $update, $input),
            'update' => Filter::colon($FP . 'update', $update_date, $input),
            'kind' => Filter::colon($FP . 'kind', Converter::strEval($kind), $input),
            'slug' => Filter::colon($FP . 'slug', $slug, $input),
            'state' => Filter::colon($FP . 'state', Mecha::alter($e, array(
                'txt' => 'published',
                'draft' => 'drafted',
                'archive' => 'archived'
            )), $input)
        );
    }

    /**
     * ==========================================================================
     *  GET LIST OF POST(S) PATH
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    foreach(Get::posts() as $path) {
     *        echo $path . '<br>';
     *    }
     *
     *    // [1]. Filter by Tag(s) ID
     *    Get::posts('DESC', 'kind:2');
     *    Get::posts('DESC', 'kind:2,3,4');
     *
     *    // [2]. Filter by Time
     *    Get::posts('DESC', 'time:2014');
     *    Get::posts('DESC', 'time:2014-11');
     *    Get::posts('DESC', 'time:2014-11-10');
     *
     *    // [3]. Filter by Slug
     *    Get::posts('DESC', 'slug:lorem');
     *    Get::posts('DESC', 'slug:lorem-ipsum');
     *
     *    // [4]. The Old Way(s)
     *    Get::posts('DESC', 'lorem');
     *    Get::posts('DESC', 'lorem-ipsum');
     *
     *    // [5]. The Old Way(s)' Alias
     *    Get::posts('DESC', 'keyword:lorem');
     *    Get::posts('DESC', 'keyword:lorem-ipsum');
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------------------
     *  $order    | string | Ascending or descending? ASC/DESC?
     *  $filter   | string | Filter the resulted array by a keyword
     *  $e        | string | The file extension(s)
     *  $folder   | string | Folder of the post(s)
     *  --------- | ------ | ----------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function posts($order = 'DESC', $filter = "", $e = 'txt', $folder = POST) {
        $results = array();
        $e = str_replace(' ', "", $e);
        $posts = strpos($e, ',') !== false ? glob($folder . DS . '*.{' . $e . '}', GLOB_NOSORT | GLOB_BRACE) : glob($folder . DS . '*.' . $e, GLOB_NOSORT);
        $total_posts = count($posts);
        if( ! is_array($posts) || $total_posts === 0) return false;
        if($order === 'DESC') {
            rsort($posts);
        } else if($order === 'ASC') {
            sort($posts);
        }
        if( ! $filter) return $posts;
        if(strpos($filter, ':') !== false) {
            list($key, $value) = explode(':', $filter, 2);
            if( ! $value) return $posts;
            if($key === 'time') {
                for($i = 0; $i < $total_posts; ++$i) {
                    list($time, $kind, $slug) = explode('_', File::N($posts[$i]), 3);
                    if(strpos($time, $value) === 0) {
                        $results[] = $posts[$i];
                    }
                }
            } else if($key === 'kind') {
                $kinds = explode(',', $value);
                for($i = 0; $i < $total_posts; ++$i) {
                    $name = str_replace('_', ',', File::N($posts[$i]));
                    foreach($kinds as $kind) {
                        if(strpos($name, ',' . $kind . ',') !== false) {
                            $results[] = $posts[$i];
                        }
                    }
                }
            } else if($key === 'slug') {
                for($i = 0; $i < $total_posts; ++$i) {
                    list($time, $kind, $slug) = explode('_', File::N($posts[$i]), 3);
                    if(strpos($slug, $value) !== false) {
                        $results[] = $posts[$i];
                    }
                }
            } else { // if($key === 'keyword') {
                for($i = 0; $i < $total_posts; ++$i) {
                    if(strpos(File::N($posts[$i]), $value) !== false) {
                        $results[] = $posts[$i];
                    }
                }
            }
        } else {
            for($i = 0; $i < $total_posts; ++$i) {
                if(strpos(File::N($posts[$i]), $filter) !== false) {
                    $results[] = $posts[$i];
                }
            }
        }
        unset($posts);
        return ! empty($results) ? $results : false;
    }

    /**
     * ==========================================================================
     *  GET LIST OF POST(S) DETAIL(S)
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    foreach(Get::postsExtract() as $file) {
     *        echo $file['path'] . '<br>';
     *    }
     *
     *    Get::postsExtract('DESC', 'time', 'kind:2');
     *    Get::postsExtract('DESC', 'time', 'kind:2,3,4');
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------------------
     *  $sorter   | string | The key of array item as sorting reference
     *  --------- | ------ | ----------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function postsExtract($order = 'DESC', $sorter = 'time', $filter = "", $e = 'txt', $folder = POST, $FP = 'page:') {
        if($files = self::posts($order, $filter, $e, $folder)) {
            $results = array();
            foreach($files as $file) {
                $results[] = self::postExtract($file, $FP);
            }
            unset($files);
            return ! empty($results) ? Mecha::eat($results)->order($order, $sorter)->vomit() : false;
        }
        return false;
    }

    /**
     * ==========================================================================
     *  GET MINIMUM DATA OF A POST
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::postAnchor('about'));
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type   | Description
     *  ---------- | ------ | ---------------------------------------------------
     *  $path      | string | The URL path of the post file, or a post slug
     *  $folder    | string | Folder of the post(s)
     *  $FP        | string | See `Get::post()`
     *  ---------- | ------ | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function postAnchor($path, $folder = POST, $FP = 'post:') {
        $config = Config::get();
        $connector = $config->index->slug === false ? '/' : '/' . $config->index->slug . '/';
        if(strpos($path, ROOT) === false) {
            $path = self::postPath($path, $folder); // By post slug, ID or time
        }
        if($path && ($buffer = File::open($path)->get(1)) !== false) {
            $results = self::postExtract($path, $FP);
            $parts = explode(S, $buffer, 2);
            $results['url'] = Filter::colon($FP . 'url', $config->url . $connector . $results['slug'], $results);
            $v = isset($parts[1]) ? Converter::DS(trim($parts[1])) : "";
            $v = Filter::colon($FP . 'title_raw', $v, $results);
            $results['title_raw'] = $v;
            $results['title'] = Filter::colon($FP . 'title', $v, $results);
            return Mecha::O($results);
        }
        return false;
    }

    /**
     * ==========================================================================
     *  GET POST HEADER(S) ONLY
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::postHeader('lorem-ipsum'));
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type   | Description
     *  ---------- | ------ | ---------------------------------------------------
     *  $path      | string | The URL path of the post file, or a post slug
     *  $folder    | string | Folder of the post(s)
     *  $FP        | string | See `Get::post()`
     *  ---------- | ------ | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function postHeader($path, $folder = POST, $FP = 'post:') {
        $config = Config::get();
        $connector = $config->index->slug === false ? '/' : '/' . $config->index->slug . '/';
        if(strpos($path, ROOT) === false) {
            $path = self::postPath($path, $folder); // By page slug, ID or time
        }
        if( ! $path) return false;
        $results = self::postExtract($path, $FP);
        $results = $results + Page::text($path, false, $FP, array(
            'link' => "",
            'author' => $config->author->name,
            'description' => "",
            'content_type' => $config->html_parser->active,
            'fields' => array()
        ), $results);
        $results['date'] = Filter::colon($FP . 'date', Date::extract($results['time']), $results);
        $results['url'] = Filter::colon($FP . 'url', $config->url . $connector . $results['slug'], $results);
        self::_fields($results, $FP);
        return Mecha::O($results);
    }

    /**
     * ==========================================================================
     *  EXTRACT POST FILE INTO LIST OF POST DATA FROM ITS PATH/SLUG/ID
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::post('about'));
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type   | Description
     *  ---------- | ------ | ---------------------------------------------------
     *  $path      | mixed  | Slug, ID, path or array of `Get::postExtract()`
     *  $excludes  | array  | Exclude some field(s) from result(s)
     *  $folder    | string | Folder of the post(s)
     *  $FP        | string | Filter prefix for `Page::text()`
     *  ---------- | ------ | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function post($path, $excludes = array(), $folder = POST, $FP = 'post:') {
        $config = Config::get();
        $speak = Config::speak();
        $excludes = array_flip($excludes);
        $connector = $config->index->slug === false ? '/' : '/' . $config->index->slug . '/';
        $results = false;
        if( ! is_array($path)) {
            // By slug => `post-slug` or by ID => `1403355917`
            if(strpos($path, $folder) !== 0) {
                $path = self::postPath($path, $folder);
            }
            // By path => `lot\posts\{$folder}\2014-06-21-20-05-17_1,2,3_page-slug.txt`
            $results = self::postExtract($path, $FP);
        } else {
            // From `Get::postExtract()`
            $results = $path;
        }
        if( ! $results || ! file_exists($results['path'])) return false;
        // RULES: Do not do any tags looping, content parsing
        // and external file requesting if it has been marked as
        // the excluded field(s). For better performance.
        $results = $results + Page::text(file_get_contents($results['path']), isset($excludes['content']) ? false : 'content', $FP, array(
            'link' => "",
            'author' => $config->author->name,
            'description' => "",
            'content_type' => $config->html_parser->active,
            'fields' => array(),
            'content' => "",
            'css' => "",
            'js' => ""
        ), $results);
        $content = $results['content_raw'];
        $time = str_replace(array(' ', ':'), '-', $results['time']);
        $e = File::E($results['path']);
        // Custom post content with PHP file, named as the post slug
        if($php = File::exist(File::D($results['path']) . DS . $results['slug'] . '.php')) {
            ob_start();
            include $php;
            $results['content'] = ob_get_clean();
        }
        $results['date'] = Filter::colon($FP . 'date', Date::extract($results['time']), $results);
        $results['url'] = Filter::colon($FP . 'url', $config->url . $connector . $results['slug'], $results);
        $results['excerpt'] = $more = "";
        if($content !== "") {
            $exc = isset($excludes['content']) && strpos($content, '<!--') !== false ? Page::text(Converter::ES($content), 'content', $FP, array(), $results) : $results;
            $exc = $exc['content'];
            $exc = is_array($exc) ? implode("", $exc) : $exc;
            // Generate fake description data
            if(empty($results['description_raw'])) {
                $results['description'] = Converter::curt($exc, $config->excerpt->length, $config->excerpt->suffix);
            }
            // Manual post excerpt with `<!-- cut+ "Read More" -->`
            if(strpos($exc, '<!-- cut+ ') !== false) {
                preg_match('#<!-- cut\+( +([\'"]?)(.*?)\2)? -->#', $exc, $matches);
                $more = ! empty($matches[3]) ? $matches[3] : $speak->read_more;
                $more = '<p><a class="fi-link" href="' . $results['url'] . '#' . sprintf($config->excerpt->id, $results['id']) . '">' . $more . '</a></p>';
                $exc = preg_replace('#<!-- cut\+( +(.*?))? -->#', '<!-- cut -->', $exc);
            }
            // ... or `<!-- cut -->`
            if(strpos($exc, '<!-- cut -->') !== false) {
                $parts = explode('<!-- cut -->', $exc, 2);
                $results['excerpt'] = Filter::colon($FP . 'excerpt', trim($parts[0]) . $more, $results);
                $results['content'] = trim($parts[0]) . NL . NL . '<span class="fi" id="' . sprintf($config->excerpt->id, $results['id']) . '" aria-hidden="true"></span>' . NL . NL . trim($parts[1]);
            }
        }
        // Post Tag(s)
        if( ! isset($excludes['tags'])) {
            $tags = array();
            foreach($results['kind'] as $id) {
                $tags[] = call_user_func('self::' . rtrim($FP, ':') . 'Tag', 'id:' . $id);
            }
            $results['tags'] = Filter::colon($FP . 'tags', Mecha::eat($tags)->order('ASC', 'name')->vomit(), $results);
        }
        // Post Image(s)
        $results['images'] = Filter::colon($FP . 'images', self::imagesURL($results['content']), $results);
        $results['image'] = Filter::colon($FP . 'image', isset($results['images'][0]) ? $results['images'][0] : Image::placeholder(), $results);
        // Post Field(s)
        if( ! isset($excludes['fields'])) {
            self::_fields($results, $FP);
        }
        // Exclude some field(s) from result(s)
        foreach($results as $key => $value) {
            if(isset($excludes[$key])) {
                unset($results[$key]);
            }
        }
        return Mecha::O($results);
    }

    /**
     * ==========================================================================
     *  GET RESPONSE PATH
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::responsePath(1399334470));
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type  | Description
     *  --------- | ----- | -----------------------------------------------------
     *  $detector | mixed | Slug, ID or time of the page
     *  --------- | ----- | -----------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function responsePath($detector, $folder = RESPONSE) {
        foreach(Config::get('__' . File::B($folder) . 's_path', array()) as $path) {
            list($post, $time, $parent) = explode('_', File::N($path), 3);
            if($time === $detector || (string) $time === Date::slug($detector)) {
                return $path;
            }
        }
        return false;
    }

    /**
     * ==========================================================================
     *  GET LIST OF RESPONSE DETAIL(S)
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::responseExtract($input));
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function responseExtract($input, $FP = 'response:') {
        if( ! $input) return false;
        $e = File::E($input);
        $update = File::T($input);
        $update_date = ! is_null($update) ? date('Y-m-d H:i:s', $update) : null;
        list($post, $id, $parent) = explode('_', File::N($input), 3);
        return array(
            'path' => Filter::colon($FP . 'path', $input, $input),
            'time' => Filter::colon($FP . 'time', Date::format($id), $input),
            'update_raw' => Filter::colon($FP . 'update_raw', $update, $input),
            'update' => Filter::colon($FP . 'update', $update_date, $input),
            'post' => Filter::colon($FP . 'post', (int) Date::format($post, 'U'), $input),
            'id' => Filter::colon($FP . 'id', (int) Date::format($id, 'U'), $input),
            'parent' => Filter::colon($FP . 'parent', $parent === '0000-00-00-00-00-00' ? null : (int) Date::format($parent, 'U'), $input),
            'state' => Filter::colon($FP . 'state', Mecha::alter($e, array(
                'txt' => 'approved',
                'hold' => 'pending'
            )), $input)
        );
    }

    /**
     * ===========================================================================
     *  GET LIST OF RESPONSE(S) PATH
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    foreach(Get::responses() as $path) {
     *        echo $path . '<br>';
     *    }
     *
     *    // [1]. Filter by Post Time
     *    Get::responses('DESC', 'post:2014');
     *    Get::responses('DESC', 'post:2014-04');
     *    Get::responses('DESC', 'post:2014-04-21');
     *
     *    // [2]. Filter by Response Time
     *    Get::responses('DESC', 'time:2014');
     *    Get::responses('DESC', 'time:2014-11');
     *    Get::responses('DESC', 'time:2014-11-10');
     *
     *    // [3]. Filter by Response Parent Time
     *    Get::responses('DESC', 'parent:2014');
     *    Get::responses('DESC', 'parent:2014-04');
     *    Get::responses('DESC', 'parent:2014-04-21');
     *
     * ---------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type    | Description
     *  --------- | ------- | ----------------------------------------------------
     *  $order    | string  | Ascending or descending? ASC/DESC?
     *  $filter   | string  | The result(s) filter
     *  $e        | boolean | The file extension(s)
     *  --------- | ------- | ----------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function responses($order = 'ASC', $filter = "", $e = 'txt', $folder = RESPONSE) {
        $results = array();
        $e = str_replace(' ', "", $e);
        $responses = strpos($e, ',') !== false ? glob($folder . DS . '*.{' . $e . '}', GLOB_NOSORT | GLOB_BRACE) : glob($folder . DS . '*.' . $e, GLOB_NOSORT);
        $total_responses = count($responses);
        if( ! is_array($responses) || $total_responses === 0) return false;
        if($order === 'DESC') {
            rsort($responses);
        } else if($order === 'ASC') {
            sort($responses);
        }
        if( ! $filter) return $responses;
        if(strpos($filter, ':') !== false) {
            list($key, $value) = explode(':', $filter, 2);
            if( ! $value) return $responses;
            if(is_numeric($value)) { // filter by ID
                $value = Date::slug($value);
            }
            if($key === 'post') {
                for($i = 0; $i < $total_responses; ++$i) {
                    list($post, $time, $parent) = explode('_', File::N($responses[$i]), 3);
                    if(strpos($post, $value) === 0) {
                        $results[] = $responses[$i];
                    }
                }
            } else if($key === 'time') {
                for($i = 0; $i < $total_responses; ++$i) {
                    list($post, $time, $parent) = explode('_', File::N($responses[$i]), 3);
                    if(strpos($time, $value) === 0) {
                        $results[] = $responses[$i];
                    }
                }
            } else if($key === 'parent') {
                if(strtolower($value) === 'null') $value = '0000-00-00-00-00-00'; // select response(s) with no parent ID
                for($i = 0; $i < $total_responses; ++$i) {
                    list($post, $time, $parent) = explode('_', File::N($responses[$i]), 3);
                    if(strpos($parent, $value) === 0) {
                        $results[] = $responses[$i];
                    }
                }
            } else { // if($key === 'keyword') {
                for($i = 0; $i < $total_responses; ++$i) {
                    if(strpos(File::N($responses[$i]), $value) !== false) {
                        $results[] = $responses[$i];
                    }
                }
            }
        } else {
            for($i = 0; $i < $total_responses; ++$i) {
                if(strpos(File::N($responses[$i]), $filter) !== false) {
                    $results[] = $responses[$i];
                }
            }
        }
        unset($responses);
        return ! empty($results) ? $results : false;
    }

    /**
     * ==========================================================================
     *  GET LIST OF RESPONSE(S) DETAIL(S)
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    foreach(Get::responsesExtract() as $file) {
     *        echo $file['path'] . '<br>';
     *    }
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------------------
     *  $sorter   | string | The key of array item as sorting reference
     *  --------- | ------ | ----------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function responsesExtract($order = 'ASC', $sorter = 'time', $filter = "", $e = 'txt', $folder = RESPONSE, $FP = 'response:') {
        if($files = self::responses($order, $filter, $e, $folder)) {
            $results = array();
            foreach($files as $file) {
                $results[] = self::responseExtract($file, $FP);
            }
            unset($files);
            return ! empty($results) ? Mecha::eat($results)->order($order, $sorter)->vomit() : false;
        }
        return false;
    }

    /**
     * ==========================================================================
     *  GET MINIMUM DATA OF A RESPONSE
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::responseAnchor(1399334470));
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function responseAnchor($path, $folder = array(RESPONSE, POST), $FP = array('response:', 'post:')) {
        if(strpos($path, ROOT) === false) {
            $path = self::responsePath($path, $folder[0]); // By post slug, ID or time
        }
        if($path && ($buffer = File::open($path)->get(1)) !== false) {
            $results = self::responseExtract($path, $FP[0]);
            $parts = explode(S, $buffer, 2);
            $v = isset($parts[1]) ? Converter::DS(trim($parts[1])) : "";
            $v = Filter::colon($FP[0] . 'name_raw', $v, $results);
            $results['name_raw'] = $v;
            $results['name'] = Filter::colon($FP[0] . 'name', $v, $results);
            return Mecha::O($results);
        }
        return false;
    }

    /**
     * ==========================================================================
     *  GET RESPONSE HEADER(S) ONLY
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::responseHeader(1399334470));
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function responseHeader($path, $folder = array(RESPONSE, POST), $FP = array('response:', 'post:')) {
        $config = Config::get();
        if(strpos($path, ROOT) === false) {
            $path = self::responsePath($path, $folder[0]); // By response ID or time
        }
        if( ! $path) return false;
        $results = self::responseExtract($path, $FP[0]);
        $results = $results + Page::text($path, false, $FP[0], array(
            'url' => '#',
            'content_type' => $config->html_parser->active,
            'fields' => array()
        ), $results);
        $results['date'] = Filter::colon($FP[0] . 'date', Date::extract($results['time']), $results);
        self::_fields($results, $FP[0]);
        return Mecha::O($results);
    }

    /**
     * ==========================================================================
     *  EXTRACT RESPONSE FILE INTO LIST OF RESPONSE DATA FROM ITS PATH/ID/TIME
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::response(1399334470));
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type   | Description
     *  ---------- | ------ | ---------------------------------------------------
     *  $path      | string | Response path, ID or time
     *  $excludes  | array  | Exclude some field(s) from result(s)
     *  $folder    | array  | Folder of response(s) and response(s)' post
     *  $FP        | array  | Filter prefix for `Page::text()`
     *  ---------- | ------ | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function response($path, $excludes = array(), $folder = array(RESPONSE, POST), $FP = array('response:', 'post:')) {
        $config = Config::get();
        $excludes = array_flip($excludes);
        $results = false;
        if( ! is_array($path)) {
            // By time => `2014-06-21-20-05-17` or by ID => `1403355917`
            if(strpos($path, $folder[0]) !== 0) {
                $path = self::responsePath($path, $folder[0]);
            }
            // By path => `lot\responses\{$folder[0]}\2014-05-24-11-17-06_2014-06-21-20-05-17_0000-00-00-00-00-00.txt`
            $results = self::responseExtract($path, $FP[0]);
        } else {
            // From `Get::responseExtract()`
            $results = $path;
        }
        if( ! $results || ! file_exists($results['path'])) return false;
        $results['date'] = Filter::colon($FP[0] . 'date', Date::extract($results['time']), $results);
        $results = $results + Page::text(file_get_contents($results['path']), 'message', $FP[0], array(
            'url' => '#',
            'content_type' => $config->html_parser->active,
            'fields' => array(),
            'message' => ""
        ), $results);
        if( ! isset($excludes['permalink'])) {
            if($path = self::postPath($results['post'], $folder[1])) {
                $link = self::postAnchor($path, $folder[1], $FP[1])->url . '#' . rtrim($FP[0], ':') . '-' . $results['id'];
            } else {
                $link = '#';
            }
            $results['permalink'] = Filter::colon($FP[0] . 'permalink', $link, $results);
        }
        if( ! isset($excludes['fields'])) {
            self::_fields($results, $FP[0]);
        }
        foreach($results as $key => $value) {
            if(isset($excludes[$key])) {
                unset($results[$key]);
            }
        }
        return Mecha::O($results);
    }

    /**
     * ==========================================================================
     *  GET IMAGE(S) URL FROM TEXT SOURCE
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::imagesURL('some text', 'no-image.png'));
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------------------
     *  $source   | string | The source text
     *  $fallback | string | Fallback image URL if nothing matched
     *  --------- | ------ | ----------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function imagesURL($source, $fallback = array()) {
        $config = Config::get();
        $results = array();
        // Matched with ...
        //
        // [1]. `![alt text](IMAGE URL)`
        // [2]. `![alt text](IMAGE URL "optional title")`
        //
        // ... and the single-quoted version of them
        if(preg_match_all('#\!\[.*?\]\(([^\s]+?)( +([\'"]).*?\3)?\)#', $source, $matches)) {
            $results = array_merge($matches[1], $results);
        }
        // Matched with ...
        //
        // [1]. `<img src="IMAGE URL">`
        // [2]. `<img foo="bar" src="IMAGE URL">`
        // [3]. `<img src="IMAGE URL" foo="bar">`
        // [4]. `<img src="IMAGE URL"/>`
        // [5]. `<img foo="bar" src="IMAGE URL"/>`
        // [6]. `<img src="IMAGE URL" foo="bar"/>`
        // [7]. `<img src="IMAGE URL" />`
        // [8]. `<img foo="bar" src="IMAGE URL" />`
        // [9]. `<img src="IMAGE URL" foo="bar" />`
        //
        // ... and the uppercased version of them, and the single-quoted version of them
        if(preg_match_all('#<img .*?src=([\'"])([^\'"]+?)\1.*? *\/?>#i', $source, $matches)) {
            $results = array_merge($matches[2], $results);
        }
        // Matched with ...
        //
        // [1]. `background: url("IMAGE URL")`
        // [2]. `background-image: url("IMAGE URL")`
        // [3]. `background: foo url("IMAGE URL")`
        // [4]. `background-image: foo url("IMAGE URL")`
        // [5]. `content: url("IMAGE URL")`
        //
        // ... and the uppercased version of them, and the single-quoted version of them, and the un-quoted version of them
        if(preg_match_all('#(background-image|background|content)\: *.*?url\(([\'"]?)([^\'"]+?)\2\)#i', $source, $matches)) {
            $results = array_merge($matches[3], $results);
        }
        // Validate URL ...
        $results_v = array();
        foreach(array_unique($results) as $url) {
            $url = Converter::url($url);
            // internal image(s)
            if(strpos($url, $config->url) === 0 && file_exists(File::path($url))) {
                $results_v[] = $url;
            // external image(s)
            } else if(strpos($url, '://') !== false) {
                $results_v[] = $url;
            }
        }
        unset($results);
        return ! empty($results_v) ? $results_v : $fallback;
    }

    /**
     * ==========================================================================
     *  GET IMAGE URL FROM TEXT SOURCE
     * ==========================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type    | Description
     *  --------- | ------- | ---------------------------------------------------
     *  $source   | string  | The source text
     *  $sequence | integer | Sequence of available image URLs
     *  $fallback | string  | Fallback image URL if nothing matched
     *  --------- | ------- | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function imageURL($source, $sequence = 1, $fallback = null) {
        $images = self::imagesURL($source, array());
        return isset($images[$sequence - 1]) ? $images[$sequence - 1] : (is_null($fallback) ? Image::placeholder() : $fallback);
    }

    // Handle custom field(s) ...
    protected static function _fields(&$results, $FP) {
        // Initialize custom field(s) with the default value(s) so that
        // user(s) don't have to write `isset()` function multiple time(s)
        // just to prevent error message(s) because of the object key(s)
        // that is not available in the old post(s).
        $field_d = self::state_field(null, array(), true, rtrim($FP, ':'));
        foreach($field_d as $k => $v) {
            $s = isset($results['fields'][$k]) && $results['fields'][$k] !== "" ? $results['fields'][$k] : null;
            $s = Filter::colon($FP . 'fields_raw.' . $k, $s, $results);
            $results['fields_raw'][$k] = $s;
            // No value(s) stored in the page file
            if($s === null) {
                // For `option` field type, the first option will be used as the default value
                if($v['type'] === 'option') {
                    if(isset($v['placeholder']) && trim($v['placeholder']) !== "") {
                        // do nothing ...
                    } else {
                        $vv = array_keys(Converter::toArray($v['value'], S, '  '));
                        $v = isset($vv[0]) ? $vv[0] : "";
                    }
                // For `boolean` field type, empty value will be translated to `false`
                } else if($v['type'] === 'boolean') {
                    $s = false;
                }
            } else {
                // For `file` field type, the original custom field value is used to limit the file extension
                // So we have to check the existence of the file first. If it does not exist, then it may be
                // contained with file extension(s), not with a file name
                if($v['type'] === 'file') {
                    $e = File::E($s, false);
                    $s = $e !== false ? File::exist(SUBSTANCE . DS . $e . DS . $s, "") : "";
                }
            }
            $results['fields'][$k] = Filter::colon($FP . 'fields.' . $k, $s, $results);
        }
        unset($field_d, $s);
    }

}