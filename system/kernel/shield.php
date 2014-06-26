<?php

/**
 * ==========================================================
 *  SHIELD ATTACHER
 * ==========================================================
 */

class Shield {

    private static function tracePath($name) {
        $name = rtrim($name, '\\/') . '.php';
        $config = Config::get();
        if($file = File::exist(SHIELD . DS . $config->shield . DS . ltrim($name, '\\/'))) {
            return $file;
        } else {
            if($file = File::exist(ROOT . DS . ltrim($name, '\\/'))) {
                return $file;
            }
        }
        return $name;
    }

    /**
     * Do Nothing
     * ----------
     */

    private static function desanitize_output($buffer) {
        $buffer = Filter::apply('before_sanitized', $buffer);
        return Filter::apply('after_sanitized', $buffer);
    }

    /**
     * Minify HTML Output
     * ------------------
     */

    private static function sanitize_output($buffer) {
        $buffer = Filter::apply('before_sanitized', $buffer);
        $str = array(
            '#\<\!--(?!\[if)([\s\S]+?)--\>#' => "", // remove comments in HTML
            '#\>[^\S ]+#s' => '>', // strip whitespaces after tags, except space
            '#[^\S ]+\<#s' => '<', // strip whitespaces before tags, except space
            '#\>\s{2,}\<#s' => '><' // strip multiple whitespaces between closing and opening tag
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
     * ----------------------------------------------------------
     *
     */

    public static function info($folder = null) {
        $config = Config::get();
        $speak = Config::speak();
        if(is_null($folder)) {
            $folder = $config->shield;
        }
        // Check whether the localized "about" file is available
        if( ! $info = File::exist(SHIELD . DS . $folder . DS . 'about.' . $config->language . '.txt')) {
            $info = SHIELD . DS . $folder . DS . 'about.txt';
        }
        $e_shield_page = "Name: " . $speak->unknown . "\n" .
             "Author: " . $speak->unknown . "\n" .
             "URL: #\n" .
             "Version: " . $speak->unknown . "\n" .
             "\n" . SEPARATOR . "\n" .
             "\n" . Config::speak('notify_not_available', array($speak->description));
        $shield_info = File::exist($info) ? Text::toPage(File::open($info)->read(), true, 'shield:') : Text::toPage($e_shield_page, true, 'shield:');
        return Mecha::O($shield_info);
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

        $info = array(
            'data' => array(
                'name' => $name,
                'minify' => $minify,
                'cacheable' => $cacheable
            ),
            'execution_time' => time(),
            'error' => Notify::errors()
        );

        Weapon::fire('before_shield_config_redefine', array($info));

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

        Weapon::fire('after_shield_config_redefine', array($info));

        if($file = File::exist(self::tracePath($name))) {
            $shield = $file;
        } elseif($file = File::exist(self::tracePath($base[0]))) {
            $shield = $file;
        } else {
            Guardian::abort(Config::speak('notify_file_not_exist', array('<code>' . self::tracePath($name) . '</code>')));
        }

        $cache = CACHE . DS . str_replace('/', '.', trim($_SERVER['REQUEST_URI'], '\\/')) . '.cache.txt';

        if($cacheable && File::exist($cache)) {
            echo File::open($cache)->read();
            exit;
        }

        ob_start($minify ? 'self::sanitize_output' : 'self::desanitize_output');

        Weapon::fire('before_launch');

        require $shield;

        Weapon::fire('after_launch');

        if($cacheable) {
            $info['data']['cache'] = $cache;
            File::write(ob_get_contents())->saveTo($cache);
            Weapon::fire('on_cache_construct', array($info));
        }

        ob_end_flush();

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

        $info = array(
            'data' => array(
                'name' => $name,
                'minify' => $minify
            ),
            'execution_time' => time(),
            'error' => Notify::errors()
        );

        Weapon::fire('before_shield_config_redefine', array($info));

        $config = Config::get();
        $speak = Config::speak();
        $page = $config->page;
        $pages = $config->pages;
        $pager = $config->pagination;
        $manager = Guardian::happy();

        Weapon::fire('after_shield_config_redefine', array($info));

        if( ! is_null($name) && File::exist(SHIELD . DS . $config->shield . DS . $name . '.php')) {
            $shield = SHIELD . DS . $config->shield . DS . $name . '.php';
        } else {
            $shield = SHIELD . DS . $config->shield . DS . '404.php';
        }

        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');

        ob_start($minify ? 'self::sanitize_output' : 'self::desanitize_output');

        Weapon::fire('before_launch');

        require $shield;

        Weapon::fire('after_launch');

        ob_end_flush();

        exit;

    }

}