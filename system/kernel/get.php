<?php

class Get {

    // Apply the missing filters
    private static function AMF($data, $FP = "", $F) {
        $output = Filter::apply($F, $data);
        if(is_string($FP) && trim($FP) !== "") {
            $output = Filter::apply($FP . $F, $output);
        }
        return $output;
    }

    /**
     * ==========================================================================
     *  GET LIST OF FILE DETAILS
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::fileExtract($input));
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function fileExtract($input) {
        if( ! $input) return false;
        $extension = pathinfo($input, PATHINFO_EXTENSION);
        return array(
            'path' => $input,
            'name' => basename($input, '.' . $extension),
            'url' => File::url($input),
            'extension' => strtolower($extension),
            'last_update' => file_exists($input) ? filemtime($input) : null,
            'update' => file_exists($input) ? date('Y-m-d H:i:s', filemtime($input)) : null,
            'size_raw' => file_exists($input) ? filesize($input) : null,
            'size' => File::size($input)
        );
    }

    /**
     * ==========================================================================
     *  GET ALL FILES RECURSIVELY
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
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter   | Type    | Desription
     *  ----------- | ------- | -------------------------------------------------
     *  $folder     | string  | Path to folder of files you want to be listed
     *  $extensions | string  | The file extensions
     *  $order      | string  | Ascending or descending? ASC/DESC?
     *  $sorter     | string  | The key of array item as sorting reference
     *  $filter     | string  | Filter the resulted array by a keyword
     *  $inclusive  | boolean | Include hidden files to results?
     *  ----------- | ------- | -------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function files($folder = ASSET, $extensions = '*', $order = 'DESC', $sorter = 'path', $filter = "", $inclusive = false) {
        if( ! file_exists($folder)) return false;
        $results = array();
        $results_inclusive = array();
        $extension = $extensions == '*' ? '.*?' : str_replace(array(', ', ','), '|', $extensions);
        $directory = new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS);
        foreach(new RegexIterator(new RecursiveIteratorIterator($directory), '#\.(' . $extension . ')$#i') as $file => $object) {
            if(empty($filter)) {
                $results_inclusive[] = self::fileExtract($file);
            } else {
                if(strpos(basename($file), $filter) !== false) {
                    $results_inclusive[] = self::fileExtract($file);
                }
            }
            if(
                // Exclude all files inside a folder from results if the
                // folder name begins with two underscores. Example: `__folder-name`
                strpos(basename(dirname($file)), '__') !== 0 &&
                // Exclude file from results if the file name begins with
                // two underscores. Example: `__file-name.txt`
                strpos(basename($file), '__') !== 0 &&
                // Linux?
                strpos(basename(dirname($file)), '.') !== 0 &&
                strpos(basename($file), '.') !== 0
            ) {
                if(empty($filter)) {
                    $results[] = self::fileExtract($file);
                } else {
                    if(strpos(basename($file), $filter) !== false) {
                        $results[] = self::fileExtract($file);
                    }
                }
            }
        }
        if( ! $inclusive) {
            return ! empty($results_inclusive) ? Mecha::eat($results_inclusive)->order($order, $sorter)->vomit() : false;
        } else {
            return ! empty($results) ? Mecha::eat($results)->order($order, $sorter)->vomit() : false;
        }
    }

    /**
     * ==========================================================================
     *  GET ADJACENT FILES
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    $files = Get::adjacentFiles(
     *        'some/path',
     *        'txt',
     *        'ASC',
     *        'update'
     *    );
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function adjacentFiles($folder = ASSET, $extensions = '*', $order = 'DESC', $sorter = 'path', $filter = "", $inclusive = false) {
        if( ! file_exists($folder)) return false;
        $results = array();
        $results_inclusive = array();
        $extension = str_replace(', ', ',', $extensions);
        $files = strpos($extension, ',') !== false ? glob($folder . DS . '*.{' . $extension . '}', GLOB_BRACE) : glob($folder . DS . '*.' . $extension);
        foreach($files as $file) {
            if(empty($filter)) {
                $results_inclusive[] = self::fileExtract($file);
            } else {
                if(strpos(basename($file), $filter) !== false) {
                    $results_inclusive[] = self::fileExtract($file);
                }
            }
            if(
                strpos(basename(dirname($file)), '__') !== 0 &&
                strpos(basename($file), '__') !== 0 &&
                strpos(basename(dirname($file)), '.') !== 0 &&
                strpos(basename($file), '.') !== 0
            ) {
                if(empty($filter)) {
                    $results[] = self::fileExtract($file);
                } else {
                    if(strpos(basename($file), $filter) !== false) {
                        $results[] = self::fileExtract($file);
                    }
                }
            }
        }
        if($inclusive) {
            return ! empty($results_inclusive) ? Mecha::eat($results_inclusive)->order($order, $sorter)->vomit() : false;
        } else {
            return ! empty($results) ? Mecha::eat($results)->order($order, $sorter)->vomit() : false;
        }
    }

    /**
     * ==========================================================================
     *  GET ALL FILES RECURSIVELY INCLUDING THE EXCLUDED FILES
     * ==========================================================================
     */

    public static function inclusiveFiles($folder = ASSET, $extensions = '*', $order = 'DESC', $sorter = 'path', $filter = "") {
        return self::files($folder, $extensions, $order, $sorter, $filter, true);
    }

    /**
     * ==========================================================================
     *  GET ADJACENT FILES INCLUDING THE EXCLUDED FILES
     * ==========================================================================
     */

    public static function inclusiveAdjacentFiles($folder = ASSET, $extensions = '*', $order = 'DESC', $sorter = 'path', $filter = "") {
        return self::adjacentFiles($folder, $extensions, $order, $sorter, $filter, true);
    }

    // Get stored configuration data (internal only)
    public static function state_config($fallback = false) {
        return File::open(STATE . DS . 'config.txt')->unserialize($fallback);
    }

    // Get stored custom field data (internal only)
    public static function state_field($fallback = false) {
        if($pre_113 = File::exist(STATE . DS . 'fields.txt')) {
            File::open($pre_113)->renameTo('field.txt');
        }
        return File::open(STATE . DS . 'field.txt')->unserialize($fallback);
    }

    // Get stored menu data (internal only)
    public static function state_menu($fallback = false) {
        if($pre_113 = File::exist(STATE . DS . 'menus.txt')) {
            File::open($pre_113)->renameTo('menu.txt');
        }
        return File::open(STATE . DS . 'menu.txt')->read($fallback);
    }

    // Get stored shortcode data (internal only)
    public static function state_shortcode($fallback = false) {
        if($pre_113 = File::exist(STATE . DS . 'shortcodes.txt')) {
            File::open($pre_113)->renameTo('shortcode.txt');
        }
        return File::open(STATE . DS . 'shortcode.txt')->unserialize($fallback);
    }

    // Get stored tag data (internal only)
    public static function state_tag($fallback = false) {
        if($pre_113 = File::exist(STATE . DS . 'tags.txt')) {
            File::open($pre_113)->renameTo('tag.txt');
        }
        return File::open(STATE . DS . 'tag.txt')->unserialize($fallback);
    }

    /**
     * ==========================================================================
     *  EXTRACT ARRAY OF TAGS FROM TAG FILES
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    $tags = Get::rawTags();
     *
     *    foreach($tags as $tag) {
     *        echo $tag['name'] . '<br>';
     *    }
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function rawTags($order = 'ASC', $sorter = 'name') {
        $config = Config::get();
        $speak = Config::speak();
        $d = DECK . DS . 'workers' . DS . 'repair.state.tag.php';
        $tags = file_exists($d) ? include $d : array();
        if($file = self::state_tag()) {
            Mecha::extend($tags, $file);
        }
        foreach($tags as $k => $v) {
            $tags[$k] = array(
                'id' => self::AMF($v['id'], 'tag:', 'id'),
                'name' => self::AMF($v['name'], 'tag:', 'name'),
                'slug' => self::AMF($v['slug'], 'tag:', 'slug'),
                'description' => self::AMF($v['description'], 'tag:', 'description')
            );
        }
        return Mecha::eat($tags)->order($order, $sorter)->vomit();
    }

    /**
     * ==========================================================================
     *  RETURN SPECIFIC TAG ITEM FILTERED BY ITS AVAILABLE DATA
     *
     *  It can be an ID, name or slug.
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    $tag = Get::rawTagsBy('lorem-ipsum');
     *    echo $tag['name'] . '<br>';
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function rawTagsBy($filter) {
        $tags = self::rawTags('ASC', 'id');
        $result = false;
        for($i = 0, $count = count($tags); $i < $count; ++$i) {
            if((is_numeric($filter) && (int) $filter === (int) $tags[$i]['id']) || (is_string($filter) && (string) $filter === (string) $tags[$i]['name']) || (is_string($filter) && (string) $filter === (string) $tags[$i]['slug'])) {
                $result = $tags[$i];
                break;
            }
        }
        return $result;
    }

    /**
     * ==========================================================================
     *  `Get::rawTags()` AS OBJECT
     * ==========================================================================
     */

    public static function tags($order = 'ASC', $sorter = 'name') {
        return Mecha::O(self::rawTags($order, $sorter));
    }

    /**
     * ==========================================================================
     *  `Get::rawTagsBy()` AS OBJECT
     * ==========================================================================
     */

    public static function tagsBy($id_or_name_or_slug) {
        return Mecha::O(self::rawTagsBy($id_or_name_or_slug));
    }

    /**
     * ==========================================================================
     *  GET LIST OF PAGES PATH
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    foreach(Get::pages() as $path) {
     *        echo $path . '<br>';
     *    }
     *
     *    // [1]. Filter by Tag(s) ID
     *    Get::pages('DESC', 'kind:2');
     *    Get::pages('DESC', 'kind:2,3,4');
     *
     *    // [2]. Filter by Time
     *    Get::pages('DESC', 'time:2014');
     *    Get::pages('DESC', 'time:2014-11');
     *    Get::pages('DESC', 'time:2014-11-10');
     *
     *    // [3]. Filter by Slug
     *    Get::pages('DESC', 'slug:lorem');
     *    Get::pages('DESC', 'slug:lorem-ipsum');
     *
     *    // [4]. The Old Ways
     *    Get::pages('DESC', 'lorem');
     *    Get::pages('DESC', 'lorem-ipsum');
     *
     *    // [5]. The Old Ways' Alias
     *    Get::pages('DESC', 'keyword:lorem');
     *    Get::pages('DESC', 'keyword:lorem-ipsum');
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type    | Description
     *  ---------- | ------- | --------------------------------------------------
     *  $order     | string  | Ascending or descending? ASC/DESC?
     *  $filter    | string  | Filter the resulted array by a keyword
     *  $extension | boolean | The file extension(s)
     *  $folder    | string  | Folder of the pages
     *  ---------- | ------- | --------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function pages($order = 'DESC', $filter = "", $extension = 'txt', $folder = PAGE) {
        $results = array();
        $pages = strpos($extension, ',') !== false ? glob($folder . DS . '*.{' . $extension . '}', GLOB_BRACE) : glob($folder . DS . '*.' . $extension);
        $total_pages = count($pages);
        if( ! is_array($pages) || $total_pages === 0) return false;
        if($order == 'ASC') {
            sort($pages);
        } else {
            rsort($pages);
        }
        if(empty($filter)) return $pages;
        if(strpos($filter, ':') !== false) {
            list($key, $value) = explode(':', $filter, 2);
            if($key == 'time') {
                for($i = 0; $i < $total_pages; ++$i) {
                    list($time, $kind, $slug) = explode('_', basename($pages[$i], '.' . pathinfo($pages[$i], PATHINFO_EXTENSION)), 3);
                    if(strpos($time, $value) !== false) {
                        $results[] = $pages[$i];
                    }
                }
                return ! empty($results) ? $results : false;
            } elseif($key == 'kind') {
                if(strpos($value, ',') !== false) {
                    $kinds = explode(',', $value);
                    for($i = 0; $i < $total_pages; ++$i) {
                        $name = basename($pages[$i], '.' . pathinfo($pages[$i], PATHINFO_EXTENSION));
                        foreach($kinds as $kind) {
                            if(
                                strpos($name, '_' . $kind . '_') !== false ||
                                strpos($name, ',' . $kind . ',') !== false ||
                                strpos($name, '_' . $kind . ',') !== false ||
                                strpos($name, ',' . $kind . '_') !== false
                            ) {
                                $results[] = $pages[$i];
                            }
                        }
                    }
                    return ! empty($results) ? array_unique($results) : false;
                } else {
                    for($i = 0; $i < $total_pages; ++$i) {
                        $name = basename($pages[$i], '.' . pathinfo($pages[$i], PATHINFO_EXTENSION));
                        if(
                            strpos($name, '_' . $value . '_') !== false ||
                            strpos($name, ',' . $value . ',') !== false ||
                            strpos($name, '_' . $value . ',') !== false ||
                            strpos($name, ',' . $value . '_') !== false
                        ) {
                            $results[] = $pages[$i];
                        }
                    }
                    return ! empty($results) ? $results : false;
                }
            } elseif($key == 'slug') {
                for($i = 0; $i < $total_pages; ++$i) {
                    list($time, $kind, $slug) = explode('_', basename($pages[$i], '.' . pathinfo($pages[$i], PATHINFO_EXTENSION)), 3);
                    if(strpos($slug, $value) !== false) {
                        $results[] = $pages[$i];
                    }
                }
                return ! empty($results) ? $results : false;
            } else { // if($key == 'keyword') {
                for($i = 0; $i < $total_pages; ++$i) {
                    if(strpos(basename($pages[$i], '.' . pathinfo($pages[$i], PATHINFO_EXTENSION)), $value) !== false) {
                        $results[] = $pages[$i];
                    }
                }
                return ! empty($results) ? $results : false;
            }
        } else {
            for($i = 0; $i < $total_pages; ++$i) {
                if(strpos(basename($pages[$i], '.' . pathinfo($pages[$i], PATHINFO_EXTENSION)), $filter) !== false) {
                    $results[] = $pages[$i];
                }
            }
            return ! empty($results) ? $results : false;
        }
        return false;
    }

    /**
     * ==========================================================================
     *  GET LIST OF ARTICLES PATH
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    foreach(Get::articles() as $path) {
     *        echo $path . '<br>';
     *    }
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function articles($order = 'DESC', $filter = "", $extension = 'txt') {
        return self::pages($order, $filter, $extension, ARTICLE);
    }

    /**
     * ===========================================================================
     *  GET LIST OF COMMENTS PATH
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    foreach(Get::comments() as $path) {
     *        echo $path . '<br>';
     *    }
     *
     * ---------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type    | Description
     *  ---------- | ------- | ---------------------------------------------------
     *  $post      | string  | Post time as results filter
     *  $order     | string  | Ascending or descending? ASC/DESC?
     *  $extension | boolean | The file extension(s)
     *  ---------- | ------- | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function comments($post = null, $order = 'ASC', $extension = 'txt') {
        if( ! is_null($post)) {
            $post = Date::format($post, 'Y-m-d-H-i-s');
            $results = strpos($extension, ',') !== false ? glob(RESPONSE . DS . $post . '_*.{' . $extension . '}', GLOB_BRACE) : glob(RESPONSE . DS . $post . '_*.' . $extension);
        } else {
            $results = strpos($extension, ',') !== false ? glob(RESPONSE . DS . '*.{' . $extension . '}', GLOB_BRACE) : glob(RESPONSE . DS . '*.' . $extension);
        }
        if( ! is_array($results) || count($results) === 0) return false;
        if($order == 'ASC') {
            sort($results);
        } else {
            rsort($results);
        }
        return $results;
    }

    /**
     * ==========================================================================
     *  GET LIST OF PAGES DETAILS
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    foreach(Get::pagesExtract() as $file) {
     *        echo $file['path'] . '<br>';
     *    }
     *
     *    Get::pagesExtract('DESC', 'time', 'kind:2');
     *    Get::pagesExtract('DESC', 'time', 'kind:2,3,4');
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type    | Description
     *  --------- | ------- | ---------------------------------------------------
     *  $sorter   | string  | The key of array item as sorting reference
     *  --------- | ------- | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function pagesExtract($order = 'DESC', $sorter = 'time', $filter = "", $extension = 'txt', $folder = PAGE) {
        if($files = self::pages($order, $filter, $extension, $folder)) {
            $results = array();
            foreach($files as $file) {
                $results[] = self::pageExtract($file);
            }
            return ! empty($results) ? Mecha::eat($results)->order($order, $sorter)->vomit() : false;
        }
        return false;
    }

    /**
     * ==========================================================================
     *  GET LIST OF ARTICLES DETAILS
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    foreach(Get::articlesExtract() as $file) {
     *        echo $file['path'] . '<br>';
     *    }
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function articlesExtract($order = 'DESC', $sorter = 'time', $filter = "", $extension = 'txt') {
        return self::pagesExtract($order, $sorter, $filter, $extension, ARTICLE);
    }

    /**
     * ==========================================================================
     *  GET LIST OF COMMENTS DETAILS
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    foreach(Get::commentsExtract() as $file) {
     *        echo $file['path'] . '<br>';
     *    }
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type    | Description
     *  --------- | ------- | ---------------------------------------------------
     *  $sorter   | string  | The key of array item as sorting reference
     *  --------- | ------- | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function commentsExtract($post = null, $order = 'ASC', $sorter = 'path', $extension = 'txt') {
        if($files = self::comments($post, $order, $extension)) {
            $results = array();
            foreach($files as $file) {
                $results[] = self::commentExtract($file);
            }
            return ! empty($results) ? Mecha::eat($results)->order($order, $sorter)->vomit() : false;
        }
        return false;
    }

    /**
     * ==========================================================================
     *  GET LIST OF PAGE DETAILS
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::pageExtract($input));
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function pageExtract($input, $FP = 'page:') {
        if( ! $input) return false;
        $extension = pathinfo($input, PATHINFO_EXTENSION);
        list($time, $kind, $slug) = explode('_', basename($input, '.' . $extension), 3);
        $kind = explode(',', $kind);
        return array(
            'path' => self::AMF($input, $FP, 'path'),
            'id' => self::AMF((int) Date::format($time, 'U'), $FP, 'id'),
            'time' => self::AMF(Date::format($time), $FP, 'time'),
            'last_update' => self::AMF(file_exists($input) ? filemtime($input) : null, $FP, 'last_update'),
            'update' => self::AMF(file_exists($input) ? date('Y-m-d H:i:s', filemtime($input)) : null, $FP, 'update'),
            'kind' => self::AMF(Converter::strEval($kind), $FP, 'kind'),
            'slug' => self::AMF($slug, $FP, 'slug'),
            'state' => self::AMF($extension == 'txt' ? 'published' : 'draft', $FP, 'state')
        );
    }

    /**
     * ==========================================================================
     *  GET LIST OF ARTICLE DETAILS
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::articleExtract($input));
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function articleExtract($input) {
        return self::pageExtract($input, 'article:');
    }

    /**
     * ==========================================================================
     *  GET LIST OF COMMENT DETAILS
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::commentExtract($input));
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function commentExtract($input) {
        $FP = 'comment:';
        if( ! $input) return false;
        $extension = pathinfo($input, PATHINFO_EXTENSION);
        list($post, $id, $parent) = explode('_', basename($input, '.' . $extension), 3);
        return array(
            'path' => self::AMF($input, $FP, 'path'),
            'time' => self::AMF(Date::format($id), $FP, 'time'),
            'last_update' => self::AMF(file_exists($input) ? filemtime($input) : null, $FP, 'last_update'),
            'update' => self::AMF(file_exists($input) ? date('Y-m-d H:i:s', filemtime($input)) : null, $FP, 'update'),
            'post' => self::AMF((int) Date::format($post, 'U'), $FP, 'post'),
            'id' => self::AMF((int) Date::format($id, 'U'), $FP, 'id'),
            'parent' => self::AMF($parent === '0000-00-00-00-00-00' ? null : (int) Date::format($parent, 'U'), $FP, 'parent'),
            'state' => self::AMF($extension == 'txt' ? 'approved' : 'pending', $FP, 'state')
        );
    }

    /**
     * ==========================================================================
     *  EXTRACT PAGE FILE INTO LIST OF PAGE DATA FROM ITS PATH/SLUG/ID
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::page('about'));
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type   | Description
     *  ---------- | ------ | ---------------------------------------------------
     *  $reference | mixed  | Slug, ID, path or array of `Get::pageExtract()`
     *  $excludes  | array  | Exclude some fields from results
     *  $folder    | string | Folder of the pages
     *  $connector | string | Path connector for page URL
     *  $FP        | string | Filter prefix for `Text::toPage()`
     *  ---------- | ------ | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function page($reference, $excludes = array(), $folder = PAGE, $connector = '/', $FP = 'page:') {

        $config = Config::get();
        $speak = Config::speak();

        $excludes = array_flip($excludes);
        $results = false;

        // From `Get::pageExtract()`
        if(is_array($reference)) {
            $results = $reference;
        } else {
            // By path => `root:cabinet/pages/0000-00-00-00-00-00_1,2,3_page-slug.txt`
            if(strpos($reference, $folder) === 0) {
                $results = self::pageExtract($reference);
            } else {
                // By slug => `page-slug` or by ID => 12345
                $results = self::pageExtract(self::pagePath($reference, $folder));
            }
        }

        if( ! $results || ! file_exists($results['path'])) return false;

        /**
         * RULES: Do not do any tags looping, content Markdown-ing and
         * external file requesting if it is marked as the excluded
         * fields. For better performance.
         */

        $results = $results + Text::toPage(File::open($results['path'])->read(), (isset($excludes['content']) ? false : 'content'), $FP);

        $content = isset($results['content_raw']) ? $results['content_raw'] : "";
        $time = str_replace(array(' ', ':'), '-', $results['time']);
        $extension = $results['state'] == 'published' ? '.txt' : '.draft';

        if($php_file = File::exist(dirname($results['path']) . DS . $results['slug'] . '.php')) {
            ob_start();
            include $php_file;
            $results['content'] = ob_get_clean();
        }

        $results['excerpt'] = "";
        $results['date'] = self::AMF(Date::extract($results['time']), $FP, 'date');
        $results['url'] = self::AMF($config->url . $connector . $results['slug'], $FP, 'url');

        if( ! isset($results['author'])) $results['author'] = self::AMF($config->author, $FP, 'author');

        if( ! isset($results['description'])) {
            $summary = self::summary($content, $config->excerpt_length, $config->excerpt_tail);
            $results['description'] = self::AMF($summary, $FP, 'description');
        }

        $content_test = isset($excludes['content']) && strpos($content, '<!--') !== false ? Text::toPage($content, 'content', $FP) : $results;
        $content_test = $content_test['content'];

        if(strpos($content_test, '<!-- cut+ ') !== false) {
            preg_match('#<!-- cut\+( +([\'"]?)(.*?)\2)? -->#', $content_test, $matches);
            $content_more = ! empty($matches[3]) ? $matches[3] : $speak->read_more;
            $content_test = preg_replace('#<!-- cut\+( +(.*?))? -->#', '<p><a class="fi-link" href="' . $results['url'] . '#read-more:' . $results['id'] . '" rel="nofollow">' . $content_more . '</a></p><!-- cut -->', $content_test);
        }

        if(strpos($content_test, '<!-- cut -->') !== false) {
            $parts = explode('<!-- cut -->', $content_test, 2);
            $results['excerpt'] = self::AMF(preg_replace('#<\/p>[\n\r]*<p><a class="fi-link"#', ' <a class="fi-link"', trim($parts[0])), $FP, 'excerpt');
            $results['content'] = trim($parts[0]) . NL . NL . "<span class=\"fi\" id=\"read-more:" . $results['id'] . "\" aria-hidden=\"true\"></span>" . NL . NL . trim($parts[1]);
        }

        if( ! isset($excludes['tags'])) {
            $tags = array();
            foreach($results['kind'] as $id) {
                $tags[] = self::rawTagsBy($id);
            }
            $results['tags'] = self::AMF($tags, $FP, 'tags');
        }

        if( ! isset($excludes['css']) || ! isset($excludes['js'])) {
            if($file = File::exist(CUSTOM . DS . $time . $extension)) {
                $custom = explode(SEPARATOR, File::open($file)->read());
                $results['css_raw'] = isset($custom[0]) ? Text::DS(trim($custom[0])) : "";
                $results['js_raw'] = isset($custom[1]) ? Text::DS(trim($custom[1])) : "";
                $css_raw = self::AMF($results['css_raw'], 'custom:', 'css_raw');
                $css_raw = self::AMF($results['css_raw'], 'custom:', 'shortcode');
                $css_raw = Filter::apply('custom:css', $css_raw);
                $css_raw = Filter::apply('css:shortcode', $css_raw);
                $results['css'] = self::AMF($css_raw, $FP, 'css');
                $js_raw = self::AMF($results['js_raw'], 'custom:', 'js_raw');
                $js_raw = self::AMF($results['js_raw'], 'custom:', 'shortcode');
                $js_raw = Filter::apply('custom:js', $js_raw);
                $js_raw = Filter::apply('js:shortcode', $js_raw);
                $results['js'] = self::AMF($js_raw, $FP, 'js');
            } else {
                $results['css'] = $results['js'] = $results['css_raw'] = $results['js_raw'] = "";
            }
            $custom = $results['css'] . $results['js'];
        } else {
            $custom = "";
        }

        $results['images'] = self::AMF(self::imagesURL($results['content'] . $custom), $FP, 'images');
        $results['image'] = self::AMF(isset($results['images'][0]) ? $results['images'][0] : Image::placeholder(), $FP, 'image');

        $comments = self::comments($results['id'], 'ASC', (Guardian::happy() ? 'txt,hold' : 'txt'));
        $results['total_comments'] = self::AMF($comments !== false ? count($comments) : 0, $FP, 'total_comments');
        $results['total_comments_text'] = self::AMF($results['total_comments'] . ' ' . ($results['total_comments'] > 1 ? $speak->comments : $speak->comment), $FP, 'total_comments_text');

        if($comments && ! isset($excludes['comments'])) {
            $results['comments'] = array();
            foreach($comments as $comment) {
                $results['comments'][] = self::comment($comment);
            }
            $results['comments'] = self::AMF($results['comments'], $FP, 'comments');
        }

        /**
         * Custom fields ...
         */

        if( ! isset($excludes['fields']) && isset($results['fields']) && is_array($results['fields'])) {

            /**
             * Initialize custom fields with the default values so that users
             * don't have to write `isset()` function multiple times just
             * to prevent error messages because of the object key that
             * is not available in the old posts.
             */

            $fields = self::state_field(array());

            $init = array();

            foreach($fields as $key => $value) {
                if( ! isset($value['scope']) || $value['scope'] == rtrim($FP, ':')) {
                    $init[$key] = $value['value'];
                }
            }

            /**
             * Start re-writing ...
             */

            foreach($results['fields'] as $key => $value) {
                $init[$key] = self::AMF(isset($value['value']) ? $value['value'] : false, $FP, 'fields.' . $key);
            }

            $results['fields'] = $init;

        }

        /**
         * Exclude some fields from results
         */

        foreach($results as $key => $value) {
            if(isset($excludes[$key])) {
                unset($results[$key]);
            }
        }

        return Mecha::O($results);

    }

    /**
     * ==========================================================================
     *  EXTRACT ARTICLE FILE INTO LIST OF ARTICLE DATA FROM ITS SLUG/FILE PATH
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::article('lorem-ipsum'));
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function article($reference, $excludes = array()) {
        return self::page($reference, $excludes, ARTICLE, '/' . Config::get('index')->slug . '/', 'article:');
    }

    /**
     * ===========================================================================
     *  EXTRACT COMMENT FILE INTO LIST OF COMMENT DATA FROM ITS PATH/ID/TIME/NAME
     * ===========================================================================
     *
     * -- CODE: ------------------------------------------------------------------
     *
     *    var_dump(Get::comment(1399334470));
     *
     * ---------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type    | Description
     *  ---------- | ------- | ---------------------------------------------------
     *  $reference | string  | Comment path, ID, time or name
     *  ---------- | ------- | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function comment($reference, $response_to = ARTICLE, $connector = null) {
        $FP = 'comment:';
        $config = Config::get();
        $results = array();
        $path = false;
        if(strpos(ROOT, $reference) === 0) { // By comment path
            $path = $reference;
        } else {
            foreach(self::comments(null, 'DESC', 'txt,hold') as $comment) {
                $base = basename($comment);
                list($_post, $_time, $_parent) = explode('_', $base);
                if(
                    ! is_numeric($reference) && (string) basename($reference) === (string) $base || // By comment name
                    (int) Date::format($reference, 'U') === (int) Date::format($_time, 'U') // By comment time/ID
                ) {
                    $path = $comment;
                    $results = self::commentExtract($comment);
                    break;
                }
            }
        }
        if( ! $path || ! file_exists($path)) return false;
        $results['date'] = self::AMF(Date::extract($results['time']), $FP, 'date');
        $results = $results + Text::toPage(File::open($path)->read(), 'message', 'comment:');
        $results['email'] = Text::parse($results['email'], '->decoded_html');
        $results['permalink'] = '#';
        $posts = glob($response_to . DS . '*.txt');
        for($i = 0, $total = count($posts); $i < $total; ++$i) {
            list($time, $kind, $slug) = explode('_', basename($posts[$i], '.' . pathinfo($posts[$i], PATHINFO_EXTENSION)), 3);
            if((int) Date::format($time, 'U') === $results['post']) {
                $results['permalink'] = self::AMF($config->url . (is_null($connector) ? '/' . $config->index->slug . '/' : $connector) . $slug . '#comment-' . $results['id'], $FP, 'permalink');
                break;
            }
        }
        if(isset($results['fields']) && is_array($results['fields'])) {
            $fields = self::state_field(array());
            $init = array();
            foreach($fields as $key => $value) {
                if( ! isset($value['scope']) || $value['scope'] == rtrim($FP, ':')) {
                    $init[$key] = $value['value'];
                }
            }
            foreach($results['fields'] as $key => $value) {
                $init[$key] = self::AMF(isset($value['value']) ? $value['value'] : false, $FP, 'fields.' . $key);
            }
            $results['fields'] = $init;
        }
        return Mecha::O($results);
    }

    /**
     * ==========================================================================
     *  GET PAGE HEADERS ONLY
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::pageHeader('lorem-ipsum'));
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter      | Type   | Description
     *  -------------- | ------ | -----------------------------------------------
     *  $path          | string | The URL path of the page file, or a page slug
     *  $folder        | string | Folder of the pages
     *  $connector     | string | See `Get::page()`
     *  $FP | string | See `Get::page()`
     *  -------------- | ------ | -----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function pageHeader($path, $folder = PAGE, $connector = '/', $FP = 'page:') {
        $config = Config::get();
        if(strpos($path, ROOT) === false) {
            $path = self::pagePath($path, $folder); // By page slug, ID or time
        }
        if( ! $path) return false;
        $results = self::pageExtract($path) + Text::toPage($path, false, $FP);
        $results['date'] = self::AMF(Date::extract($results['time']), $FP, 'date');
        $results['url'] = self::AMF($config->url . $connector . $results['slug'], $FP, 'url');
        if( ! isset($results['author'])) $results['author'] = self::AMF($config->author, $FP, 'author');
        if( ! isset($results['description'])) $results['description'] = self::AMF("", $FP, 'description');
        if(isset($results['fields']) && is_array($results['fields'])) {
            $fields = self::state_field(array());
            $init = array();
            foreach($fields as $key => $value) {
                if( ! isset($value['scope']) || $value['scope'] == rtrim($FP, ':')) {
                    $init[$key] = $value['value'];
                }
            }
            foreach($results['fields'] as $key => $value) {
                $init[$key] = self::AMF(isset($value['value']) ? $value['value'] : false, $FP, 'fields.' . $key);
            }
            $results['fields'] = $init;
        }
        return Mecha::O($results);
    }

    /**
     * ==========================================================================
     *  GET ARTICLE HEADERS ONLY
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::articleHeader('lorem-ipsum'));
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function articleHeader($path) {
        return self::pageHeader($path, ARTICLE, '/' . Config::get('index')->slug . '/', 'article:');
    }

    /**
     * ==========================================================================
     *  GET MINIMUM DATA OF A PAGE
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::pageAnchor('about'));
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter      | Type   | Description
     *  -------------- | ------ | -----------------------------------------------
     *  $path          | string | The URL path of the page file, or a page slug
     *  $folder        | string | Folder of the pages
     *  $connector     | string | See `Get::page()`
     *  $FP | string | See `Get::page()`
     *  -------------- | ------ | -----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function pageAnchor($path, $folder = PAGE, $connector = '/', $FP = 'page:') {
        $config = Config::get();
        if(strpos($path, ROOT) === false) {
            $path = self::pagePath($path, $folder); // By page slug, ID or time
        }
        if($path && ($buffer = File::open($path)->get(1)) !== false) {
            $results = self::pageExtract($path);
            $parts = explode(':', $buffer, 2);
            $results['url'] = self::AMF($config->url . $connector . $results['slug'], $FP, 'url');
            $results['title'] = self::AMF((isset($parts[1]) ? Text::DS(trim($parts[1])) : '?'), $FP, 'title');
            return Mecha::O($results);
        }
        return false;
    }

    /**
     * ==========================================================================
     *  GET MINIMUM DATA OF AN ARTICLE
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::articleAnchor('lorem-ipsum'));
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function articleAnchor($path) {
        return self::pageAnchor($path, ARTICLE, '/' . Config::get('index')->slug . '/', 'article:');
    }

    /**
     * ==========================================================================
     *  GET PAGE PATH
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::pagePath('lorem-ipsum'));
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------------------
     *  $detector | mixed  | Slug, ID or time of the page
     *  --------- | ------ | ----------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     */

    public static function pagePath($detector, $folder = PAGE) {
        $results = false;
        foreach(glob($folder . DS . '*.{txt,draft}', GLOB_BRACE) as $path) {
            list($time, $kind, $slug) = explode('_', basename($path, '.' . pathinfo($path, PATHINFO_EXTENSION)), 3);
            if($slug == $detector || (is_numeric($detector) && (string) date('Y-m-d-H-i-s', $detector) === (string) $time)) {
                $results = $path;
                break;
            }
        }
        return $results;
    }

    /**
     * ==========================================================================
     *  GET ARTICLE PATH
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::articlePath('lorem-ipsum'));
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------------------
     *  $detector | mixed  | Slug, ID or time of the article
     *  --------- | ------ | ----------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     */

    public static function articlePath($detector) {
        return self::pagePath($detector, ARTICLE);
    }

    /**
     * ==========================================================================
     *  GET SUMMARY FROM LONG TEXT SOURCE
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    $summary = Get::summary('Very very long text...');
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  See => `Converter::curt()`
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function summary($input, $chars = 100, $tail = '&hellip;') {
        return Converter::curt($input, $chars, $tail);
    }

    /**
     * ==========================================================================
     *  GET IMAGES URL FROM TEXT SOURCE
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::imagesURL('some text', 'no-image.png'));
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type   | Description
     *  ---------- | ------ | ---------------------------------------------------
     *  $source    | string | The source text
     *  $fallback  | string | Fallback image URL if nothing matched
     *  ---------- | ------ | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function imagesURL($source, $fallback = array()) {

        /**
         * Matched with ...
         * ----------------
         *
         * [1]. `![alt text](IMAGE URL)`
         * [2]. `![alt text](IMAGE URL "optional title")`
         *
         * ... and the single-quoted version of them
         *
         */

        // if(preg_match_all('#\!\[.*?\]\(([^\s]+?)( +([\'"]).*?\3)?\)#', $source, $matches)) {
        //     return $matches[1];
        // }

        /**
         * Matched with ...
         * ----------------
         *
         * [1]. `<img src="IMAGE URL">`
         * [2]. `<img foo="bar" src="IMAGE URL">`
         * [3]. `<img src="IMAGE URL" foo="bar">`
         * [4]. `<img src="IMAGE URL"/>`
         * [5]. `<img foo="bar" src="IMAGE URL"/>`
         * [6]. `<img src="IMAGE URL" foo="bar"/>`
         * [7]. `<img src="IMAGE URL" />`
         * [8]. `<img foo="bar" src="IMAGE URL" />`
         * [9]. `<img src="IMAGE URL" foo="bar" />`
         *
         * ... and the uppercased version of them, and the single-quoted version of them
         *
         */

        if(preg_match_all('#<img .*?src=([\'"])([^\'"]+?)\1.*? *\/?>#i', $source, $matches)) {
            return $matches[2];
        }

        /**
         * Matched with ...
         * ----------------
         *
         * [1]. `background:url("IMAGE URL")`
         * [2]. `background-image:url("IMAGE URL")`
         * [3]. `background: url("IMAGE URL")`
         * [4]. `background-image: url("IMAGE URL")`
         * [5]. `background: foo url("IMAGE URL")`
         * [6]. `background-image: foo url("IMAGE URL")`
         * [7]. `content:url("IMAGE URL")`
         * [8]. `content: url("IMAGE URL")`
         *
         * ... and the uppercased version of them, and the single-quoted version of them, and the un-quoted version of them
         *
         */

        if(preg_match_all('#(background(-image)?|content)\:.*?url\(([\'"]|)?([^\'"]+?)\3\)#i', $source, $matches)) {
            return $matches[4];
        }

        return $fallback; // No images!

    }

    /**
     * ==========================================================================
     *  GET IMAGE URL FROM TEXT SOURCE
     * ==========================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type    | Description
     *  ---------- | ------- | --------------------------------------------------
     *  $source    | string  | The source text
     *  $sequence  | integer | Sequence of available image URLs
     *  $fallback  | string  | Fallback image URL if nothing matched
     *  ---------- | ------- | --------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     */

    public static function imageURL($source, $sequence = 1, $fallback = '?') {
        $images = self::imagesURL($source, array());
        return isset($images[$sequence - 1]) ? $images[$sequence - 1] : ($fallback == '?' ? Image::placeholder() : $fallback);
    }

    /**
     * ==========================================================================
     *  GET CLIENT IP ADDRESS
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    echo Get::IP();
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function IP() {
        $ip = 'N/A';
        if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && ! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            if(strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') > 0) {
                $addresses = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $ip = trim($addresses[0]);
            } else {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return Guardian::check($ip, '->IP') ? $ip : 'N/A';
    }

    /**
     * ==========================================================================
     *  GET CLIENT USER AGENT INFO
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    echo Get::UA();
     *
     * --------------------------------------------------------------------------
     *
     */

    public static function UA() {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * ==========================================================================
     *  GET TIMEZONE LIST
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::timezone());
     *    var_dump(Get::timezone('Asia/Jakarta'));
     *
     * --------------------------------------------------------------------------
     *
     */

     public static function timezone($identifier = null, $fallback = false, $format = '(UTC%1$s) %2$s &ndash; %3$s') {
        // http://pastebin.com/vBmW1cnX
        static $regions = array(
            DateTimeZone::AFRICA,
            DateTimeZone::AMERICA,
            DateTimeZone::ANTARCTICA,
            DateTimeZone::ASIA,
            DateTimeZone::ATLANTIC,
            DateTimeZone::AUSTRALIA,
            DateTimeZone::EUROPE,
            DateTimeZone::INDIAN,
            DateTimeZone::PACIFIC
        );
        $timezones = array();
        $timezone_offsets = array();
        $timezone_list = array();
        foreach($regions as $region) {
            $timezones = array_merge($timezones, DateTimeZone::listIdentifiers($region));
        }
        foreach($timezones as $timezone) {
            $tz = new DateTimeZone($timezone);
            $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
        }
        ksort($timezone_offsets);
        foreach($timezone_offsets as $timezone => $offset) {
            $offset_prefix = $offset < 0 ? '-' : '+';
            $offset_formatted = gmdate('H:i', abs($offset));
            $pretty_offset = $offset_prefix . $offset_formatted;
            $t = new DateTimeZone($timezone);
            $c = new DateTime(null, $t);
            $current_time = $c->format('g:i A');
            $timezone_list[$timezone] = sprintf($format, $pretty_offset, str_replace('_', ' ', $timezone), $current_time);
        }
        if( ! is_null($identifier)) {
            return isset($timezone_list[$identifier]) ? $timezone_list[$identifier] : $fallback;
        }
        return $timezone_list;
    }

}