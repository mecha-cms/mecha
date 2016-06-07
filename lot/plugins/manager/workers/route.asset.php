<?php


/**
 * Asset Manager
 * -------------
 */

Route::accept(array($config->manager->slug . '/asset', $config->manager->slug . '/asset/(:num)'), function($offset = 1) use($config, $speak) {
    $p = Request::get('path', false);
    $d = ASSET . File::path($p ? DS . $p : "");
    $d = str_replace(DS . DS, DS, $d);
    if( ! file_exists($d)) {
        Shield::abort(); // Folder not found!
    }
    // Disallow path traversal
    if(strpos($d . DS, ASSET . DS . '..' . DS) === 0) {
        Shield::abort();
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
                $folder = Text::parse(File::path($request['folder']), '->safe_path_name');
                // Disallow path traversal
                if(strpos(DS . $folder . DS, DS . '..' . DS) !== false) {
                    $folder = trim(str_replace(DS . '..' . DS, DS, DS . $folder . DS), DS);
                    Notify::error(Config::speak('notify_invalid_format', array($speak->folder, '<code>' . $folder . '</code>')));
                }
                if(File::exist($d . DS . $folder)) {
                    Notify::error(Config::speak('notify_folder_exist', '<code>' . $folder . '</code>'));
                }
            } else {
                Notify::error(Config::speak('notify_error_empty_field', $speak->folder));
            }
            if( ! Notify::errors()) {
                File::pocket($d . DS . $folder);
                Notify::success(Config::speak('notify_folder_created', '<code>' . $folder . '</code>'));
                Session::set('recent_item_update', explode(DS, $folder));
                $P = array('data' => $request);
                Weapon::fire(array('on_asset_update', 'on_asset_construct'), array($P, $P));
                if(isset($request['redirect'])) {
                    $folder = File::url($folder);
                    Guardian::kick($config->manager->slug . '/asset/' . $offset . str_replace('&', '&amp;', HTTP::query('path', $p ? $p . '/' . $folder : $folder)));
                }
                Guardian::kick($config->manager->slug . '/asset/' . $offset);
            } else {
                $tab_id = 'tab-content-2';
                include __DIR__ . DS . 'task.js.tab.php';
            }
        // New file
        } else {
            if(isset($_FILES) && ! empty($_FILES)) {
                File::upload($_FILES['file'], $d, function($name, $type, $size, $link) {
                    Session::set('recent_item_update', (array) $name);
                });
                $P = array('data' => $_FILES);
                Weapon::fire(array('on_asset_update', 'on_asset_construct'), array($P, $P));
            }
            if( ! Notify::errors()) {
                Guardian::kick($config->manager->slug . '/asset/' . $offset . str_replace('&', '&amp;', HTTP::query('path', $p)));
            } else {
                $tab_id = 'tab-content-3';
                include __DIR__ . DS . 'task.js.tab.php';
            }
        }
    }
    $filter = Request::get('q', "");
    $filter = $filter ? Text::parse($filter, '->safe_file_name') : "";
    $files = Get::closestFiles($d, "", 'ASC', 'path', $filter);
    $files_chunk = Mecha::eat($files)->chunk($offset, $config->per_page * 2)->vomit();
    Config::set(array(
        'page_title' => $speak->assets . $config->title_separator . $config->manager->title,
        'offset' => $offset,
        'pagination' => Navigator::extract($files, $offset, $config->per_page * 2, $config->manager->slug . '/asset'),
        'cargo' => 'cargo.asset.php'
    ));
    Shield::lot(array(
        'segment' => 'asset',
        'files' => $files_chunk ? Mecha::O($files_chunk) : false
    ))->attach('manager');
});


/**
 * Asset Repairer
 * --------------
 */

Route::accept($config->manager->slug . '/asset/repair/(file|files):(:all)', function($slug = "", $file = "") use($config, $speak) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    $old = File::path($file);
    $p = Request::get('path', false);
    if( ! $file = File::exist(ASSET . DS . $old)) {
        Shield::abort(); // File not found!
    }
    Config::set(array(
        'page_title' => $speak->editing . ': ' . File::B($old) . $config->title_separator . $config->manager->title,
        'cargo' => 'repair.asset.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // Empty field
        if( ! Request::post('name')) {
            Notify::error(Config::speak('notify_error_empty_field', $speak->name));
        } else {
            // Safe file name
            $new = Text::parse(File::path($request['name']), '->safe_path_name');
            $is_file = is_file(ASSET . DS . $new);
            // Missing file extension
            if($is_file && ! preg_match('#\.\w+$#', $new)) {
                Notify::error($speak->notify_error_file_extension_missing);
            }
            // File name already exist
            if($old !== $new && File::exist(ASSET . DS . $new)) {
                Notify::error(Config::speak('notify_' . ($is_file ? 'file' : 'folder') . '_exist', '<code>' . File::B($new) . '</code>'));
            }
            $P = array('data' => $request);
            if( ! Notify::errors()) {
                if(isset($request['content'])) {
                    File::open($file)->write($request['content'])->save();
                }
                File::open($file)->moveTo(ASSET . DS . $new);
                Notify::success(Config::speak('notify_' . (is_dir(ASSET . DS . $old) ? 'folder' : 'file') . '_updated', '<code>' . File::B($old) . '</code>'));
                Session::set('recent_item_update', explode(DS, $new));
                Weapon::fire(array('on_asset_update', 'on_asset_repair'), array($P, $P));
                Guardian::kick($config->manager->slug . '/asset/1' . str_replace('&', '&amp;', HTTP::query('path', $p)));
            }
        }
    }
    Shield::lot(array(
        'segment' => 'asset',
        'path' => $old,
        'content' => is_file(ASSET . DS . $old) && strpos(',' . SCRIPT_EXT . ',', ',' . File::E($old) . ',') !== false ? File::open(ASSET . DS . $old)->read() : false
    ))->attach('manager');
});


/**
 * Asset Killer
 * ------------
 */

Route::accept($config->manager->slug . '/asset/kill/(file|files):(:all)', function($slug = "", $file = "") use($config, $speak) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    $name = File::path($file);
    $p = Request::get('path', false);
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
        'cargo' => 'kill.asset.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $info_path = Mecha::walk($deletes, function($v) {
            $_path = ASSET . DS . $v;
            File::open($_path)->delete();
            return $_path;
        });
        $is_folder_or_file = count($deletes) === 1 && is_dir(ASSET . DS . $deletes[0]) ? 'folder' : 'file';
        $P = array('data' => array('files' => $info_path));
        Notify::success(Config::speak('notify_' . $is_folder_or_file . '_deleted', '<code>' . implode('</code>, <code>', $deletes) . '</code>'));
        Weapon::fire(array('on_asset_update', 'on_asset_destruct'), array($P, $P));
        Guardian::kick($config->manager->slug . '/asset/1' . str_replace('&', '&amp;', HTTP::query('path', $p)));
    } else {
        Notify::warning(count($deletes) === 1 ? Config::speak('notify_confirm_delete_', '<code>' . File::path($name) . '</code>') : $speak->notify_confirm_delete);
    }
    Shield::lot(array(
        'segment' => 'asset',
        'files' => Mecha::O($deletes)
    ))->attach('manager');
});


/**
 * Multiple Asset Action
 * ---------------------
 */

Route::accept($config->manager->slug . '/asset/do', function($path = "") use($config, $speak) {
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        if( ! isset($request['selected'])) {
            Notify::error($speak->notify_error_no_files_selected);
            Guardian::kick($config->manager->slug . '/asset/1');
        }
        $files = Mecha::walk($request['selected'], function($v) {
            return str_replace('%2F', '/', Text::parse($v, '->encoded_url'));
        });
        Guardian::kick($config->manager->slug . '/asset/' . $request['action'] . '/files:' . implode(';', $files));
    }
});