<?php


/**
 * Wizard Loader
 * -------------
 *
 * [1]. Guardian::wizard('menu');
 *
 */

Guardian::plug('wizard', function($name = "", $vars = array(), $folder = 'yap') {
    $name = File::path($name);
    $_file = false;
    $r = File::D(__DIR__, 3) . DS . 'languages' . DS;
    if($file = File::exist($r . Config::get('language') . DS . $folder . DS . $name . '.txt')) {
        $_file = $file;
    } else if($file = File::exist($r . 'en_US' . DS . $folder . DS . $name . '.txt')) {
        $_file = $file;
    } else if($file = File::exist(ROOT . DS . $name . '.txt')) {
        $_file = $file;
    } else if($file = File::exist($name . '.txt')) {
        $_file = $file;
    }
    $wizard = $_file ? Page::text(File::open($_file)->read(), 'content', 'wizard:', array('content_type' => 'HTML')) : false;
    return $wizard ? vsprintf($wizard['content'], $vars) : "";
});