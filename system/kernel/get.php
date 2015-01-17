<?php

class Get {

    public static $placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    private static function pathTrace($detector, $folder = PAGE) {
        $results = false;
        foreach(glob($folder . DS . '*.{txt,draft}', GLOB_BRACE) as $path) {
            list($time, $kind, $slug) = explode('_', basename($path, '.' . pathinfo($path, PATHINFO_EXTENSION)));
            if($slug == $detector || (is_numeric($detector) && (string) date('Y-m-d-H-i-s', $detector) === (string) $time)) {
                $results = $path;
                break;
            }
        }
        return $results;
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
            'url' => str_replace(array(ROOT, '\\'), array(Config::get('url'), '/'), $input),
            'extension' => strtolower($extension),
            'last_update' => File::exist($input) ? filemtime($input) : null,
            'update' => File::exist($input) ? date('Y-m-d H:i:s', filemtime($input)) : null,
            'size_raw' => File::exist($input) ? filesize($input) : null,
            'size' => File::size($input, 'KB')
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
        if( ! File::exist($folder)) return false;
        $results = array();
        $results_inclusive = array();
        $extension = $extensions == '*' ? '.*?' : str_replace(array(', ', ','), '|', $extensions);
        $directory = new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS);
        foreach(new RegexIterator(new RecursiveIteratorIterator($directory), '#\.(' . $extension . ')$#i') as $file => $object) {
            if(empty($filter)) {
                $results_inclusive[] = self::fileExtract($file);
            } else {
                if(strpos($file, $filter) !== false) {
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
                    if(strpos($file, $filter) !== false) {
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
        if( ! File::exist($folder)) return false;
        $results = array();
        $results_inclusive = array();
        $extension = str_replace(', ', ',', $extensions);
        $files = strpos($extension, ',') !== false ? glob($folder . DS . '*.{' . $extension . '}', GLOB_BRACE) : glob($folder . DS . '*.' . $extension);
        foreach($files as $file) {
            if(empty($filter)) {
                $results_inclusive[] = self::fileExtract($file);
            } else {
                if(strpos($file, $filter) !== false) {
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
                    if(strpos($file, $filter) !== false) {
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
        $tags = include DECK . DS . 'workers' . DS . 'repair.state.tags.php';
        if($file = File::exist(STATE . DS . 'tags.txt')) {
            $tags = array_replace_recursive($tags, File::open($file)->unserialize());
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
                    list($time, $kind, $slug) = explode('_', basename($pages[$i], '.' . pathinfo($pages[$i], PATHINFO_EXTENSION)));
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
                    list($time, $kind, $slug) = explode('_', basename($pages[$i], '.' . pathinfo($pages[$i], PATHINFO_EXTENSION)));
                    if(strpos($slug, $value) !== false) {
                        $results[] = $pages[$i];
                    }
                }
                return ! empty($results) ? $results : false;
            } else { // if($key == 'keyword') ...
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

    public static function pageExtract($input) {
        if( ! $input) return false;
        $extension = pathinfo($input, PATHINFO_EXTENSION);
        list($time, $kind, $slug) = explode('_', basename($input, '.' . $extension));
        $kind = explode(',', $kind);
        return array(
            'path' => $input,
            'time' => Date::format($time),
            'last_update' => File::exist($input) ? filemtime($input) : null,
            'update' => File::exist($input) ? date('Y-m-d H:i:s', filemtime($input)) : null,
            'kind' => Converter::strEval($kind),
            'slug' => $slug,
            'state' => $extension == 'txt' ? 'published' : 'draft'
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
        return self::pageExtract($input);
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
        if( ! $input) return false;
        $extension = pathinfo($input, PATHINFO_EXTENSION);
        list($post, $id, $parent) = explode('_', basename($input, '.' . $extension));
        return array(
            'path' => $input,
            'time' => Date::format($id),
            'last_update' => File::exist($input) ? filemtime($input) : null,
            'update' => File::exist($input) ? date('Y-m-d H:i:s', filemtime($input)) : null,
            'post' => (int) Date::format($post, 'U'),
            'id' => (int) Date::format($id, 'U'),
            'parent' => $parent === '0000-00-00-00-00-00' ? null : (int) Date::format($parent, 'U'),
            'state' => $extension == 'txt' ? 'approved' : 'pending'
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
     *  Parameter      | Type   | Description
     *  -------------- | ------ | -----------------------------------------------
     *  $reference     | mixed  | Slug, ID, path or array of `Get::pageExtract()`
     *  $excludes      | array  | Exclude some fields from results
     *  $folder        | string | Folder of the pages
     *  $connector     | string | Path connector for page URL
     *  $filter_prefix | string | Filter prefix for `Text::toPage()`
     *  -------------- | ------ | -----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function page($reference, $excludes = array(), $folder = PAGE, $connector = '/', $filter_prefix = 'page:') {

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
                $results = self::pageExtract(self::pathTrace($reference, $folder));
            }
        }

        if( ! $results || ! File::exist($results['path'])) return false;

        /**
         * RULES: Do not do any tags looping, content Markdown-ing and
         * external file requesting if it is marked as the excluded
         * fields. For better performance.
         */

        $results = $results + Text::toPage(File::open($results['path'])->read(), (isset($excludes['content']) ? false : true), $filter_prefix);

        $content = isset($results['content_raw']) ? $results['content_raw'] : "";
        $time = str_replace(array(' ', ':'), '-', $results['time']);

        if($php_file = File::exist(dirname($results['path']) . DS . $results['slug'] . '.php')) {
            ob_start();
            include $php_file;
            $results['content'] = ob_get_clean();
        }

        $results['excerpt'] = "";
        $results['date'] = Date::extract($results['time']);
        $results['url'] = $config->url . $connector . $results['slug'];
        $results['id'] = $results['date']['unix'];

        if( ! isset($results['author'])) $results['author'] = Filter::apply($filter_prefix . 'author', Filter::apply('author', $config->author));

        if( ! isset($results['description'])) {
            $summary = self::summary($content, $config->excerpt_length, $config->excerpt_tail);
            $results['description'] = Filter::apply($filter_prefix . 'description', Filter::apply('description', $summary));
        }

        $content_test = isset($excludes['content']) && strpos($content, '<!--') !== false ? Filter::apply($filter_prefix . 'content', Filter::apply('content', Text::parse(Filter::apply($filter_prefix . 'shortcode', Filter::apply('shortcode', $content)))->to_html)) : $results['content'];
        if( ! isset($excludes['excerpt']) && strpos($content_test, '<!-- cut -->') !== false) {
            $parts = explode('<!-- cut -->', $content_test, 2);
            $results['excerpt'] = trim($parts[1]);
            $results['content'] = trim($parts[0]) . "\n\n<span id=\"read-more:" . $results['id'] . "\" aria-hidden=\"true\"></span>\n\n" . $results['excerpt'];
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
                $js_raw = Filter::apply('js', $js_raw);
                $results['js'] = Filter::apply('custom:js', $js_raw);
            } else {
                $results['css'] = $results['js'] = $results['css_raw'] = $results['js_raw'] = "";
            }
            $custom = $results['css'] . $results['js'];
        } else {
            $custom = "";
        }

        $results['image'] = self::imageURL($results['content'] . $custom, 1);

        $comments = self::comments($results['id'], 'ASC', (Guardian::happy() ? 'txt,hold' : 'txt'));
        $results['total_comments'] = $comments !== false ? count($comments) : 0;
        $results['total_comments_text'] = $results['total_comments'] . ' ' . ($results['total_comments'] > 1 ? $speak->comments : $speak->comment);

        if($comments && ! isset($excludes['comments'])) {
            $results['comments'] = array();
            foreach($comments as $comment) {
                $results['comments'][] = self::comment($comment);
            }
        }

        /**
         * Custom fields ...
         */

        if( ! isset($results['fields'])) $results['fields'] = array();

        if( ! isset($excludes['fields'])) {

            /**
             * Initialize custom fields with empty values so that users
             * don't have to write `isset()` function multiple times just
             * to prevent error messages because of the object key that
             * is not available in the old posts.
             */

            if($file = File::exist(STATE . DS . 'fields.txt')) {
                $fields = File::open($file)->unserialize();
            } else {
                $fields = array();
            }

            $init = array();

            foreach($fields as $key => $value) {
                if( ! isset($value['scope'])) $value['scope'] = 'all';
                if($value['scope'] == str_replace(':', "", $filter_prefix) || $value['scope'] == 'all') {
                    $init[$key] = "";
                }
            }

            /**
             * Start re-writing ...
             */

            foreach($results['fields'] as $key => $value) {
                $init[$key] = isset($value['value']) ? Filter::apply($filter_prefix . 'fields.' . $key, $value['value']) : false;
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
        if( ! $path || ! File::exist($path)) return false;
        $results['date'] = Date::extract($results['time']);
        $results = $results + Text::toPage(File::open($path)->read(), true, 'comment:');
        $results['email'] = Text::parse($results['email'])->to_decoded_html;
        $results['message_raw'] = $results['content_raw'];
        unset($results['content_raw']);
        $results['message'] = Filter::apply('message', $results['content']);
        unset($results['content']);
        $results['message'] = Filter::apply('comment:message', $results['message']);
        $results['permalink'] = '#';
        $posts = glob($response_to . DS . '*.txt');
        for($i = 0, $total = count($posts); $i < $total; ++$i) {
            list($time, $kind, $slug) = explode('_', basename($posts[$i], '.' . pathinfo($posts[$i], PATHINFO_EXTENSION)));
            if((int) Date::format($time, 'U') == $results['post']) {
                $results['permalink'] = $config->url . (is_null($connector) ? '/' . $config->index->slug . '/' : $connector) . $slug . '#comment-' . $results['id'];
                break;
            }
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
     *  $filter_prefix | string | See `Get::page()`
     *  -------------- | ------ | -----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function pageHeader($path, $folder = PAGE, $connector = '/', $filter_prefix = 'page:') {
        $config = Config::get();
        if(strpos($path, ROOT) === false) {
            $path = self::pathTrace($path, $folder); // By page slug, ID or time
        }
        if( ! $path) return false;
        $results = self::pageExtract($path) + Text::toPage($path, false, $filter_prefix);
        $results['date'] = Date::extract($results['time']);
        $results['id'] = (int) Date::format($results['time'], 'U');
        $results['url'] = $config->url . $connector . $results['slug'];
        if( ! isset($results['author'])) $results['author'] = Filter::apply($filter_prefix . 'author', Filter::apply('author', $config->author));
        if( ! isset($results['description'])) $results['description'] = Filter::apply($filter_prefix . 'description', Filter::apply('description', ""));
        if( ! isset($results['fields'])) $results['fields'] = array();
        if($file = File::exist(STATE . DS . 'fields.txt')) {
            $fields = File::open($file)->unserialize();
        } else {
            $fields = array();
        }
        $init = array();
        foreach($fields as $key => $value) {
            if( ! isset($value['scope'])) $value['scope'] = 'all';
            if($value['scope'] == str_replace(':', "", $filter_prefix) || $value['scope'] == 'all') {
                $init[$key] = "";
            }
        }
        foreach($results['fields'] as $key => $value) {
            $init[$key] = isset($value['value']) ? Filter::apply($filter_prefix . 'fields.' . $key, $value['value']) : false;
        }
        $results['fields'] = $init;
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
     *  $filter_prefix | string | See `Get::page()`
     *  -------------- | ------ | -----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function pageAnchor($path, $folder = PAGE, $connector = '/', $filter_prefix = 'page:') {
        $config = Config::get();
        if(strpos($path, ROOT) === false) {
            $path = self::pathTrace($path, $folder); // By page slug, ID or time
        }
        if($path && $handle = fopen($path, 'r')) {
            $results = self::pageExtract($path);
            $parts = explode(':', fgets($handle, 4096), 2);
            fclose($handle);
            $results['id'] = (int) Date::format($results['time'], 'U');
            $results['url'] = $config->url . $connector . $results['slug'];
            $results['title'] = Filter::apply($filter_prefix . 'title', Filter::apply('title', (isset($parts[1]) ? trim($parts[1]) : '?')));
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

        return $fallback == '?' ? self::$placeholder : $fallback; // No images!

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
        $images = self::imagesURL($source, $fallback);
        return is_array($images) && isset($images[$sequence - 1]) ? $images[$sequence - 1] : ($fallback == '?' ? self::$placeholder : $fallback);
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
        return Guardian::check($ip)->this_is_IP ? $ip : 'N/A';
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

}