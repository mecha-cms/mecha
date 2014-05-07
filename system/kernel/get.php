<?php

class Get {

    protected function __construct() {}
    protected function __clone() {}

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
            list($year, $month, $day, $hour, $minute, $second) = explode('-', $part[0]);
            $tree[$i] = array(
                'file_path' => $reference[$i],
                'file_name' => $base . '.txt',
                'time' => isset($part[0]) ? $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':' . $second : '0000-00-00 00:00:00',
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
     *    $files = Get::files('some/path', 'ASC', 'last_update');
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
        $text = preg_replace(array(
            '#(<.*?>|[\n\r\s\t\*\_\`>\#])+#',
            '# +#',
            '#&nbsp;#'
        ), array(
            ' ',
            ' ',
            ""
        ), $text);
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
     *    foreach(Get::pages() as $file_name) {
     *        echo $file_name . '<br>';
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
                foreach(glob($folder . '/*.txt') as $file) {
                    $part = explode('_', basename($file, '.txt'));
                    if($reference == $part[2] || (is_numeric($reference) && (string) Date::format($reference, 'Y-m-d-H-i-s') == (string) $part[0])) {
                        $results = self::extract($file);
                        break;
                    }
                }
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

        list($year, $month, $day, $hour, $minute, $second) = explode('-', $time);
        $results['url'] = $config->url . '/' . ($folder == ARTICLE ? $config->index->slug . '/' : "") . $results['slug'];
        $results['date']['unix'] = $results['id'] = (int) Date::format($results['time'], 'U');
        $results['date']['W3C'] = Date::format($results['time'], 'c');
        $date_GMT = new DateTime($results['date']['W3C']);
        $date_GMT->setTimeZone(new DateTimeZone('UTC'));
        $month_names = (array) $speak->months;
        $day_names = (array) $speak->days;
        $results['date']['GMT'] = $date_GMT->format('Y-m-d H:i:s');
        $results['date']['year'] = $year;
        $results['date']['month'] = $month_names[(int) $month - 1];
        $results['date']['day'] = $day_names[Date::format($results['time'], 'w')];
        $results['date']['month_number'] = $month;
        $results['date']['day_number'] = $day;
        $results['date']['hour'] = $hour;
        $results['date']['minute'] = $minute;
        $results['date']['second'] = $second;

        if( ! isset($results['author'])) $results['author'] = $config->author;

        $results['image'] = self::imageURL($content, 1);

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
        }

        $comments = self::comments($results['id']);
        $results['article_total_comments'] = $results['page_total_comments'] = $comments !== false ? count($comments) : 0;
        $results['article_total_comments_text'] = $results['page_total_comments_text'] = ($results['page_total_comments'] . ' ' . ($results['page_total_comments'] > 1 ? $speak->comments : $speak->comment));

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

    public static function imagesURL($source, $fallback = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAAA3NCSVQICAjb4U/gAAAADElEQVQImWOor68HAAL+AX7vOF2TAAAAAElFTkSuQmCC') {

        /**
         * Matched with...
         * 1. `![alt text](IMAGE URL)`
         * 2. `![alt text](IMAGE URL "optional title")`
         * ... and the single-quoted version of them
         */

        if(preg_match_all('#\!\[.*?\]\((.*?)(| +\'.[^\']*?\'| +".[^"]*?")\)#', $source, $matches)) {
            return $matches[1];
        }

        /**
         * Matched with...
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

        return $fallback; // No images!

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

    public static function imageURL($source, $sequence = 1, $fallback = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAAA3NCSVQICAjb4U/gAAAADElEQVQImWOor68HAAL+AX7vOF2TAAAAAElFTkSuQmCC') {
        $images = self::imagesURL($source, $fallback);
        return is_array($images) && isset($images[$sequence - 1]) ? $images[$sequence - 1] : $fallback;
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
            list($year, $month, $day, $hour, $minute, $second) = explode('-', $id);
            $results[] = array(
                'file_path' => $comment,
                'file_name' => basename($comment),
                'time' => $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':' . $second,
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