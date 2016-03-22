<?php


// Refresh plugin(s) order cache on every update event
Weapon::add('on_plugin_update', function() {
    Plugin::reload();
});


/**
 * Plugin Manager
 * --------------
 */

Route::accept(array($config->manager->slug . '/plugin', $config->manager->slug . '/plugin/(:num)'), function($offset = 1) use($config, $speak) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    $destination = PLUGIN;
    if(isset($_FILES) && ! empty($_FILES)) {
        $request = Request::post();
        Guardian::checkToken($request['token']);
        include __DIR__ . DS . 'task.package.ignite.php';
        if( ! Notify::errors()) {
            File::upload($_FILES['file'], $destination, function() use($speak) {
                Notify::clear();
                Notify::success(Config::speak('notify_success_uploaded', $speak->plugin));
            });
            if($package = File::exist($destination . DS . $name)) {
                if(is_file($package)) {
                    Package::take($package)->extract(); // Extract the ZIP file
                    File::open($package)->delete(); // Delete the ZIP file
                    $P = array('data' => $_FILES);
                    Weapon::fire(array(
                        'on_plugin_update',
                        'on_plugin_construct',
                        'on_plugin_' . md5($path) . '_update',
                        'on_plugin_' . md5($path) . '_construct'
                    ), array($P, $P));
                    $s = $destination . DS . $path . DS;
                    if(file_exists($s . 'launch__.php') || file_exists($s . '__launch.php') || file_exists($s . 'launch.php')) {
                        Weapon::fire(array('on_plugin_mount', 'on_plugin_' . md5($path) . '_mount'), array($P, $P));
                        Guardian::kick($config->manager->slug . '/plugin/' . $path); // Redirect to the plugin manager page
                    } else {
                        Guardian::kick($config->manager->slug . '/plugin?q=' . $path);
                    }
                }
            }
        } else {
            $tab_id = 'tab-content-2';
            include __DIR__ . DS . 'task.js.tab.php';
        }
    }
    $filter = Request::get('q', "");
    $filter = $filter ? Text::parse($filter, '->safe_file_name') : "";
    $s = Get::closestFolders($destination, 'ASC', 'path', $filter);
    if( ! $folders = Mecha::eat($s)->chunk($offset, $config->manager->per_page)->vomit()) {
        Shield::abort();
    }
    Config::set(array(
        'page_title' => $speak->plugins . $config->title_separator . $config->manager->title,
        'offset' => $offset,
        'pagination' => Navigator::extract($s, $offset, $config->manager->per_page, $config->manager->slug . '/plugin'),
        'cargo' => 'cargo.plugin.php'
    ));
    Shield::lot(array(
        'segment' => 'plugin',
        'folders' => Mecha::O($folders)
    ))->attach('manager');
});


/**
 * Plugin Configurator
 * -------------------
 */

Route::accept($config->manager->slug . '/plugin/(:any)', function($slug = 1) use($config, $speak) {
    if(is_numeric($slug)) {
        // It's an index page
        Route::execute($config->manager->slug . '/plugin/(:num)', array($slug));
    }
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    if( ! $_slug = Plugin::exist($slug)) {
        Shield::abort(); // Folder not found!
    }
    $info = Plugin::info($slug);
    // `lot\plugins\{$slug}\configurator.php`
    if( ! $info->configurator = File::exist($_slug . DS . 'configurator.php')) {
        // `lot\plugins\{$slug}\workers\configurator.php`
        $info->configurator = File::exist($_slug . DS . 'workers' . DS . 'configurator.php');
    }
    Config::set(array(
        'page_title' => $speak->managing . ': ' . $info->title . $config->title_separator . $config->manager->title,
        'page' => $info,
        'cargo' => 'repair.plugin.php'
    ));
    Shield::lot(array(
        'segment' => 'plugin',
        'folder' => $slug
    ))->attach('manager');
});


/**
 * Plugin Freezer/Igniter
 * ----------------------
 */

Route::accept($config->manager->slug . '/plugin/(freeze|fire)/id:(:any)', function($path = "", $slug = "") use($config, $speak) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    $offset = Request::get('o', 1);
    $mode = $path === 'freeze' ? 'eject' : 'mount';
    $G = array('data' => array('id' => $slug, 'action' => $path));
    Weapon::fire(array(
        'on_plugin_update',
        'on_plugin_' . $mode,
        'on_plugin_' . md5($slug) . '_update',
        'on_plugin_' . md5($slug) . '_' . $mode
    ), array($G, $G));
    Weapon::add('shield_lot_before', function() use($config, $speak, $mode, $slug, $offset) {
        if($mode === 'mount') {
            // Rename `pending.php` to `launch.php`
            File::open(PLUGIN . DS . $slug . DS . 'pending__.php')->renameTo('launch__.php');
            File::open(PLUGIN . DS . $slug . DS . '__pending.php')->renameTo('__launch.php');
            File::open(PLUGIN . DS . $slug . DS . 'pending.php')->renameTo('launch.php');
        } else {
            // Rename `launch.php` to `pending.php`
            File::open(PLUGIN . DS . $slug . DS . 'launch__.php')->renameTo('pending__.php');
            File::open(PLUGIN . DS . $slug . DS . '__launch.php')->renameTo('__pending.php');
            File::open(PLUGIN . DS . $slug . DS . 'launch.php')->renameTo('pending.php');
        }
        Notify::success(Config::speak('notify_success_updated', $speak->plugin));
        Guardian::kick($config->manager->slug . '/plugin/' . $offset);
    }, 1);
});


/**
 * Plugin Killer
 * -------------
 */

Route::accept($config->manager->slug . '/plugin/kill/id:(:any)', function($slug = "") use($config, $speak) {
    if( ! Guardian::happy(1) || ! $plugin = Plugin::exist($slug)) {
        Shield::abort();
    }
    $info = Plugin::info($slug, true);
    $info['slug'] = $slug;
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . $info['title'] . $config->title_separator . $config->manager->title,
        'page' => $info,
        'cargo' => 'kill.plugin.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $P = array('data' => array('id' => $slug));
        Weapon::fire(array(
            'on_plugin_update',
            'on_plugin_destruct',
            'on_plugin_' . md5($slug) . '_update',
            'on_plugin_' . md5($slug) . '_destruct'
        ), array($P, $P));
        File::open($plugin)->delete(); // delete later ...
        Notify::success(Config::speak('notify_success_deleted', $speak->plugin));
        Guardian::kick($config->manager->slug . '/plugin');
    } else {
        Notify::warning(Config::speak('notify_confirm_delete_', '<strong>' . $info['title'] . '</strong>'));
    }
    Shield::lot(array('segment' => 'plugin'))->attach('manager');
});


/**
 * Plugin Updater (Base)
 * ---------------------
 */

if($route = Route::is($config->manager->slug . '/plugin/(:any)/update')) {
    Weapon::add('routes_before', function() use($config, $speak, $route) {
        if( ! Route::accepted($route['path']) && Request::method('post')) {
            Guardian::checkToken($_POST['token']);
            unset($_POST['token']); // remove token from request array
            Route::accept($route['path'], function() use($config, $speak, $route) {
                $request = Request::post(null, array());
                $s = $route['lot'][0];
                File::serialize($request)->saveTo(PLUGIN . DS . $s . DS . 'states' . DS . 'config.txt', 0600);
                Notify::success(Config::speak('notify_success_updated', $speak->config));
                Guardian::kick(File::D($config->url_current));
            });
        }
    }, 1);
}