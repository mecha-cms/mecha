<?php


/**
 * Shields Manager
 * ---------------
 */

Route::accept(array($config->manager->slug . '/shield', $config->manager->slug . '/shield/(:any)'), function($folder = false) use($config, $speak) {
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    if( ! $folder) $folder = $config->shield;
    if( ! File::exist(SHIELD . DS . $folder)) {
        Shield::abort(); // Folder not found!
    }
    if(isset($_FILES) && ! empty($_FILES)) {
        Guardian::checkToken(Request::post('token'));
        $accepted_mimes = array(
            'application/download',
            'application/octet-stream',
            'application/x-compressed',
            'application/x-zip-compressed',
            'application/zip',
            'multipart/x-zip',
        );
        $accepted_extensions = array(
            'zip'
        );
        $name = $_FILES['file']['name'];
        $type = $_FILES['file']['type'];
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $path = basename($name, '.' . $extension);
        if( ! empty($name)) {
            if(File::exist(SHIELD . DS . $path)) {
                Notify::error(Config::speak('notify_file_exist', array('<code>' . $path . '/&hellip;</code>')));
            } else {
                if( ! in_array($type, $accepted_mimes) || ! in_array($extension, $accepted_extensions)) {
                    Notify::error(Config::speak('notify_invalid_file_extension', array('ZIP')));
                }
            }
        } else {
            Notify::error($speak->notify_error_no_file_selected);
        }
        if( ! Notify::errors()) {
            File::upload($_FILES['file'], SHIELD, Config::speak('notify_success_uploaded', array($speak->shield)));
            $P = array('data' => $_FILES);
            Weapon::fire('on_shield_update', array($P, $P));
            Weapon::fire('on_shield_construct', array($P, $P));
            if($uploaded = File::exist(SHIELD . DS . $name)) {
                Package::take($uploaded)->extract(); // Extract the ZIP file
                File::open($uploaded)->delete(); // Delete the ZIP file
                Guardian::kick($config->manager->slug . '/shield');
            }
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
    Config::set(array(
        'page_title' => $speak->shields . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'shield.php'
    ));
    $the_shields = glob(SHIELD . DS . '*', GLOB_ONLYDIR);
    sort($the_shields);
    Shield::define(array(
        'info' => Shield::info($folder),
        'the_shield_path' => $folder,
        'the_shield_contents' => Get::files(SHIELD . DS . $folder, SCRIPT_EXT, 'ASC', 'name'),
        'the_shields' => $the_shields
    ))->attach('manager', false);
});


/**
 * Shield Igniter
 * --------------
 */

Route::accept($config->manager->slug . '/shield/(:any)/ignite', function($folder = "") use($config, $speak) {
    if(Guardian::get('status') != 'pilot' || $folder === "") {
        Shield::abort();
    }
    if( ! $file = File::exist(SHIELD . DS . $folder)) {
        Shield::abort(); // Folder not found!
    }
    Config::set(array(
        'page_title' => $speak->creating . ': ' . $speak->shield . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'repair.shield.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $path = File::path($request['name']);
        if( ! Request::post('name')) {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->name)));
        } else {
            // Missing file extension
            if( ! preg_match('#^.*?\.(.+?)$#', $path)) {
                Notify::error($speak->notify_error_file_extension_missing);
            }
            if(File::exist(SHIELD . DS . $folder . DS . $path)) {
                Notify::error(Config::speak('notify_file_exist', array('<code>' . $path . '</code>')));
            }
            $accepted_extensions = explode(',', SCRIPT_EXT);
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if( ! in_array($extension, $accepted_extensions)) {
                Notify::error(Config::speak('notify_error_file_extension', array($extension)));
            }
        }
        $P = array('data' => $request);
        if( ! Notify::errors()) {
            File::write($request['content'])->saveTo(SHIELD . DS . $folder . DS . $path);
            Notify::success(Config::speak('notify_file_created', array('<code>' . basename($path) . '</code>')));
            Weapon::fire('on_shield_update', array($P, $P));
            Weapon::fire('on_shield_construct', array($P, $P));
            Guardian::kick($config->url . '/' . $config->manager->slug . '/shield/' . $folder);
        }
    }
    Shield::define(array(
        'the_shield' => $folder,
        'the_path' => "",
        'the_content' => ""
    ))->attach('manager', false);
});


/**
 * Shield Repair
 * -------------
 */

Route::accept($config->manager->slug . '/shield/(:any)/repair/file:(:all)', function($folder = "", $path = "") use($config, $speak) {
    if(Guardian::get('status') != 'pilot' || $folder === "" || $path === "") {
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
        'page_title' => $speak->editing . ': ' . basename($path) . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'repair.shield.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $name = File::path($request['name']);
        if( ! Request::post('name')) {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->name)));
        } else {
            // Missing file extension
            if( ! preg_match('#^.*?\.(.+?)$#', $name)) {
                Notify::error($speak->notify_error_file_extension_missing);
            }
            if($path != $name && File::exist(SHIELD . DS . $folder . DS . $name)) {
                Notify::error(Config::speak('notify_file_exist', array('<code>' . $name . '</code>')));
            }
            $accepted_extensions = explode(',', SCRIPT_EXT);
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if( ! in_array($extension, $accepted_extensions)) {
                Notify::error(Config::speak('notify_error_file_extension', array($extension)));
            }
        }
        $P = array('data' => $request);
        if( ! Notify::errors()) {
            File::open($file)->write($request['content'])->save();
            if($path != $name) {
                File::open($file)->moveTo(SHIELD . DS . $folder . DS . $name);
            }
            Notify::success(Config::speak('notify_file_updated', array('<code>' . basename($path) . '</code>')));
            Weapon::fire('on_shield_update', array($G, $P));
            Weapon::fire('on_shield_repair', array($G, $P));
            Guardian::kick($config->url . '/' . $config->manager->slug . '/shield/' . $folder . '/repair/file:' . File::url($name));
        }
    }
    Shield::define(array(
        'the_shield' => $folder,
        'the_path' => $path,
        'the_file' => $file,
        'the_content' => $content
    ))->attach('manager', false);
});


/**
 * Shield Killer
 * -------------
 */

Route::accept(array($config->manager->slug . '/shield/kill/id:(:any)', $config->manager->slug . '/shield/(:any)/kill/file:(:all)'), function($folder = "", $path = false) use($config, $speak) {
    if(Guardian::get('status') != 'pilot' || $folder === "") {
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
        'page_title' => $speak->deleting . ': ' . ($path ? basename($file) : $info->title) . $config->title_separator . $config->manager->title,
        'files' => Get::files(SHIELD . DS . $folder, '*'),
        'cargo' => DECK . DS . 'workers' . DS . 'kill.shield.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $P = array('data' => array('path' => $file));
        File::open($file)->delete();
        if($path) {
            Notify::success(Config::speak('notify_file_deleted', array('<code>' . basename($path) . '</code>')));
        } else {
            Notify::success(Config::speak('notify_success_deleted', array($speak->shield)));
        }
        Weapon::fire('on_shield_update', array($P, $P));
        Weapon::fire('on_shield_destruct', array($P, $P));
        Guardian::kick($config->manager->slug . '/shield' . ($path ? '/' . $folder : ""));
    } else {
        Notify::warning(Config::speak('notify_confirm_delete_', array($path ? '<code>' . File::path($path) . '</code>' : '<strong>' . $info->title . '</strong>')));
    }
    Shield::define(array(
        'the_shield' => $folder,
        'the_path' => $path,
        'info' => $info
    ))->attach('manager', false);
});


/**
 * Shield Attacher
 * ---------------
 */

Route::accept($config->manager->slug . '/shield/(attach|eject)/id:(:any)', function($path = "", $slug = "") use($config, $speak) {
    $d = DECK . DS . 'workers' . DS . 'repair.state.config.php';
    $new_config = file_exists($d) ? include $d : array();
    if($file = Get::state_config()) {
        Mecha::extend($new_config, $file);
    }
    $new_config['shield'] = $path == 'attach' ? $slug : 'normal';
    File::serialize($new_config)->saveTo(STATE . DS . 'config.txt', 0600);
    $G = array('data' => array('id' => $slug, 'action' => $path));
    $mode = $path == 'attach' ? 'mounted' : 'eject';
    Notify::success(Config::speak('notify_success_updated', array($speak->shield)));
    Weapon::fire('on_shield_update', array($G, $G));
    Weapon::fire('on_shield_' . $mode, array($G, $G));
    Weapon::fire('on_shield_' . md5($slug) . '_update', array($G, $G));
    Weapon::fire('on_shield_' . md5($slug) . '_' . $mode, array($G, $G));
    foreach(glob(SYSTEM . DS . 'log' . DS . 'asset.*.log') as $asset_cache) {
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