<?php


/**
 * Assets Manager
 * --------------
 */

Route::accept(array($config->manager->slug . '/asset', $config->manager->slug . '/asset/(:num)'), function($offset = 1) use($config, $speak) {
    $offset = (int) $offset;
    $p = File::path(Request::get('path', ""));
    $dir_path = ASSET . ($p ? DS . $p : "") . DS;
    if($request = Request::post()) {
        // New folder
        if(isset($request['folder'])) {
            $folder = Text::parse($request['folder'], '->safe_file_name');
            if(trim($request['folder']) !== "") {
                if(File::exist($dir_path . $folder)) {
                    Notify::error(Config::speak('notify_folder_exist', '<code>' . $folder . '</code>'));
                }
            } else {
                Notify::error(Config::speak('notify_error_empty_field', $speak->folder));
            }
            if( ! Notify::errors()) {
                File::dir($dir_path . $folder);
                Notify::success(Config::speak('notify_folder_created', '<code>' . $folder . '</code>'));
                Session::set('recent_asset_updated', $folder);
                $P = array('data' => $request);
                Weapon::fire('on_asset_update', array($P, $P));
                Weapon::fire('on_asset_construct', array($P, $P));
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
                Guardian::checkToken(Request::post('token'));
                File::upload($_FILES['file'], ASSET, function($name, $type, $size, $link) {
                    Session::set('recent_asset_updated', $name);
                });
                $P = array('data' => $_FILES);
                Weapon::fire('on_asset_update', array($P, $P));
                Weapon::fire('on_asset_construct', array($P, $P));
            }
            if( ! Notify::errors()) {
                // do nothing ...
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
    $filter = Request::get('q', false);
    $filter = $filter ? Text::parse($filter, '->safe_file_name') : "";
    $takes = glob($dir_path . ($filter ? $filter : '*'));
    if($_files = Mecha::eat($takes)->chunk($offset, $config->per_page * 2)->vomit()) {
        $files = array();
        foreach($_files as $_file) {
            $files[] = Get::fileExtract($_file);
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
    Shield::lot('path', $p)->attach('manager', false);
});


/**
 * Asset Repair
 * ------------
 */

Route::accept($config->manager->slug . '/asset/repair/(file|files):(:all)', function($path = "", $old = "") use($config, $speak) {
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    $old = File::path($old);
    $dir_name = rtrim(dirname($old), DS);
    $old_name = ltrim(basename($old), DS);
    $p = Request::get('path', "");
    $p = $p ? '?path=' . $p : "";
    if( ! $file = File::exist(ASSET . DS . $dir_name . DS . $old_name)) {
        Shield::abort(); // File not found!
    }
    Config::set(array(
        'page_title' => $speak->editing . ': ' . $old_name . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'repair.asset.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // Empty field
        if( ! Request::post('name')) {
            Notify::error(Config::speak('notify_error_empty_field', $speak->name));
        } else {
            // Safe file name
            $new_name = Text::parse($request['name'], '->safe_file_name');
            // Missing file extension
            if(is_file(dirname($file) . DS . $new_name) && ! preg_match('#^.*?\.(.+?)$#', $new_name)) {
                Notify::error($speak->notify_error_file_extension_missing);
            }
            // File name already exist
            if($old_name !== $new_name && File::exist(dirname($file) . DS . $new_name)) {
                Notify::error(Config::speak('notify_file_exist', '<code>' . $new_name . '</code>'));
            }
            $P = array('data' => $request);
            if( ! Notify::errors()) {
                if(Request::post('content')) {
                    File::open($file)->write($request['content'])->save();
                }
                File::open($file)->renameTo($new_name);
                Notify::success(Config::speak('notify_file_updated', '<code>' . $old_name . '</code>'));
                Session::set('recent_asset_updated', $new_name);
                Weapon::fire('on_asset_update', array($P, $P));
                Weapon::fire('on_asset_repair', array($P, $P));
                Guardian::kick($config->manager->slug . '/asset' . $p);
            }
        }
    }
    Shield::lot('the_name', $old)->attach('manager', false);
});


/**
 * Asset Killer
 * ------------
 */

Route::accept($config->manager->slug . '/asset/kill/(file|files):(:all)', function($path = "", $name = "") use($config, $speak) {
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    $name = File::path($name);
    $p = Request::get('path', "");
    $p = $p ? '?path=' . $p : "";
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
        'page_title' => $speak->deleting . ': ' . (count($deletes) === 1 ? basename($name) : $speak->assets) . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'kill.asset.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $info_path = array();
        foreach($deletes as $file_to_delete) {
            $_path = ASSET . DS . $file_to_delete;
            $info_path[] = $_path;
            File::open($_path)->delete();
        }
        $P = array('data' => array('files' => $info_path));
        Notify::success(Config::speak('notify_file_deleted', '<code>' . implode('</code>, <code>', $deletes) . '</code>'));
        Weapon::fire('on_asset_update', array($P, $P));
        Weapon::fire('on_asset_destruct', array($P, $P));
        Guardian::kick($config->manager->slug . '/asset' . $p);
    } else {
        Notify::warning(count($deletes) === 1 ? Config::speak('notify_confirm_delete_', '<code>' . File::path($name) . '</code>') : $speak->notify_confirm_delete);
    }
    Shield::lot('the_name', $deletes)->attach('manager', false);
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
            Guardian::kick($config->manager->slug . '/asset');
        }
        $files = array();
        foreach($request['selected'] as $file) {
            $files[] = str_replace('%2F', '/', Text::parse($file, '->encoded_url'));
        }
        Guardian::kick($config->manager->slug . '/asset/kill/files:' . implode(';', $files));
    }
});