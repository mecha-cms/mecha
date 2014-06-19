<?php

class Get {

    protected function __construct() {}
    protected function __clone() {}

    private static $placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    /**
     * Get Full Path of Page File by its Slug or ID
     * --------------------------------------------
     *
     * [1]. page-slug
     * [2]. 123456789
     *
     */

    private static function tracePath($detector, $folder = PAGE) {
        $results = false;
        foreach(glob($folder . DS . '*.txt') as $file_path) {
            list($time, $kind, $slug) = explode('_', basename($file_path, '.txt'));
            if($slug == $detector || (is_numeric($detector) && (string) Date::format($detector, 'Y-m-d-H-i-s') == (string) $time)) {
                $results = $file_path;
                break;
            }
        }
        return $results;
    }

    /**
     * =========================================================================
     *  CONVERT FILE PATH OF ARTICLE/PAGE INTO ARRAY OF INFO
     *
     *  File name pattern => `0000-00-00-00-00-00_1,2,3,4_page-slug.txt`
     * =========================================================================
     *
     * -- CODE: ----------------------------------------------------------------
     *
     *    [1]. var_dump(Get::extract('2014-04-12-07-00-05_1,2,3,4_page.txt'));
     *
     *    [2]. var_dump(Get::extract(glob(ARTICLE . DS . '*.txt')));
     *
     *    [3]. var_dump(Get::extract('articles', 'DESC', 'file_path'));
     *
     *    [4]. var_dump(Get::extract('articles', 'DESC', 'time', 'kind:1'));
     *
     * -------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type   | Description
     *  ---------- | ------ | --------------------------------------------------
     *  $reference | string | The file path, or keyword of specific method
     *  $reference | array  | Array of file path
     *  ---------- | ------ | --------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function extract($reference, $order = 'DESC', $sorter = 'file_path', $filter = "") {
        $tree = array();
        if( ! $reference) return false;
        if(is_string($reference)) {
            if($reference == 'articles') {
                $reference = self::articles($order, $filter);
            } elseif($reference == 'pages') {
                $reference = self::pages($order, $filter);
            } elseif($reference == 'comments') {
                return self::comments(null, $order, $sorter);
            }
        }
        if( ! is_array($reference)) {
            $reference = array($reference);
        }
        for($i = 0, $count = count($reference); $i < $count; ++$i) {
            $base = basename($reference[$i], '.txt');
            $part = explode('_', $base);
            $tree[$i] = array(
                'file_path' => $reference[$i],
                'file_name' => $base . '.txt',
                'time' => isset($part[0]) ? Date::format($part[0], 'Y-m-d H:i:s') : '0000-00-00 00:00:00',
                'update' => File::exist($reference[$i]) ? Date::format(filemtime($reference[$i]), 'Y-m-d H:i:s') : null,
                'kind' => isset($part[1]) ? Converter::strEval(explode(',', $part[1])) : array(),
                'slug' => isset($part[2]) ? $part[2] : ""
            );
        }
        return ! empty($tree) ? Mecha::eat($tree)->order($order, $sorter)->vomit() : false;
    }

    /**
     * =========================================================================
     *  EXTRACT FILE PATH FROM ALL OF THE EXISTING FILES IN A FOLDER
     *
     *  [1]. all => All files including the hidden files.
     *  [2]. recursive => All files without the hidden files.
     *  [3]. adjacent => All files without the hidden files that placed
     *       close to the selected folder.
     * =========================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter   | Type   | Desription
     *  ----------- | ------ | -------------------------------------------------
     *  $folder     | string | Path to folder of files you want to be listed
     *  $extensions | string | The file extensions
     *  $order      | string | Ascending or descending? ASC/DESC?
     *  $sorter     | string | The key of array item as sorting reference
     *  $filter     | string | Filter the resulted array by a keyword
     *  $output     | string | `all`, `recursive` or `adjacent` ?
     *  ----------- | ------ | -------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    private static function traceFiles($folder = ASSET, $extensions = '*', $order = 'DESC', $sorter = 'path', $filter = "", $output = 'all') {
        $config = Config::get();
        $tree = array();
        // Example: `*`, `txt`, `gif,jpg,jpeg,png`
        $extensions = $extensions == '*' ? '.*?' : preg_replace('# *\, *#', '|', $extensions);
        $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS));
        foreach(new RegexIterator($dir, '#.*?' . preg_quote($filter) . '.*?\.(' . $extensions . ')$#i') as $file => $object) {
            $info = pathinfo($file);
            $tree['all'][] = array(
                'path' => $file,
                'url' => str_replace(array(ROOT, '\\'), array($config->url, '/'), $file),
                'name' => $info['basename'],
                'dirname' => $info['dirname'],
                'extension' => strtolower($info['extension']),
                'last_update' => filemtime($file),
                'update' => Date::format(filemtime($file), 'Y-m-d H:i:s'),
                'size' => File::size($file, 'KB')
            );
            if(
                // Exclude file from results if the file name begins with
                // two underscores. Example: `__file-name.txt`
                strpos(basename($info['basename']), '__') !== 0 &&
                // Exclude all files in a folder from results if the
                // folder name begins with two underscores. Example: `__folder-name`
                strpos(basename($info['dirname']), '__') !== 0 &&
                // Linux?
                strpos(basename($info['basename']), '.') !== 0 &&
                strpos(basename($info['dirname']), '.') !== 0
            ) {
                $tree['recursive'][] = array(
                    'path' => $file,
                    'url' => str_replace(array(ROOT, '\\'), array($config->url, '/'), $file),
                    'name' => $info['basename'],
                    'dirname' => $info['dirname'],
                    'extension' => strtolower($info['extension']),
                    'last_update' => filemtime($file),
                    'update' => Date::format(filemtime($file), 'Y-m-d H:i:s'),
                    'size' => File::size($file, 'KB')
                );
                if(rtrim(dirname($file), '\\/') === rtrim($folder, '\\/')) {
                    $tree['adjacent'][] = array(
                        'path' => $file,
                        'url' => str_replace(array(ROOT, '\\'), array($config->url, '/'), $file),
                        'name' => $info['basename'],
                        'dirname' => $info['dirname'],
                        'extension' => strtolower($info['extension']),
                        'last_update' => filemtime($file),
                        'update' => Date::format(filemtime($file), 'Y-m-d H:i:s'),
                        'size' => File::size($file, 'KB')
                    );
                }
            }
        }
        return ! empty($tree[$output]) ? Mecha::eat($tree[$output])->order($order, $sorter)->vomit() : false;
    }

    /**
     * =========================================================================
     *  GET ALL FILES RECURSIVELY
     * =========================================================================
     *
     * -- CODE: ----------------------------------------------------------------
     *
     *    $files = Get::files(
     *        'some/path',
     *        'txt',
     *        'ASC',
     *        'last_update'
     *    );
     *
     *    $files = Get::files(
     *        'some/path',
     *        'gif,jpg,jpeg,png',
     *        'ASC',
     *        'last_update'
     *    );
     *
     * -------------------------------------------------------------------------
     *
     */

    public static function files($folder = ASSET, $extensions = '*', $order = 'DESC', $sorter = 'path', $filter = "") {
        return self::traceFiles($folder, $extensions, $order, $sorter, $filter, 'recursive');
    }

    /**
     * =========================================================================
     *  GET ADJACENT FILES
     * =========================================================================
     *
     * -- CODE: ----------------------------------------------------------------
     *
     *    $files = Get::adjacentFiles(
     *        'some/path',
     *        'txt',
     *        'ASC',
     *        'last_update'
     *    );
     *
     * -------------------------------------------------------------------------
     *
     */

    public static function adjacentFiles($folder = ASSET, $extensions = '*', $order = 'DESC', $sorter = 'path', $filter = "") {
        return self::traceFiles($folder, $extensions, $order, $sorter, $filter, 'adjacent');
    }

    /**
     * =========================================================================
     *  GET ALL FILES RECURSIVELY INCLUDING THE EXCLUDED FILES
     * =========================================================================
     *
     * -- CODE: ----------------------------------------------------------------
     *
     *    $files = Get::inclusiveFiles(
     *        'some/path',
     *        'txt',
     *        'ASC',
     *        'last_update'
     *    );
     *
     * -------------------------------------------------------------------------
     *
     */

    public static function inclusiveFiles($folder = ASSET, $extensions = '*', $order = 'DESC', $sorter = 'path', $filter = "") {
        return self::traceFiles($folder, $extensions, $order, $sorter, $filter, 'all');
    }

    /**
     * =========================================================================
     *  CREATE SUMMARY FROM LONG TEXT SOURCE
     * =========================================================================
     *
     * -- CODE: ----------------------------------------------------------------
     *
     *    $summary = Get::summary('Very very long text...');
     *
     * -------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter   | Type    | Description
     *  ----------- | ------- | ------------------------------------------------
     *  $text       | string  | The source text
     *  $maxchars   | integer | The maximum length of summary
     *  $tail       | string  | Character that follows at the end of the summary
     *  ----------- | ------- | ------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function summary($text, $maxchars = 100, $tail = '&hellip;') {
        $text = preg_replace(
            array(
                '#(<.*?>|[\n\r\s\t\*\_\`])+#',
                '# +#',
                '#\&nbsp\;#',
                '#[\-\~\=\#]{2,}#',
                '# *\. *([a-z])#i',
                '#<|>#'
            ),
            array(
                ' ',
                ' ',
                "",
                "",
                '. $1',
                ""
            ),
        $text);
        return trim(substr($text, 0, $maxchars) . ($maxchars < strlen($text) ? $tail : ""));
    }

    /**
     * =========================================================================
     *  EXTRACT ARRAY OF TAGS FROM TAG FILES
     * =========================================================================
     *
     * -- CODE: ----------------------------------------------------------------
     *
     *    $tags = Get::rawTags();
     *
     *    foreach($tags as $tag) {
     *        echo $tag['name'] . '<br>';
     *    }
     *
     * -------------------------------------------------------------------------
     *
     */

    public static function rawTags($order = 'ASC', $sorter = 'name') {
        $config = Config::get();
        $speak = Config::speak();
        if($file = File::exist(STATE . DS . 'tags.txt')) {
            $tags = unserialize(File::open($file)->read());
        } else {
            $tags = include STATE . DS . 'repair.tags.php';
        }
        return Mecha::eat($tags)->order($order, $sorter)->vomit();
    }

    /**
     * =========================================================================
     *  RETURN SPECIFIC TAG ITEM FILTERED BY ITS AVAILABLE DATA
     *
     *  It can be an ID, name or slug.
     * =========================================================================
     *
     * -- CODE: ----------------------------------------------------------------
     *
     *    $tag = Get::rawTagsBy('lorem-ipsum');
     *    echo $tag['name'] . '<br>';
     *
     * -------------------------------------------------------------------------
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
     * =========================================================================
     *  `Get::rawTags()` AS OBJECT
     * =========================================================================
     */

    public static function tags($order = 'ASC', $sorter = 'name') {
        return Mecha::O(self::rawTags($order, $sorter));
    }

    /**
     * =========================================================================
     *  `Get::rawTagsBy()` AS OBJECT
     * =========================================================================
     */

    public static function tagsBy($id_or_name_or_slug) {
        return Mecha::O(self::rawTagsBy($id_or_name_or_slug));
    }

    /**
     * =========================================================================
     *  GET LIST OF PAGE FILES
     * =========================================================================
     *
     * -- CODE: ----------------------------------------------------------------
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
     * -------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ---------------------------------------------------
     *  $order    | string | Ascending or descending? ASC/DESC?
     *  $filter   | string | Filter the resulted array by a keyword
     *  $folder   | string | Folder of the pages
     *  --------- | ------ | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function pages($order = 'DESC', $filter = "", $folder = PAGE) {
        $results = array();
        $pages = glob($folder . DS . '*.txt');
        $total_pages = count($pages);
        if($total_pages === 0) return false;
        if($order == 'DESC') {
            rsort($pages);
        } else {
            sort($pages);
        }
        if(empty($filter)) return $pages;
        if(strpos($filter, ':') !== false) {
            list($key, $value) = explode(':', $filter, 2);
            if($key == 'time') {
                for($i = 0; $i < $total_pages; ++$i) {
                    list($time, $kind, $slug) = explode('_', basename($pages[$i], '.txt'));
                    if(strpos($time, $value) !== false) {
                        $results[] = $pages[$i];
                    }
                }
                return ! empty($results) ? $results : false;
            } elseif($key == 'kind') {
                if(strpos($value, ',') !== false) {
                    $kinds = explode(',', $value);
                    for($i = 0; $i < $total_pages; ++$i) {
                        $name = basename($pages[$i], '.txt');
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
                        $name = basename($pages[$i], '.txt');
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
                    list($time, $kind, $slug) = explode('_', basename($pages[$i], '.txt'));
                    if(strpos($slug, $value) !== false) {
                        $results[] = $pages[$i];
                    }
                }
                return ! empty($results) ? $results : false;
            } else { // if($key == 'keyword') ...
                for($i = 0; $i < $total_pages; ++$i) {
                    if(strpos(basename($pages[$i], '.txt'), $value) !== false) {
                        $results[] = $pages[$i];
                    }
                }
                return ! empty($results) ? $results : false;
            }
        } else {
            for($i = 0; $i < $total_pages; ++$i) {
                if(strpos(basename($pages[$i], '.txt'), $filter) !== false) {
                    $results[] = $pages[$i];
                }
            }
            return ! empty($results) ? $results : false;
        }
        return false;
    }

    /**
     * =========================================================================
     *  GET LIST OF ARTICLE FILES
     * =========================================================================
     *
     * -- CODE: ----------------------------------------------------------------
     *
     *    foreach(Get::articles() as $path) {
     *        echo $path . '<br>';
     *    }
     *
     * -------------------------------------------------------------------------
     *
     */

    public static function articles($order = 'DESC', $filter = "") {
        return self::pages($order, $filter, ARTICLE);
    }

    /**
     * =========================================================================
     *  EXTRACT PAGE FILE INTO LIST OF PAGE DATA FROM ITS SLUG/FILE PATH
     * =========================================================================
     *
     * -- CODE: ----------------------------------------------------------------
     *
     *    var_dump(Get::page('about'));
     *
     * -------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter      | Type   | Description
     *  -------------- | ------ | ----------------------------------------------
     *  $reference     | mixed  | Slug, ID, path or array of `Get::extract()`
     *  $excludes      | array  | Exclude some fields from results
     *  $folder        | string | Folder of the pages
     *  $connector     | string | Path connector for page URL
     *  $filter_prefix | string | Filter prefix for `Text::toPage()`
     *  -------------- | ------ | ----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function page($reference, $excludes = array(), $folder = PAGE, $connector = '/', $filter_prefix = 'page:') {

        $config = Config::get();
        $speak = Config::speak();

        $excludes = array_flip($excludes);
        $results = false;

        // From `Get::extract('root:cabinet/pages/0000-00-00-00-00-00_1,2,3,4_page-slug.txt')`
        if(is_array($reference)) {
            $results = $reference;
        } else {
            // By path => `root:cabinet/pages/0000-00-00-00-00-00_1,2,3,4_page-slug.txt`
            if(strpos($reference, $folder) === 0) {
                $results = self::extract($reference);
            } else {
                // By slug => `page-slug` or by ID => 12345
                $results = self::extract(self::tracePath($reference, $folder));
            }
        }

        $results = $results ? $results[0] : false;

        if( ! $results || ! File::exist($results['file_path'])) return false;

        /**
         * RULES: Do not do any tags looping, content Markdown-ing and
         * external file requesting if it is marked as the excluded
         * fields. For better performance.
         */

        $results = $results + Text::toPage(File::open($results['file_path'])->read(), (isset($excludes['content']) ? false : true), $filter_prefix);

        $content = $results['content_raw'];
        $time = Date::format($results['time'], 'Y-m-d-H-i-s');

        if($php_file = File::exist(dirname($results['file_path']) . DS . $results['slug'] . '.php')) {
            ob_start();
            include $php_file;
            $php_rendered = ob_get_contents();
            ob_end_clean();
            $results['content'] = $php_rendered;
        }

        $results['url'] = $config->url . $connector . $results['slug'];
        $results['date'] = Date::extract($time);
        $results['update'] = Date::format(filemtime($results['file_path']), 'Y-m-d H:i:s');
        $results['id'] = $results['date']['unix'];

        if( ! isset($results['author'])) $results['author'] = Filter::apply($filter_prefix . 'author', Filter::apply('author', $config->author));

        if( ! isset($results['description'])) {
            $results['description'] = self::summary($content, $config->excerpt_length, $config->excerpt_tail);
        } else {
            $results['description'] = Text::parse($results['description'])->to_decoded_json;
        }

        if( ! isset($excludes['tags'])) {
            $tags = array();
            foreach($results['kind'] as $id) {
                $tags[] = self::rawTagsBy($id);
            }
            $results['tags'] = $tags;
        }

        if( ! isset($excludes['css']) || ! isset($excludes['js'])) {
            if($file = File::exist(CUSTOM . DS . $time . '.txt')) {
                $custom = explode(SEPARATOR, File::open($file)->read());
                $results['css_raw'] = isset($custom[0]) ? trim($custom[0]) : "";
                $css_raw = Filter::apply('shortcode', $results['css_raw']);
                $css_raw = Filter::apply('custom:shortcode', $css_raw);
                $css_raw = Filter::apply('css', $css_raw);
                $results['css'] = Filter::apply('custom:css', $css_raw);
                $results['js_raw'] = isset($custom[1]) ? trim($custom[1]) : "";
                $js_raw = Filter::apply('shortcode', $results['js_raw']);
                $js_raw = Filter::apply('custom:shortcode', $js_raw);
                $js_raw = Filter::apply('javascript', $js_raw);
                $results['js'] = Filter::apply('custom:javascript', $js_raw);
            } else {
                $results['css'] = $results['css_raw'] = "";
                $results['js'] = $results['js_raw'] = "";
            }
            $custom = $results['css'] . $results['js'];
        } else {
            $custom = "";
        }

        $results['image'] = self::imageURL($content . $custom, 1);

        $comments = self::comments($results['id']);
        $results['total_comments'] = $comments !== false ? count($comments) : 0;
        $results['total_comments_text'] = $results['total_comments'] . ' ' . ($results['total_comments'] > 1 ? $speak->comments : $speak->comment);

        if($comments && ! isset($excludes['comments'])) {
            $results['comments'] = array();
            foreach($comments as $comment) {
                $results['comments'][] = self::comment($comment['id']);
            }
        }

        /**
         * Custom fields ...
         */

        if( ! isset($excludes['fields'])) {

            /**
             * Initialize custom fields with empty values so that users
             * don't have to write `isset()` function multiple times just
             * to prevent error messages because of the object key that
             * is not available in the old posts.
             */

            if($file = File::exist(STATE . DS . 'fields.txt')) {
                $fields = unserialize(File::open($file)->read());
            } else {
                $fields = array();
            }

            $init = array();

            foreach($fields as $key => $value) {
                $init[$key] = "";
            }

            /**
             * Start re-writing ...
             */

            if(isset($results['fields'])) {
                foreach(Converter::strEval($results['fields']) as $key => $value) {
                    $init[$key] = isset($value['value']) ? Filter::apply($filter_prefix . 'fields.' . $key, $value['value']) : false;
                }
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
     * =========================================================================
     *  EXTRACT ARTICLE FILE INTO LIST OF ARTICLE DATA FROM ITS SLUG/FILE PATH
     * =========================================================================
     *
     * -- CODE: ----------------------------------------------------------------
     *
     *    var_dump(Get::article('lorem-ipsum'));
     *
     * -------------------------------------------------------------------------
     *
     */

    public static function article($reference, $excludes = array()) {
        return self::page($reference, $excludes, ARTICLE, '/' . Config::get('index')->slug . '/', 'article:');
    }

    /**
     * =========================================================================
     *  GET PAGE HEADERS ONLY
     * =========================================================================
     *
     * -- CODE: ----------------------------------------------------------------
     *
     *    var_dump(Get::pageHeader('lorem-ipsum'));
     *
     * -------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter      | Type   | Description
     *  -------------- | ------ | ----------------------------------------------
     *  $path          | string | The URL path of the page file, or a page slug
     *  $folder        | string | Folder of the pages
     *  $connector     | string | See `Get::pages()`
     *  $filter_prefix | string | See `Get::pages()`
     *  -------------- | ------ | ----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function pageHeader($path, $folder = PAGE, $connector = '/', $filter_prefix = 'page:') {
        $config = Config::get();
        if(strpos($path, ROOT) === false) {
            $path = self::tracePath($path, $folder);
        }
        if($path && $handle = fopen($path, 'r')) {
            list($time, $kind, $slug) = explode('_', basename($path, '.txt'));
            $results = array(
                'id' => (int) Date::format($time, 'U'),
                'time' => Date::format($time, 'Y-m-d H:i:s'),
                'update' => Date::format(filemtime($path), 'Y-m-d H:i:s'),
                'kind' => Converter::strEval(explode(',', $kind)),
                'slug' => $slug,
                'url' => $config->url . $connector . $slug
            );
            while(($buffer = fgets($handle, 4096)) !== false) {
                if(trim($buffer) === "" || trim($buffer) == SEPARATOR) {
                    fclose($handle);
                    break;
                }
                $field = explode(':', $buffer, 2);
                $key = Text::parse(strtolower(trim($field[0])))->to_array_key;
                $value = Converter::strEval(trim($field[1]));
                $value = Filter::apply($filter_prefix . $key, Filter::apply($key, $value));
                $results[$key] = $value;
            }
            if( ! isset($results['author'])) $results['author'] = Filter::apply($filter_prefix . 'author', Filter::apply('author', $config->author));
            if($file = File::exist(STATE . DS . 'fields.txt')) {
                $fields = unserialize(File::open($file)->read());
            } else {
                $fields = array();
            }
            $init = array();
            foreach($fields as $key => $value) {
                $init[$key] = "";
            }
            if(isset($results['fields'])) {
                foreach($results['fields'] as $key => $value) {
                    $init[$key] = isset($value['value']) ? Filter::apply($filter_prefix . 'fields.' . $key, $value['value']) : false;
                }
            }
            $results['fields'] = $init;
            $results['description'] = isset($results['description']) ? Text::parse($results['description'])->to_decoded_json : "";
            return Mecha::O($results);
        }
        return false;
    }

    /**
     * =========================================================================
     *  GET ARTICLE HEADERS ONLY
     * =========================================================================
     *
     * -- CODE: ----------------------------------------------------------------
     *
     *    var_dump(Get::articleHeader('lorem-ipsum'));
     *
     * -------------------------------------------------------------------------
     *
     */

    public static function articleHeader($path) {
        return self::pageHeader($path, ARTICLE, '/' . Config::get('index')->slug . '/', 'article:');
    }

    /**
     * =========================================================================
     *  GET MINIMUM DATA OF A PAGE
     * =========================================================================
     *
     * -- CODE: ----------------------------------------------------------------
     *
     *    var_dump(Get::pageAnchor('about'));
     *
     * -------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter      | Type   | Description
     *  -------------- | ------ | ----------------------------------------------
     *  $path          | string | The URL path of the page file, or a page slug
     *  $folder        | string | Folder of the pages
     *  $connector     | string | See `Get::pages()`
     *  $filter_prefix | string | See `Get::pages()`
     *  -------------- | ------ | ----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function pageAnchor($path, $folder = PAGE, $connector = '/', $filter_prefix = 'page:') {
        $config = Config::get();
        if(strpos($path, ROOT) === false) {
            $path = self::tracePath($path, $folder);
        }
        if($path && $handle = fopen($path, 'r')) {
            $parts = explode(':', fgets($handle), 2);
            list($time, $kind, $slug) = explode('_', basename($path, '.txt'));
            fclose($handle);
            return (object) array(
                'id' => (int) Date::format($time, 'U'),
                'time' => Date::format($time, 'Y-m-d H:i:s'),
                'update' => Date::format(filemtime($path), 'Y-m-d H:i:s'),
                'kind' => Converter::strEval(explode(',', $kind)),
                'slug' => $slug,
                'title' => Filter::apply($filter_prefix . 'title', Filter::apply('title', (isset($parts[1]) ? trim($parts[1]) : '?'))),
                'url' => $config->url . $connector . $slug
            );
        }
        return false;
    }

    /**
     * =========================================================================
     *  GET MINIMUM DATA OF AN ARTICLE
     * =========================================================================
     *
     * -- CODE: ----------------------------------------------------------------
     *
     *    var_dump(Get::articleAnchor('lorem-ipsum'));
     *
     * -------------------------------------------------------------------------
     *
     */

    public static function articleAnchor($path) {
        return self::pageAnchor($path, ARTICLE, '/' . Config::get('index')->slug . '/', 'article:');
    }

    /**
     * =========================================================================
     *  GET IMAGES URL FROM TEXT SOURCE
     * =========================================================================
     *
     * -- CODE: ----------------------------------------------------------------
     *
     *    var_dump(Get::imagesURL('some text', 'no-image.png'));
     *
     * -------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type   | Description
     *  ---------- | ------ | --------------------------------------------------
     *  $source    | string | The source text
     *  $fallback  | string | Fallback image URL if nothing matched
     *  ---------- | ------ | --------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function imagesURL($source, $fallback = '?') {

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

        if(preg_match_all('#\!\[.*?\]\((.*?)(| +\'.[^\']*?\'| +".[^"]*?")\)#', $source, $matches)) {
            return $matches[1];
        }

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

        if(preg_match_all('#<img (.*?)?src=(\'(.[^\']*?)\'|"(.[^"]*?)")(.*?)? ?\/?>#i', $source, $matches)) {
            return $matches[4];
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

        if(preg_match_all('#(background|background-image|content)\:(.*?)?url\((\'(.[^\']*?)\'|"(.[^"]*?)")\)#i', $source, $matches)) {
            return $matches[4];
        }

        return $fallback == '?' ? self::$placeholder : $fallback; // No images!

    }

    /**
     * =========================================================================
     *  GET IMAGE URL FROM TEXT SOURCE
     * =========================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type    | Description
     *  ---------- | ------- | -------------------------------------------------
     *  $source    | string  | The source text
     *  $sequence  | integer | Sequence of available image URLs
     *  $fallback  | string  | Fallback image URL if nothing matched
     *  ---------- | ------- | -------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     */

    public static function imageURL($source, $sequence = 1, $fallback = '?') {
        $images = self::imagesURL($source, $fallback);
        return is_array($images) && isset($images[$sequence - 1]) ? $images[$sequence - 1] : ($fallback == '?' ? self::$placeholder : $fallback);
    }

    /**
     * ==========================================================================
     *  GET COMMENTS BY PAGE TIME
     * ==========================================================================
     *
     * -- CODE: -----------------------------------------------------------------
     *
     *    var_dump(Get::comments('2014-04-02-15-15-15'));
     *
     * --------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type   | Description
     *  ---------- | ------ | ---------------------------------------------------
     *  $post_time | string | Post time as results filter
     *  $order     | string | Order results by ascending or descending? ASC/DESC?
     *  $sorter    | string | The key of array item as sorting reference
     *  ---------- | ------ | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function comments($post_time = null, $order = 'ASC', $sorter = 'id') {
        $results = array();
        $take = is_null($post_time) ? glob(RESPONSE . DS . '*.txt') : glob(RESPONSE . DS . Date::format($post_time, 'Y-m-d-H-i-s') . '_*.txt');
        foreach($take as $comment) {
            list($post, $id, $parent) = explode('_', basename($comment, '.txt'));
            $results[] = array(
                'file_path' => $comment,
                'file_name' => basename($comment),
                'time' => Date::format($id, 'Y-m-d H:i:s'),
                'update' => Date::format(filemtime($comment), 'Y-m-d H:i:s'),
                'post' => (int) Date::format($post, 'U'),
                'id' => (int) Date::format($id, 'U'),
                'parent' => $parent === '0000-00-00-00-00-00' ? null : (int) Date::format($parent, 'U')
            );
        }
        return Mecha::eat($results)->order($order, $sorter)->vomit();
    }

    /**
     * =========================================================================
     *  EXTRACT COMMENT FILE INTO LIST OF COMMENT DATA FROM ITS TIME/ID
     * =========================================================================
     *
     * -- CODE: ----------------------------------------------------------------
     *
     *    var_dump(Get::comment(1399334470));
     *
     * -------------------------------------------------------------------------
     *
     */

    public static function comment($reference, $excludes = array(), $folder = RESPONSE, $response_to = ARTICLE, $connector = null) {
        $config = Config::get();
        $results = array();
        $name = false;
        if(strpos(ROOT, $reference) === 0) {
            $name = basename($reference);
        } else {
            foreach(self::comments() as $comment) {
                if(
                    (int) Date::format($reference, 'U') === $comment['id'] || // By comment ID
                    ! is_numeric($reference) && (string) basename($reference) === (string) $comment['file_name'] // By comment name
                ) {
                    $results = $comment;
                    $name = $comment['file_name'];
                    break;
                }
            }
        }
        if( ! $name || ! $file = File::exist($folder . DS . $name)) return false;
        $results = $results + Text::toPage(File::open($file)->read(), true, 'comment:');
        $results['email'] = Text::parse($results['email'])->to_decoded_html;
        $results['message_raw'] = $results['content_raw'];
        $results['message'] = Filter::apply('message', $results['content']);
        $results['message'] = Filter::apply('comment:message', $results['message']);
        $results['permalink'] = '#';
        unset($results['content_raw']);
        unset($results['content']);
        foreach(glob($response_to . DS . '*.txt') as $posts) {
            list($time, $kind, $slug) = explode('_', basename($posts, '.txt'));
            if((int) Date::format($time, 'U') == $results['post']) {
                $results['permalink'] = $config->url . (is_null($connector) ? '/' . $config->index->slug . '/' : $connector) . $slug . '#comment-' . $results['id'];
                break;
            }
        }
        return (object) $results;
    }

}