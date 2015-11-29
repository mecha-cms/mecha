<?php

class Plugin extends Base {

    // Loading plugin(s) ...
    public static function load($name = 'plugins.order.cache') {
        if($plugins = File::exist(CACHE . DS . $name)) {
            return File::open($plugins)->unserialize();
        }
        $plugins = array();
        foreach(glob(PLUGIN . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR) as $plugin) {
            $plugin = File::B($plugin);
            $plugins[$plugin] = (float) File::open(PLUGIN . DS . $plugin . DS . '__stack.txt')->read(10);
        }
        asort($plugins);
        File::serialize($plugins)->saveTo(CACHE . DS . $name, 0600);
        return $plugins;
    }

    // Reload the plugin(s) ...
    public static function reload($name = 'plugins.order.cache') {
        File::open(CACHE . DS . $name)->delete();
        return self::load($name);
    }

    // Get plugin info by its folder
    public static function info($folder = null, $array = false) {
        $config = Config::get();
        $speak = Config::speak();
        // Check whether the localized "about" file is available
        if( ! $info = File::exist(PLUGIN . DS . $folder . DS . 'about.' . $config->language . '.txt')) {
            $info = PLUGIN . DS . $folder . DS . 'about.txt';
        }
        $d = 'Title' . S . ' ' . Text::parse($folder, '->title') . "\n" .
             'Author' . S . ' ' . $speak->anon . "\n" .
             'URL' . S . ' #' . "\n" .
             'Version' . S . ' 0.0.0' . "\n" .
             "\n" . SEPARATOR . "\n" .
             "\n" . Config::speak('notify_not_available', $speak->description);
        $info = Text::toPage(File::open($info)->read($d), 'content', 'plugin:');
        return $array ? $info : Mecha::O($info);
    }

}