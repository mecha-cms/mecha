<?php

/**
 * ==========================================================
 *  SHIELD ATTACHER
 * ==========================================================
 */

class Shield {

    private static function checkPath($name) {
        $name = trim($name, '\\/') . '.php';
        $config = Config::get();
        if(File::exist(SHIELD . DS . $config->shield . DS . $name)) {
            return SHIELD . DS . $config->shield . DS . $name;
        } else {
            if(File::exist(ROOT . DS . $name)) {
                return ROOT . DS . $name;
            } else {
                return $name;
            }
        }
    }

    /**
     * Minify HTML output
     */
    private static function sanitize_output($buffer) {
        $buffer = Filter::apply('before_sanitized', $buffer);
        $str = array(
            '/<\!--(?!\[if)([\s\S]+?)-->/' => "", // remove comments in HTML
            '/>[^\S ]+/s' => '>', // strip whitespaces after tags, except space
            '/[^\S ]+\</s' => '<', // strip whitespaces before tags, except space
            '/>\s{2,}</s' => '><' // strip multiple whitespaces between closing and opening tag
        );
        $buffer = preg_replace(array_keys($str), array_values($str), $buffer);
        return Filter::apply('after_sanitized', $buffer);
    }

    /**
     * ==========================================================
     *  GET SHIELD INFO
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    var_dump(Shield::info('aero'));
     *
     * -----------------------------------------------------------
     *
     */

    public static function info($folder = null) {
        $speak = Config::speak();
        $info = SHIELD . DS . (isset($folder) ? $folder : Config::get('shield')) . DS . 'about.txt';
        if(File::exist($info)) {
            $results = Text::toPage(File::open($info)->read());
            return Mecha::O($results);
        } else {
            return (object) array(
                'name' => $speak->unknown,
                'author' => $speak->unknown,
                'content_raw' => "",
                'content' => ""
            );
        }
    }

    /**
     * ==========================================================
     *  RENDER A PAGE
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    Shield::attach('article', true, false);
     *
     * ----------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type    | Description
     *  ---------- | ------- | ----------------------------------
     *  $name      | string  | Name of the shield
     *  $minify    | boolean | Minify HTML output?
     *  $cacheable | boolean | Create a cache file on page visit?
     *  ---------- | ------- | ----------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function attach($name, $minify = true, $cacheable = false) {

        $config = Config::get();
        $speak = Config::speak();
        $page = $config->page;
        $pages = $config->pages;
        $pager = $config->pagination;
        $manager = Guardian::happy();
        $base = explode('-', $name, 2);

        if($config->page_type == 'article') {
            $article = $page; // Create page alias for article
        }

        if(File::exist(self::checkPath($name))) {
            $shield = self::checkPath($name);
        } elseif(File::exist(self::checkPath($base[0]))) {
            $shield = self::checkPath($base[0]);
        } else {
            Guardian::abort(Config::speak('notify_file_not_exist', array('<code>' . self::checkPath($name) . '</code>')));
        }

        $cache = CACHE . DS . str_replace('/', '.', trim($_SERVER['REQUEST_URI'], '\\/')) . '.cache.txt';

        if($cacheable && File::exist($cache)) {
            echo File::open($cache)->read();
            exit;
        }

        ob_start($minify ? 'self::sanitize_output' : null);
        Weapon::fire('before_launch');

        require $shield;

        Weapon::fire('after_launch');
        ob_end_flush();

        if($cacheable) {
            File::write(ob_get_contents())->saveTo($cache);
        }

        exit;

    }

    /**
     * ==========================================================
     *  RENDER A 404 PAGE
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    [1]. Shield::abort();
     *
     *    [2]. Shield::abort('404-custom');
     *
     * ----------------------------------------------------------
     *
     */

    public static function abort($name = null, $minify = true) {

        $config = Config::get();
        $speak = Config::speak();
        $page = $config->page;
        $pages = array($page);
        $pager = $config->pagination;
        $manager = Guardian::happy();

        if( ! is_null($name) && File::exist(SHIELD . DS . $config->shield . DS . $name . '.php')) {
            $shield = SHIELD . DS . $config->shield . DS . $name . '.php';
        } else {
            $shield = SHIELD . DS . $config->shield . DS . '404.php';
        }

        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');

        ob_start($minify ? 'self::sanitize_output' : null);
        Weapon::fire('before_launch');

        require $shield;

        Weapon::fire('after_launch');
        ob_end_flush();

        exit;

    }

}