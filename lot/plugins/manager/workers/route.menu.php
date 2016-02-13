<?php


/**
 * Menu Manager
 * ------------
 */

Route::accept($config->manager->slug . '/menu', function() use($config, $speak) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    $menus = Get::state_menu(null, array(), false);
    Config::set(array(
        'page_title' => $speak->menus . $config->title_separator . $config->manager->title,
        'cargo' => 'cargo.menu.php'
    ));
    Shield::lot(array(
        'segment' => 'menu',
        'files' => ! empty($menus) ? Mecha::O($menus) : false
    ))->attach('manager');
});


/**
 * Menu Repairer/Igniter
 * ---------------------
 */

Route::accept(array($config->manager->slug . '/menu/ignite', $config->manager->slug . '/menu/repair/key:(:any)'), function($key = false) use($config, $speak) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    $menus = Get::state_menu(null, array(), false);
    if( ! $key) {
        $menu_raw = "";
        $title = Config::speak('manager.title_new_', $speak->menu) . $config->title_separator . $config->manager->title;
    } else {
        if( ! isset($menus[$key])) {
            Shield::abort(); // Menu not found!
        }
        $menu_raw = Converter::toText($menus[$key]);
        $title = $speak->editing . ': ' . $speak->menu . $config->title_separator . $config->manager->title;
    }
    Config::set(array(
        'page_title' => $title,
        'cargo' => 'repair.menu.php'
    ));
    $G = array('data' => array('content' => $menu_raw));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // Check for invalid input
        if(preg_match('#(^|\n)(\t| {1,3})(?:[^ ])#', $request['content'])) {
            Notify::error($speak->notify_invalid_indent_character);
            Guardian::memorize($request);
        }
        $k = Text::parse(str_replace(array('Menu::', '()'), "", $request['key']), '->array_key');
        if( ! $key) {
            if(isset($menus[$k])) {
                Notify::error(Config::speak('notify_exist', '<code>Menu::' . $k . '()</code>'));
            }
        } else {
            unset($menus[$key]);
        }
        if($k === "" || $k === '__') {
            Notify::error(Config::speak('notify_error_empty_field', $speak->name));
        }
        $menus[$k] = Converter::toArray($request['content'], S, '    ');
        $P = array('data' => $request);
        if( ! Notify::errors()) {
            ksort($menus);
            File::serialize($menus)->saveTo(STATE . DS . 'menu.txt', 0600);
            Notify::success(Config::speak('notify_success_' . ( ! $key ? 'created' : 'updated'), $speak->menu));
            Weapon::fire(array('on_menu_update', 'on_menu_' . ( ! $key ? 'construct' : 'repair')), array($G, $P));
            Guardian::kick($key !== $k ? $config->manager->slug . '/menu' : $config->manager->slug . '/menu/repair/key:' . $key);
        }
    }
    Shield::lot(array(
        'segment' => 'menu',
        'id' => $key,
        'content' => $menu_raw
    ))->attach('manager');
});


/**
 * Menu Killer
 * -----------
 */

Route::accept($config->manager->slug . '/menu/kill/key:(:any)', function($key = false) use($config, $speak) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    $menus = Get::state_menu(null, array(), false);
    if( ! isset($menus[$key])) {
        Shield::abort(); // Menu not found!
    }
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . $speak->menu . $config->title_separator . $config->manager->title,
        'cargo' => 'kill.menu.php'
    ));
    $G = array('data' => $menus);
    $G['data']['key'] = $key;
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        unset($menus[$key]); // delete ...
        ksort($menus);
        $P = array('data' => $menus);
        $P['data']['key'] = $key;
        File::serialize($menus)->saveTo(STATE . DS . 'menu.txt', 0600);
        Notify::success(Config::speak('notify_success_deleted', $speak->menu));
        Weapon::fire(array('on_menu_update', 'on_menu_destruct'), array($G, $P));
        Guardian::kick($config->manager->slug . '/menu');
    } else {
        Notify::warning(Config::speak('notify_confirm_delete_', '<code>Menu::' . $key . '()</code>'));
    }
    Shield::lot(array(
        'segment' => 'menu',
        'id' => $key,
        'file' => Mecha::O($menus[$key])
    ))->attach('manager');
});