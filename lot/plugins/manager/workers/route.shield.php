<?php


/**
 * Shield Manager
 * --------------
 */

Route::accept(array($config->manager->slug . '/shield', $config->manager->slug . '/shield/(:any)'), function($folder = false) use($config, $speak) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    if( ! $folder) $folder = $config->shield;
    if( ! $_folder = Shield::exist($folder)) {
        Shield::abort(); // Folder not found!
    }
    $destination = SHIELD;
    if(isset($_FILES) && ! empty($_FILES)) {
        $request = Request::post();
        Guardian::checkToken($request['token']);
        include __DIR__ . DS . 'task.package.ignite.php';
        if( ! Notify::errors()) {
            File::upload($_FILES['file'], $destination, function() use($speak) {
                Notify::clear();
                Notify::success(Config::speak('notify_success_uploaded', $speak->shield));
            });
            $P = array('data' => $_FILES);
            Weapon::fire(array('on_shield_update', 'on_shield_construct'), array($P, $P));
            if($package = File::exist($destination . DS . $name)) {
                if(is_file($package)) {
                    Package::take($package)->extract(); // Extract the ZIP file
                    File::open($package)->delete(); // Delete the ZIP file
                    Config::load(); // Refresh the configuration data ...
                    Guardian::kick($config->manager->slug . '/shield/' . $path);
                }
            }
        } else {
            $tab_id = 'tab-content-2';
            include __DIR__ . DS . 'task.js.tab.php';
        }
    }
    $folders = Get::closestFolders($destination, 'ASC', null, 'key:path');
    $info = Shield::info($folder);
    // `lot\shields\{$folder}\configurator.php`
    if( ! $info->configurator = File::exist($_folder . DS . 'configurator.php')) {
        // `lot\shields\{$folder}\workers\configurator.php`
        $info->configurator = File::exist($_folder . DS . 'workers' . DS . 'configurator.php');
    }
    Config::set(array(
        'page_title' => $speak->shields . $config->title_separator . $config->manager->title,
        'page' => $info,
        'cargo' => 'cargo.shield.php'
    ));
    Shield::lot(array(
        'segment' => 'shield',
        'folder' => $folder,
        'folders' => $folders,
        'files' => Mecha::O(Get::files($destination . DS . $folder, SCRIPT_EXT, 'ASC', 'path'))
    ))->attach('manager');
});


/**
 * Shield Repairer/Igniter
 * -----------------------
 */

Route::accept(array($config->manager->slug . '/shield/(:any)/ignite', $config->manager->slug . '/shield/(:any)/repair/file:(:all)'), function($folder = false, $file = false) use($config, $speak) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    if( ! $_folder = Shield::exist($folder)) {
        Shield::abort(); // Folder not found!
    }
    if($file === false) {
        $path = $content = $_file = false;
        $title = $speak->creating . ': ' . $speak->shield . $config->title_separator . $config->manager->title;
    } else {
        $path = File::path($file);
        if( ! $_file = File::exist($_folder . DS . $path)) {
            Shield::abort(); // File not found!
        }
        $content = File::open($_file)->read();
        $title = $speak->editing . ': ' . File::B($file) . $config->title_separator . $config->manager->title;
    }
    $G = array('data' => array('path' => $_file, 'name' => $path, 'content' => $content));
    Config::set(array(
        'page_title' => $title,
        'cargo' => 'repair.shield.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $name = Text::parse(File::path($request['name']), '->safe_path_name');
        if(trim($request['name']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', $speak->name));
        } else {
            if($path !== $name && File::exist($_folder . DS . $name)) {
                Notify::error(Config::speak('notify_file_exist', '<code>' . $name . '</code>'));
            }
            if(($e = File::E($name)) !== "") {
                if(strpos(',' . SCRIPT_EXT . ',', ',' . $e . ',') === false) {
                    Notify::error(Config::speak('notify_error_file_extension', $e));
                }
            } else {
                // Missing file extension
                Notify::error($speak->notify_error_file_extension_missing);
            }
        }
        $P = array('data' => $request);
        if( ! Notify::errors()) {
            $s = $_file !== false ? $_file : $_folder . DS . $name;
            File::write($request['content'])->saveTo($s);
            if($path !== false && $path !== $name) {
                File::open($s)->moveTo($_folder . DS . $name);
            }
            // Remove empty folder(s)
            $f = glob(File::D($s) . DS . '*', GLOB_NOSORT);
            if(empty($f)) {
                File::open(File::D($s))->delete();
            }
            Notify::success(Config::speak('notify_file_' . ($file === false ? 'created' : 'updated'), '<code>' . File::B($name) . '</code>'));
            Session::set('recent_item_update', File::B($name));
            Weapon::fire(array('on_shield_update', 'on_shield_repair'), array($G, $P));
            Guardian::kick($config->manager->slug . '/shield/' . $folder . ($file !== false ? '/repair/file:' . File::url($name) : ""));
        }
    }
    Shield::lot(array(
        'segment' => 'shield',
        'folder' => $folder,
        'path' => $path,
        'content' => $content
    ))->attach('manager');
});


/**
 * Shield Killer
 * -------------
 */

Route::accept(array($config->manager->slug . '/shield/kill/id:(:any)', $config->manager->slug . '/shield/(:any)/kill/file:(:all)'), function($folder = false, $file = false) use($config, $speak) {
    if( ! Guardian::happy(1) || $folder === "") {
        Shield::abort();
    }
    $info = Shield::info($folder);
    $path = $file !== false ? File::path($file) : false;
    if($file !== false) {
        if( ! $_file = File::exist(SHIELD . DS . $folder . DS . $path)) {
            Shield::abort(); // File not found!
        }
    } else {
        if( ! $_file = Shield::exist($folder)) {
            Shield::abort(); // Folder not found!
        }
    }
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . ($file !== false ? File::B($file) : $info->title) . $config->title_separator . $config->manager->title,
        'page' => $info,
        'cargo' => 'kill.shield.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $P = array('data' => array('path' => $_file));
        File::open($_file)->delete();
        if($_file !== false) {
            // Remove empty folder(s)
            $f = glob(File::D($_file) . DS . '*', GLOB_NOSORT);
            if(empty($f)) {
                File::open(File::D($_file))->delete();
            }
            Notify::success(Config::speak('notify_file_deleted', '<code>' . File::B($_file) . '</code>'));
        } else {
            Notify::success(Config::speak('notify_success_deleted', $speak->shield));
        }
        Weapon::fire(array('on_shield_update', 'on_shield_destruct'), array($P, $P));
        Guardian::kick($config->manager->slug . '/shield' . ($file !== false  ? '/' . $folder : ""));
    } else {
        Notify::warning(Config::speak('notify_confirm_delete_', $file !== false ? '<code>' . $path . '</code>' : '<strong>' . $info->title . '</strong>'));
    }
    Shield::lot(array(
        'segment' => 'shield',
        'folder' => $folder,
        'files' => Mecha::O(Get::files(SHIELD . DS . $folder, '*')),
        'path' => $path
    ))->attach('manager');
});


/**
 * Shield Attacher
 * ---------------
 */

Route::accept($config->manager->slug . '/shield/(attach|eject)/id:(:any)', function($path = "", $slug = "") use($config, $speak) {
    if( ! Guardian::happy(1) || ! Shield::exist($slug)) {
        Shield::abort();
    }
    $mode = $path === 'attach' ? 'mount' : 'eject';
    $G = array('data' => array('id' => $slug, 'action' => $path));
    Weapon::fire(array(
        'on_shield_update',
        'on_shield_' . $mode,
        'on_shield_' . md5($slug) . '_update',
        'on_shield_' . md5($slug) . '_' . $mode
    ), array($G, $G));
    $new_config = Get::state_config();
    $new_config['shield'] = $path === 'attach' ? $slug : 'normal';
    File::serialize($new_config)->saveTo(STATE . DS . 'config.txt', 0600);
    Notify::success(Config::speak('notify_success_updated', $speak->shield));
    foreach(glob(CACHE . DS . 'asset.*.log', GLOB_NOSORT) as $asset_cache) {
        File::open($asset_cache)->delete(); // clear cache log ...
    }
    Guardian::kick($config->manager->slug . '/shield/' . $slug);
});


/**
 * Shield Updater (Base)
 * ---------------------
 */

if($route = Route::is($config->manager->slug . '/shield/(:any)/update')) {
    Weapon::add('routes_before', function() use($config, $speak, $route) {
        if( ! Route::accepted($route['path']) && Request::method('post')) {
            Guardian::checkToken($_POST['token']);
            unset($_POST['token']); // remove token from request array
            Route::accept($route['path'], function() use($config, $speak, $route) {
                $request = Request::post(null, array());
                $s = $route['lot'][0];
                File::serialize($request)->saveTo(SHIELD . DS . $s . DS . 'states' . DS . 'config.txt', 0600);
                Notify::success(Config::speak('notify_success_updated', $speak->config));
                Guardian::kick(File::D($config->url_current));
            });
        }
    }, 1);
}