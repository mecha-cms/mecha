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
    if( ! File::exist(SHIELD . DS . $folder)) {
        Shield::abort(); // Folder not found!
    }
    if(isset($_FILES) && ! empty($_FILES)) {
        Guardian::checkToken(Request::post('token'));
        $task_connect_path = SHIELD;
        include __DIR__ . DS . 'task.package.1.php';
        if( ! Notify::errors()) {
            File::upload($_FILES['file'], SHIELD, function() use($speak) {
                Notify::clear();
                Notify::success(Config::speak('notify_success_uploaded', $speak->shield));
            });
            $P = array('data' => $_FILES);
            Weapon::fire(array('on_shield_update', 'on_shield_construct'), array($P, $P));
            Weapon::fire(, array($P, $P));
            $task_connect_kick = 'shield';
            include __DIR__ . DS . 'task.package.2.php';
        } else {
            Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
                echo '<script>
(function($) {
    $(\'.tab-area .tab[href$="#tab-content-2"]\').trigger("click");
})(window.Zepto || window.jQuery);
</script>';
            }, 11);
        }
    }
    $folders = glob(SHIELD . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR);
    sort($folders);
    Config::set(array(
        'page_title' => $speak->shields . $config->title_separator . $config->manager->title,
        'page' => Shield::info($folder, true),
        'cargo' => 'cargo.shield.php'
    ));
    Shield::lot(array(
        'segment' => 'shield',
        'folder' => $folder,
        'folders' => $folders,
        'files' => Mecha::O(Get::files(SHIELD . DS . $folder, SCRIPT_EXT, 'ASC', 'path'))
    ))->attach('manager');
});


/**
 * Shield Igniter
 * --------------
 */

Route::accept($config->manager->slug . '/shield/(:any)/ignite', function($folder = "") use($config, $speak) {
    if( ! Guardian::happy(1) || $folder === "") {
        Shield::abort();
    }
    if( ! $file = File::exist(SHIELD . DS . $folder)) {
        Shield::abort(); // Folder not found!
    }
    Config::set(array(
        'page_title' => $speak->creating . ': ' . $speak->shield . $config->title_separator . $config->manager->title,
        'cargo' => 'repair.shield.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $path = File::path($request['name']);
        if( ! Request::post('name')) {
            Notify::error(Config::speak('notify_error_empty_field', $speak->name));
        } else {
            if(File::exist(SHIELD . DS . $folder . DS . $path)) {
                Notify::error(Config::speak('notify_file_exist', '<code>' . $path . '</code>'));
            }
            if(($extension = File::E($path)) !== "") {
                if(strpos(',' . SCRIPT_EXT . ',', ',' . $extension . ',') === false) {
                    Notify::error(Config::speak('notify_error_file_extension', $extension));
                }
            } else {
                // Missing file extension
                Notify::error($speak->notify_error_file_extension_missing);
            }
        }
        $P = array('data' => $request);
        if( ! Notify::errors()) {
            File::write($request['content'])->saveTo(SHIELD . DS . $folder . DS . $path);
            Notify::success(Config::speak('notify_file_created', '<code>' . File::B($path) . '</code>'));
            Session::set('recent_file_update', File::B($path));
            Weapon::fire(array('on_shield_update', 'on_shield_construct'), array($P, $P));
            Guardian::kick($config->manager->slug . '/shield/' . $folder);
        }
    }
    Shield::lot(array(
        'segment' => 'shield',
        'folder' => $folder,
        'path' => null,
        'content' => null
    ))->attach('manager');
});


/**
 * Shield Repairer
 * ---------------
 */

Route::accept($config->manager->slug . '/shield/(:any)/repair/file:(:all)', function($folder = "", $path = "") use($config, $speak) {
    if( ! Guardian::happy(1) || $folder === "" || $path === "") {
        Shield::abort();
    }
    $path = File::path($path);
    if( ! $file = File::exist(SHIELD . DS . $folder)) {
        Shield::abort(); // Folder not found!
    }
    if( ! $file = File::exist(SHIELD . DS . $folder . DS . $path)) {
        Shield::abort(); // File not found!
    }
    $content = File::open($file)->read();
    $G = array('data' => array('path' => $file, 'name' => $path, 'content' => $content));
    Config::set(array(
        'page_title' => $speak->editing . ': ' . File::B($path) . $config->title_separator . $config->manager->title,
        'cargo' => 'repair.shield.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $name = File::path($request['name']);
        if( ! Request::post('name')) {
            Notify::error(Config::speak('notify_error_empty_field', $speak->name));
        } else {
            if($path !== $name && File::exist(SHIELD . DS . $folder . DS . $name)) {
                Notify::error(Config::speak('notify_file_exist', '<code>' . $name . '</code>'));
            }
            if(($extension = File::E($name)) !== "") {
                if(strpos(',' . SCRIPT_EXT . ',', ',' . $extension . ',') === false) {
                    Notify::error(Config::speak('notify_error_file_extension', $extension));
                }
            } else {
                // Missing file extension
                Notify::error($speak->notify_error_file_extension_missing);
            }
        }
        $P = array('data' => $request);
        if( ! Notify::errors()) {
            File::open($file)->write($request['content'])->save();
            if($path !== $name) {
                File::open($file)->moveTo(SHIELD . DS . $folder . DS . $name);
            }
            Notify::success(Config::speak('notify_file_updated', '<code>' . File::B($path) . '</code>'));
            Weapon::fire(array('on_shield_update', 'on_shield_repair'), array($G, $P));
            Guardian::kick($config->manager->slug . '/shield/' . $folder . '/repair/file:' . File::url($name));
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

Route::accept(array($config->manager->slug . '/shield/kill/id:(:any)', $config->manager->slug . '/shield/(:any)/kill/file:(:all)'), function($folder = "", $path = false) use($config, $speak) {
    if( ! Guardian::happy(1) || $folder === "") {
        Shield::abort();
    }
    $info = Shield::info($folder);
    if($path) {
        $path = File::path($path);
        if( ! $file = File::exist(SHIELD . DS . $folder . DS . $path)) {
            Shield::abort(); // File not found!
        }
    } else {
        if( ! $file = File::exist(SHIELD . DS . $folder)) {
            Shield::abort(); // Folder not found!
        }
    }
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . ($path ? File::B($file) : $info->title) . $config->title_separator . $config->manager->title,
        'page' => $info,
        'cargo' => 'kill.shield.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $P = array('data' => array('path' => $file));
        File::open($file)->delete();
        if($path) {
            Notify::success(Config::speak('notify_file_deleted', '<code>' . File::B($path) . '</code>'));
        } else {
            Notify::success(Config::speak('notify_success_deleted', $speak->shield));
        }
        Weapon::fire(array('on_shield_update', 'on_shield_destruct'), array($P, $P));
        Guardian::kick($config->manager->slug . '/shield' . ($path ? '/' . $folder : ""));
    } else {
        Notify::warning(Config::speak('notify_confirm_delete_', $path ? '<code>' . File::path($path) . '</code>' : '<strong>' . $info->title . '</strong>'));
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
    $new_config = Get::state_config();
    $new_config['shield'] = $path === 'attach' ? $slug : 'normal';
    File::serialize($new_config)->saveTo(STATE . DS . 'config.txt', 0600);
    $G = array('data' => array('id' => $slug, 'action' => $path));
    $mode = $path === 'eject' ? 'eject' : 'mount';
    Notify::success(Config::speak('notify_success_updated', $speak->shield));
    Weapon::fire(array(
        'on_shield_update',
        'on_shield_' . $mode,
        'on_shield_' . md5($slug) . '_update',
        'on_shield_' . md5($slug) . '_' . $mode
    ), array($G, $G));
    foreach(glob(LOG . DS . 'asset.*.log', GLOB_NOSORT) as $asset_cache) {
        File::open($asset_cache)->delete();
    }
    Guardian::kick($config->manager->slug . '/shield/' . $slug);
});


/**
 * Shield Backup
 * -------------
 */

Route::accept($config->manager->slug . '/shield/backup/id:(:any)', function($folder = "") use($config, $speak) {
    $name = $folder . '.zip';
    Package::take(SHIELD . DS . $folder)->pack(ROOT . DS . $name, true);
    $G = array('data' => array('path' => ROOT . DS . $name, 'file' => ROOT . DS . $name));
    Weapon::fire('on_backup_construct', array($G, $G));
    Guardian::kick($config->manager->slug . '/backup/send:' . $name);
});