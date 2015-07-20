<?php

class Plugin extends Base {

    // Loading plugin(s) ...
    public static function load() {
        if($plugins_order = File::exist(CACHE . DS . 'plugins.order.cache')) {
            return File::open($plugins_order)->unserialize();
        }
        $plugins = array();
        $plugins_list = glob(PLUGIN . DS . '*' . DS . 'launch.php', GLOB_NOSORT);
        $plugins_payload = count($plugins_list);
        sort($plugins_list);
        for($i = 0; $i < $plugins_payload; ++$i) {
            $plugins[] = false;
        }
        for($j = 0; $j < $plugins_payload; ++$j) {
            $plugins_list[$j] = str_replace(PLUGIN . DS, "", dirname($plugins_list[$j]));
            if($overtake = File::exist(PLUGIN . DS . $plugins_list[$j] . DS . '__overtake.txt')) {
                $to_index = Mecha::edge((int) file_get_contents($overtake) - 1, 0, $plugins_payload - 1);
                array_splice($plugins, $to_index, 0, array($plugins_list[$j]));
            } else {
                $plugins[$j] = $plugins_list[$j];
            }
        }
        File::serialize($plugins)->saveTo(CACHE . DS . 'plugins.order.cache', 0600);
        unset($plugins_list, $plugins_order, $plugins_payload);
        return $plugins;
    }

    // Get plugin info by its folder
    public static function info($folder = null) {
        $config = Config::get();
        $speak = Config::speak();
        // Check whether the localized "about" file is available
        if( ! $info = File::exist(PLUGIN . DS . $folder . DS . 'about.' . $config->language . '.txt')) {
            $info = PLUGIN . DS . $folder . DS . 'about.txt';
        }
        $page_default = 'Title' . S . ' ' . ucwords(Text::parse($folder, '->text')) . "\n" .
            'Author' . S . ' ' . $speak->anon . "\n" .
            'URL' . S . ' #' . "\n" .
            'Version' . S . ' 0.0.0' . "\n" .
            "\n" . SEPARATOR . "\n" .
            "\n" . Config::speak('notify_not_available', $speak->description);
        return Mecha::O(Text::toPage(File::open($info)->read($page_default), 'content', 'plugin:'));
    }

}