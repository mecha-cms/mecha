<?php

class Plugin extends __ {

    // Compare with current version
    public static function version($info, $v = null) {
        if(is_string($info)) {
            $info = self::info($info)->version;
        } else {
            $info = (object) $info;
            $info = isset($info->version) ? $info->version : '0.0.0';
        }
        return Mecha::version($v, $info);
    }

    /**
     * ==========================================================
     *  LOADING PLUGIN(S)
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    $plugins = Plugin::load();
     *
     * ----------------------------------------------------------
     *
     */

    public static function load($cache = true, $name = 'plugins.order.cache') {
        if($cache && $plugins = File::exist(CACHE . DS . $name)) {
            return File::open($plugins)->unserialize();
        }
        $plugins = array();
        foreach(glob(PLUGIN . DS . '*', GLOB_ONLYDIR) as $plugin) {
            $plugin = File::B($plugin);
            $plugins[$plugin] = (float) File::open(PLUGIN . DS . $plugin . DS . '__stack.txt')->read(10);
        }
        asort($plugins);
        File::serialize($plugins)->saveTo(CACHE . DS . $name, 0600);
        return $plugins;
    }

    /**
     * ==========================================================
     *  RELOAD PLUGIN(S)
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    Weapon::add('on_plugin_update', function() {
     *        Plugin:reload(); 
     *    });
     *
     * ----------------------------------------------------------
     *
     */

    public static function reload($cache = true, $name = 'plugins.order.cache') {
        if($cache) File::open(CACHE . DS . $name)->delete();
        self::load(false, $name);
    }

    /**
     * ==========================================================
     *  GET PLUGIN INFO
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    var_dump(Plugin::info('manager'));
     *
     * ----------------------------------------------------------
     *
     */

    public static function info($folder = null, $array = false) {
        $config = Config::get();
        $speak = Config::speak();
        // Check whether the localized "about" file is available
        if( ! $info = File::exist(PLUGIN . DS . $folder . DS . 'about.' . $config->language . '.txt')) {
            $info = PLUGIN . DS . $folder . DS . 'about.txt';
        }
        $info = Page::text(File::open($info)->read(), 'content', 'plugin:', array(
            'id' => self::exist($folder) ? $folder : false,
            'title' => Text::parse($folder, '->title'),
            'author' => $speak->anon,
            'url' => '#',
            'version' => '0.0.0',
            'content' => Config::speak('notify_not_available', $speak->description)
        ));
        return $array ? $info : Mecha::O($info);
    }

    /**
     * ==========================================================
     *  CHECK IF PLUGIN ALREADY EXIST
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    if($path = Plugin::exist('manager')) { ... }
     *
     * ----------------------------------------------------------
     *
     */

    public static function exist($name, $fallback = false) {
        $name = PLUGIN . DS . $name;
        return file_exists($name) && is_dir($name) ? $name : $fallback;
    }

}