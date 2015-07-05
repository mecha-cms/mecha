<?php


/**
 * Asset Manager
 * -------------
 */

Route::accept(array($config->manager->slug . '/asset', $config->manager->slug . '/asset/(:num)'), function($offset = 1) use($config, $speak) {
    $offset = (int) $offset;
    $p = Request::get('path');
    $d = ASSET . File::path($p ? DS . $p : "");
    if( ! file_exists($d)) {
        Shield::abort(); // Folder not found!
    }
    // Disallow `htaccess` and `php` file extension(s)
    $ee = File::$config['file_extension_allow'];
    foreach(array('htaccess', 'php') as $e) {
        if(($_e = array_search($e, $ee)) !== false) {
            unset(File::$config['file_extension_allow'][$_e]);
        }
    }
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // New folder
        if(isset($request['folder'])) {
            if(trim($request['folder']) !== "") {
                $folder = explode(DS, File::path($request['folder']));
                foreach($folder as &$f) {
                    $f = Text::parse($f, '->safe_file_name');
                }
                unset($f);
                $folder = implode(DS, $folder);
                if(File::exist($d . DS . $folder)) {
                    Notify::error(Config::speak('notify_folder_exist', '<code>' . $folder . '</code>'));
                }
            } else {
                Notify::error(Config::speak('notify_error_empty_field', $speak->folder));
            }
            if( ! Notify::errors()) {
                File::pocket($d . DS . $folder);
                Notify::success(Config::speak('notify_folder_created', '<code>' . $folder . '</code>'));
                $fo = explode(DS, $folder);
                Session::set('recent_file_update', $fo[0]);
                $P = array('data' => $request);
                Weapon::fire('on_asset_update', array($P, $P));
                Weapon::fire('on_asset_construct', array($P, $P));
                if(isset($request['redirect'])) {
                    $folder = File::url($folder);
                    Guardian::kick($config->manager->slug . '/asset/' . $offset . '?path=' . urlencode($p ? $p . '/' . $folder : $folder));
                }
                Guardian::kick($config->manager->slug . '/asset/' . $offset);
            } else {
                Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
                    echo '<script>
        (function($) {
            $(\'.tab-area .tab[href$="#tab-content-2"]\').trigger("click");
        })(window.Zepto || window.jQuery);
        </script>';
                }, 11);
            }
        // New file
        } else {
            if(isset($_FILES) && ! empty($_FILES)) {
                File::upload($_FILES['file'], $d, function($name, $type, $size, $link) {
                    Session::set('recent_file_update', $name);
                });
                $P = array('data' => $_FILES);
                Weapon::fire('on_asset_update', array($P, $P));
                Weapon::fire('on_asset_construct', array($P, $P));
            }
            if( ! Notify::errors()) {
                Guardian::kick($config->manager->slug . '/asset/' . $offset . ($p ? '?path=' . urlencode($p) : ""));
            } else {
                Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
                    echo '<script>
        (function($) {
            $(\'.tab-area .tab[href$="#tab-content-3"]\').trigger("click");
        })(window.Zepto || window.jQuery);
        </script>';
                }, 11);
            }
        }
    }
    $filter = Request::get('q', "");
    $filter = $filter ? Text::parse($filter, '->safe_file_name') : false;
    $takes = glob($d . DS . '*', GLOB_NOSORT);
    if($filter) {
        foreach($takes as $k => $v) {
            if(strpos(File::N($v), $filter) === false) {
                unset($takes[$k]);
            }
        }
    }
    if($_files = Mecha::eat($takes)->chunk($offset, $config->per_page * 2)->vomit()) {
        $files = array();
        foreach($_files as $_file) {
            $files[] = File::inspect($_file);
        }
        $files = Mecha::eat($files)->order('ASC', 'path')->vomit();
        unset($_files);
    } else {
        $files = false;
    }
    Config::set(array(
        'page_title' => $speak->assets . $config->title_separator . $config->manager->title,
        'offset' => $offset,
        'files' => $files,
        'pagination' => Navigator::extract($takes, $offset, $config->per_page * 2, $config->manager->slug . '/asset'),
        'cargo' => DECK . DS . 'workers' . DS . 'asset.php'
    ));
    Shield::attach('manager', false);
});


/**
 * Asset Repair
 * ------------
 */

Route::accept($config->manager->slug . '/asset/repair/(file|files):(:all)', function($path = "", $old = "") use($config, $speak) {
    if(Guardian::get('status') !== 'pilot') {
        Shield::abort();
    }
    $old = File::path($old);
    $p = Request::get('path');
    $p = $p ? '?path=' . urlencode($p) : "";
    if( ! $file = File::exist(ASSET . DS . $old)) {
        Shield::abort(); // File not found!
    }
    Config::set(array(
        'page_title' => $speak->editing . ': ' . File::B($old) . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'repair.asset.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // Empty field
        if( ! Request::post('name')) {
            Notify::error(Config::speak('notify_error_empty_field', $speak->name));
        } else {
            // Safe file name
            $new = explode(DS, File::path($request['name']));
            foreach($new as &$n) {
                $n = Text::parse($n, '->safe_file_name');
            }
            unset($n);
            $new = implode(DS, $new);
            // Missing file extension
            if(is_file(ASSET . DS . $new) && ! preg_match('#^.*?\.(.+?)$#', $new)) {
                Notify::error($speak->notify_error_file_extension_missing);
            }
            // File name already exist
            if($old !== $new && File::exist(ASSET . DS . $new)) {
                Notify::error(Config::speak('notify_file_exist', '<code>' . File::B($new) . '</code>'));
            }
            $P = array('data' => $request);
            if( ! Notify::errors()) {
                if(Request::post('content')) {
                    File::open($file)->write($request['content'])->save();
                }
                File::open($file)->moveTo(ASSET . DS . $new);
                Notify::success(Config::speak('notify_' . (is_dir(ASSET . DS . $old) ? 'folder' : 'file') . '_updated', '<code>' . File::B($old) . '</code>'));
                $new = explode(DS, $new);
                Session::set('recent_file_update', $new[0]);
                Weapon::fire('on_asset_update', array($P, $P));
                Weapon::fire('on_asset_repair', array($P, $P));
                Guardian::kick($config->manager->slug . '/asset/1' . $p);
            }
        }
    }
    Shield::lot(array(
        'the_name' => $old,
        'the_content' => is_file(ASSET . DS . $old) && in_array(File::E($old), explode(',', SCRIPT_EXT)) ? File::open(ASSET . DS . $old)->read() : false
    ))->attach('manager', false);
});


/**
 * Asset Killer
 * ------------
 */

Route::accept($config->manager->slug . '/asset/kill/(file|files):(:all)', function($path = "", $name = "") use($config, $speak) {
    if(Guardian::get('status') !== 'pilot') {
        Shield::abort();
    }
    $name = File::path($name);
    $p = Request::get('path');
    $p = $p ? '?path=' . urlencode($p) : "";
    if(strpos($name, ';') !== false) {
        $deletes = explode(';', $name);
    } else {
        if( ! File::exist(ASSET . DS . $name)) {
            Shield::abort(); // File not found!
        } else {
            $deletes = array($name);
        }
    }
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . (count($deletes) === 1 ? File::B($name) : $speak->assets) . $config->title_separator . $config->manager->title,
        'files' => $deletes,
        'cargo' => DECK . DS . 'workers' . DS . 'kill.asset.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $info_path = array();
        $is_folder_or_file = count($deletes) === 1 && is_dir(ASSET . DS . $deletes[0]) ? 'folder' : 'file';
        foreach($deletes as $file_to_delete) {
            $_path = ASSET . DS . $file_to_delete;
            $info_path[] = $_path;
            File::open($_path)->delete();
        }
        $P = array('data' => array('files' => $info_path));
        Notify::success(Config::speak('notify_' . $is_folder_or_file . '_deleted', '<code>' . implode('</code>, <code>', $deletes) . '</code>'));
        Weapon::fire('on_asset_update', array($P, $P));
        Weapon::fire('on_asset_destruct', array($P, $P));
        Guardian::kick($config->manager->slug . '/asset/1' . $p);
    } else {
        Notify::warning(count($deletes) === 1 ? Config::speak('notify_confirm_delete_', '<code>' . File::path($name) . '</code>') : $speak->notify_confirm_delete);
    }
    Shield::attach('manager', false);
});


/**
 * Multiple Asset Killer
 * ---------------------
 */

Route::accept($config->manager->slug . '/asset/kill', function($path = "") use($config, $speak) {
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        if( ! isset($request['selected'])) {
            Notify::error($speak->notify_error_no_files_selected);
            Guardian::kick($config->manager->slug . '/asset/1');
        }
        $files = array();
        foreach($request['selected'] as $file) {
            $files[] = str_replace('%2F', '/', Text::parse($file, '->encoded_url'));
        }
        Guardian::kick($config->manager->slug . '/asset/kill/files:' . implode(';', $files));
    }
});