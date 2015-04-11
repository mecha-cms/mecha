<?php

class Plugin extends Plugger {

    public static $bucket = array();

    public static function load() {
        if($plugins_order = File::exist(CACHE . DS . 'plugins.order.cache')) {
            return File::open($plugins_order)->unserialize();
        }
        $plugins = array();
        $plugins_list = glob(PLUGIN . DS . '*' . DS . 'launch.php');
        $plugins_payload = count($plugins_list);
        sort($plugins_list);
        for($i = 0; $i < $plugins_payload; ++$i) {
            $plugins[] = false;
        }
        for($j = 0; $j < $plugins_payload; ++$j) {
            if($overtake = File::exist(dirname($plugins_list[$j]) . DS . '__overtake.txt')) {
                $to_index = Mecha::edge((int) file_get_contents($overtake) - 1, 0, $plugins_payload - 1);
                array_splice($plugins, $to_index, 0, array(dirname($plugins_list[$j])));
            } else {
                $plugins[$j] = dirname($plugins_list[$j]);
            }
        }
        File::serialize($plugins)->saveTo(CACHE . DS . 'plugins.order.cache', 0600);
        return $plugins;
    }

}