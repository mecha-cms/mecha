<?php

class Get {

    protected function __construct() {}
    protected function __clone() {}

    private static $placeholder = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAAA3NCSVQICAjb4U/gAAAADElEQVQImWOor68HAAL+AX7vOF2TAAAAAElFTkSuQmCC';

    // Find page's file path by a slug => `page-slug` or by ID => 12345
    private static function tracePath($detector, $folder = PAGE) {
        $results = false;
        foreach(glob($folder . '/*.txt') as $file_path) {
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
     *    [2]. var_dump(Get::extract(glob(ARTICLE . '/*.txt')));
     *
     * -------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type   | Detail
     *  ---------- | ------ | --------------------------------------------------
     *  $reference | string | The file path
     *  $reference | array  | Array of file path
     *  ---------- | ------ | --------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function extract($reference) {
        $tree = array();
        if( ! $reference) return false;
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
                'kind' => isset($part[1]) ? explode(',', $part[1]) : array(),
                'slug' => isset($part[2]) ? $part[2] : ""
            );
        }
        return $tree;
    }

    /**
     * =========================================================================
     *  BASIC FILE LISTER
     * =========================================================================
     *
     * -- CODE: ----------------------------------------------------------------
     *
     *    $files = Get::files('some/path', 'txt', 'ASC', 'last_update');
     *
     *    foreach($files as $file_details) {
     *        echo $file_details . '<br>';
     *    }
     *
     * -------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter   | Type   | Detail
     *  ----------- | ------ | -------------------------------------------------
     *  $folder     | string | The folder path of files to be listed
     *  $extensions | string | The file extensions
     *  $order      | string | Ascending or descending? ASC/DESC?
     *  $sorter     | string | The key of array item as sorting reference
     *  $filter     | string | Filter the resulted array by keyword
     *  ----------- | ------ | -------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function files($folder = CACHE, $extensions = 'txt', $order = 'DESC', $sorter = 'path', $filter = "") {
        $tree = array();
        if(strpos($extensions, ',') !== false) {
            $files = glob($folder . '/*.{' . $extensions . '}', GLOB_BRACE);
        } else {
            $files = glob($folder . '/*.' . $extensions);
        }
        $config = Config::get();
        if(empty($filter)) {
            for($i = 0, $count = count($files); $i < $count; ++$i) {
                $info = pathinfo($files[$i]);
                $tree[] = array(
                    'path' => $files[$i],
                    'url' => str_replace(array(ROOT, '\\'), array($config->url, '/'), $files[$i]),
                    'name' => $info['basename'],
                    'dirname' => $info['dirname'],
                    'extension' => strtolower($info['extension']),
                    'last_update' => filemtime($files[$i]),
                    'size' => File::size($files[$i], 'KB')
                );
            }
            return Mecha::eat($tree)->order($order, $sorter)->vomit();
        } else {
            for($i = 0, $count = count($files); $i < $count; ++$i) {
                $info = pathinfo($files[$i]);
                if(strpos($info['filename'], $filter) !== false) {
                    $tree[] = array(
                        'path' => $files[$i],
                        'url' => str_replace(array(ROOT, '\\'), array($config->url, '/'), $files[$i]),
                        'name' => $info['basename'],
                        'dirname' => $info['dirname'],
                        'extension' => strtolower($info['extension']),
                        'last_update' => filemtime($files[$i]),
                        'size' => File::size($files[$i], 'KB')
                    );
                }
            }
            return Mecha::eat($tree)->order($order, $sorter)->vomit();
        }
        return false;
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
     *  Parameter   | Type    | Detail
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
                '#(<.*?>|[\n\r\s\t\*\_\`>\#])+#',
                '# +#',
                '#\&nbsp\;#',
                '#[\-\~\=]{2,}#'
            ),
            array(
                ' ',
                ' ',
                "",
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
        return Mecha::eat(unserialize(File::open(STATE . '/tags.txt')->read()))
            ->order($order, $sorter)->vomit();
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
            if((is_numeric($filter) && (int) $filter == $tags[$i]['id']) || (is_string($filter) && (string) $filter == $tags[$i]['name']) || (is_string($filter) && (string) $filter == $tags[$i]['slug'])) {
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
     * -------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ---------------------------------------------------
     *  $order    | string | Ascending or descending? ASC/DESC?
     *  $filter   | string | Filter the resulted array by keyword
     *  $folder   | string | Folder of the pages
     *  --------- | ------ | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function pages($order = 'DESC', $filter = "", $folder = PAGE) {

        $results = array();
        $pages = glob($folder . '/*.txt');

        if($order == 'DESC') {
            rsort($pages);
        } else {
            sort($pages);
        }

        if(empty($filter)) {
            return $pages;
        } else {
            if(Config::get('page_type') == 'tag') { // A tag page
                for($i = 0, $count = count($pages); $i < $count; ++$i) {
                    $name = basename($pages[$i], '.txt');
                    if(
                        /**
                         * Micro optimizations are evils ...
                         * And I am the evil one ... muahahahaaa!!!
                         */
                        strpos($name, '_' . $filter . '_') !== false ||
                        strpos($name, ',' . $filter . ',') !== false ||
                        strpos($name, ',' . $filter . '_') !== false ||
                        strpos($name, '_' . $filter . ',') !== false
                    ) {
                        $results[] = $pages[$i];
                    }
                }
                return $results;
            } else {
                for($i = 0, $count = count($pages); $i < $count; ++$i) {
                    if(strpos(basename($pages[$i], '.txt'), $filter) !== false) {
                        $results[] = $pages[$i];
                    }
                }
                return $results;
            }
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
     *  Parameter  | Type   | Description
     *  ---------- | ------ | --------------------------------------------------
     *  $reference | mixed  | Slug, ID, file path or array of `Get::extract()`
     *  $excludes  | array  | Exclude some fields from results
     *  $folder    | string | Folder of the pages
     *  ---------- | ------ | --------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function page($reference, $excludes = array(), $folder = PAGE) {

        $config = Config::get();
        $speak = Config::speak();

        $excludes = array_flip($excludes);
        $results = false;

        // From `Get::extract("C:\wamp\www\cabinet/articles/0000-00-00-00-00-00_1,2,3,4_page-slug.txt")`
        if(is_array($reference)) {
            $results = $reference;
        } else {
            // Get page detail by full path => `C:\wamp\www\cabinet/articles/0000-00-00-00-00-00_1,2,3,4_page-slug.txt`
            if(strpos($reference, $folder) === 0) {
                $results = self::extract($reference);
            } else {
                // Get page detail by slug => `page-slug` or by ID => 12345
                $results = self::extract(self::tracePath($reference, $folder));
            }
        }

        $results = $results[0];

        if( ! File::exist($results['file_path'])) return false;

        /**
         * RULES: Do not do any tags looping, content Markdown-ing and
         * external file requesting if it is marked as the excluded
         * fields. For better performance.
         */

        $results = $results + Text::toPage(File::open($results['file_path'])->read(), (isset($excludes['content']) ? false : true));

        $content = $results['content_raw'];
        $time = Date::format($results['time'], 'Y-m-d-H-i-s');

        $results['url'] = $config->url . '/' . ($folder == ARTICLE ? $config->index->slug . '/' : "") . $results['slug'];
        $results['date'] = Date::extract($time);
        $results['id'] = $results['date']['unix'];

        if( ! isset($results['author'])) $results['author'] = $config->author;

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
            $file = CUSTOM . '/' . $time . '.txt';
            if(File::exist($file)) {
                $custom = explode(SEPARATOR, File::open($file)->read());
                $results['css_raw'] = isset($custom[0]) ? trim($custom[0]) : "";
                $results['css'] = Filter::apply('shortcode', $results['css_raw']);
                $results['js_raw'] = isset($custom[1]) ? trim($custom[1]) : "";
                $results['js'] = Filter::apply('shortcode', $results['js_raw']);
            } else {
                $results['css'] = $results['css_raw'] = "";
                $results['js'] = $results['js_raw'] = "";
            }
            $custom = $results['css'] . $results['js'];
        } else {
            $custom = "";
        }

        $results['image'] = self::imageURL($content . $custom, 1, (File::exist(ROOT . '/favicon.ico') ? $config->url . '/favicon.ico' : self::$placeholder));

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
            $init = array();
            foreach(unserialize(File::open(STATE . '/fields.txt')->read()) as $key => $value) {
                $init[$key] = "";
            }

            /**
             * Start re-writing ...
             */
            if(isset($results['fields'])) {
                $extra = Mecha::A(Text::parse($results['fields'])->to_decoded_json);
                if($results['fields'] != '{}' || ( ! empty($extra) && (string) $extra !== 'null')) {
                    foreach($extra as $key => $value) {
                        if($value['type'] == 'boolean') {
                            $init[$key] = isset($value['value']) && ! empty($value['value']) ? true : false;
                        } else {
                            $init[$key] = $value['value'];
                        }
                    }
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
        return self::page($reference, $excludes, ARTICLE);
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
     *  Parameter | Type   | Description
     *  --------- | ------ | ---------------------------------------------------
     *  $path     | string | The URL path of the page file, or a page slug
     *  $folder   | string | Folder of the pages
     *  --------- | ------ | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function pageHeader($path, $folder = PAGE) {
        $config = Config::get();
        if(strpos($path, ROOT) === false) {
            $path = self::tracePath($path, $folder);
        }
        if($handle = fopen($path, 'r')) {
            list($time, $kind, $slug) = explode('_', basename($path, '.txt'));
            $results = array(
                'id' => (int) Date::format($time, 'U'),
                'time' => Date::format($time, 'Y-m-d H:i:s'),
                'kind' => explode(',', $kind),
                'slug' => $slug,
                'url' => $config->url . '/' . ($folder == ARTICLE ? $config->index->slug . '/' : "") . $slug
            );
            while(($buffer = fgets($handle, 4096)) !== false) {
                if(trim($buffer) === "" || trim($buffer) == SEPARATOR) {
                    fclose($handle);
                    break;
                }
                $field = explode(':', $buffer, 2);
                $value = trim($field[1]);
                if(is_numeric($value)) {
                    $value = (int) $value;
                }
                if(preg_match('#^(true|false)$#', strtolower($value))) {
                    $value = $value == 'true' ? true : false;
                }
                $results[Text::parse(strtolower(trim($field[0])))->to_array_key] = $value;
            }
            $init = array();
            foreach(unserialize(File::open(STATE . '/fields.txt')->read()) as $key => $value) {
                $init[$key] = "";
            }
            if(isset($results['fields'])) {
                $extra = Mecha::A(Text::parse($results['fields'])->to_decoded_json);
                if($results['fields'] != '{}' || ( ! empty($extra) && (string) $extra !== 'null')) {
                    foreach($extra as $key => $value) {
                        if($value['type'] == 'boolean') {
                            $init[$key] = isset($value['value']) && ! empty($value['value']) ? true : false;
                        } else {
                            $init[$key] = $value['value'];
                        }
                    }
                }
            }
            $results['description'] = isset($results['description']) ? Text::parse($results['description'])->to_decoded_json : "";
            $results['fields'] = $init;
            return (object) $results;
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
        return self::pageHeader($path, ARTICLE);
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
     *  Parameter | Type   | Description
     *  --------- | ------ | ---------------------------------------------------
     *  $path     | string | The URL path of the page file, or a page slug
     *  $folder   | string | Folder of the pages
     *  --------- | ------ | ---------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function pageAnchor($path, $folder = PAGE) {
        $config = Config::get();
        if(strpos($path, ROOT) === false) {
            $path = self::tracePath($path, $folder);
        }
        if($handle = fopen($path, 'r')) {
            $parts = explode(':', fgets($handle), 2);
            list($time, $kind, $slug) = explode('_', basename($path, '.txt'));
            fclose($handle);
            return (object) array(
                'id' => (int) Date::format($time, 'U'),
                'time' => Date::format($time, 'Y-m-d H:i:s'),
                'kind' => explode(',', $kind),
                'slug' => $slug,
                'title' => isset($parts[1]) ? trim($parts[1]) : '?',
                'url' => $config->url . '/' . ($folder == ARTICLE ? $config->index->slug . '/' : "") . $slug
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
        return self::pageAnchor($path, ARTICLE);
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
         * 1. `![alt text](IMAGE URL)`
         * 2. `![alt text](IMAGE URL "optional title")`
         * ... and the single-quoted version of them
         */

        if(preg_match_all('#\!\[.*?\]\((.*?)(| +\'.[^\']*?\'| +".[^"]*?")\)#', $source, $matches)) {
            return $matches[1];
        }

        /**
         * Matched with ...
         * 1. `<img src="IMAGE URL">`
         * 2. `<img foo="bar" src="IMAGE URL">`
         * 3. `<img src="IMAGE URL" foo="bar">`
         * 4. `<img src="IMAGE URL"/>`
         * 5. `<img foo="bar" src="IMAGE URL"/>`
         * 6. `<img src="IMAGE URL" foo="bar"/>`
         * 7. `<img src="IMAGE URL" />`
         * 8. `<img foo="bar" src="IMAGE URL" />`
         * 9. `<img src="IMAGE URL" foo="bar" />`
         * ... and the uppercased version of them, and the single-quoted version of them
         */

        if(preg_match_all('#<img (.*?)?src=(\'(.[^\']*?)\'|"(.[^"]*?)")(.*?)? ?\/?>#i', $source, $matches)) {
            return $matches[4];
        }

        /**
         * Matched with ...
         * 1. `background:url("IMAGE URL")`
         * 2. `background-image:url("IMAGE URL")`
         * 3. `background: url("IMAGE URL")`
         * 4. `background-image: url("IMAGE URL")`
         * 5. `background: foo url("IMAGE URL")`
         * 6. `background-image: foo url("IMAGE URL")`
         * 7. `content:url("IMAGE URL")`
         * 8. `content: url("IMAGE URL")`
         * ... and the uppercased version of them, and the single-quoted version of them, and the un-quoted version of them
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

        foreach(glob(RESPONSE . '/*.txt') as $comment) {
            list($post, $id, $parent) = explode('_', basename($comment, '.txt'));
            $results[] = array(
                'file_path' => $comment,
                'file_name' => basename($comment),
                'time' => Date::format($id, 'Y-m-d H:i:s'),
                'post' => (int) Date::format($post, 'U'),
                'id' => (int) Date::format($id, 'U'),
                'parent' => $parent === '0000-00-00-00-00-00' ? null : (int) Date::format($parent, 'U')
            );
        }

        if( ! is_null($post_time)) {
            $clone = $results;
            $results = array();
            foreach($clone as $comment) {
                if((int) Date::format($post_time, 'U') == $comment['post']) {
                    $results[] = $comment;
                }
            }
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

    public static function comment($id) {

        $config = Config::get();
        $results = array();

        foreach(self::comments() as $comment) {
            if((int) Date::format($id, 'U') == $comment['id']) {
                $results = $comment;
                $name = $comment['file_name'];
                break;
            }
        }

        if( ! File::exist(RESPONSE . '/' . $name)) return false;

        $results = $results + Text::toPage(File::open(RESPONSE . '/' . $name)->read());

        $results['email'] = Text::parse($results['email'])->to_decoded_html;
        $results['message_raw'] = $results['content_raw'];
        $results['message'] = Filter::apply('comment', Text::parse($results['content'])->to_html);
        $results['permalink'] = '#';

        unset($results['content_raw']);
        unset($results['content']);

        foreach(glob(ARTICLE . '/*.txt') as $posts) {
            list($time, $kind, $slug) = explode('_', basename($posts, '.txt'));
            if((int) Date::format($time, 'U') == $results['post']) {
                $results['permalink'] = $config->url . '/' . $config->index->slug . '/' . $slug . '#comment-' . $results['id'];
                break;
            }
        }

        return (object) $results;

    }

}