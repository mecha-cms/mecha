<?php


/**
 * Empty Plugin Page
 * -----------------
 */

$e_plugin_page = "Title: %s\n" .
     "Author: " . $speak->anon . "\n" .
     "URL: #\n" .
     "Version: 0.0.0\n" .
     "\n" . SEPARATOR . "\n" .
     "\n" . Config::speak('notify_not_available', array($speak->description));


/**
 * Plugins Manager
 * ---------------
 */

Route::accept(array($config->manager->slug . '/plugin', $config->manager->slug . '/plugin/(:num)'), function($offset = 1) use($config, $speak, $e_plugin_page) {
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    $offset = (int) $offset;
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
            if(File::exist(PLUGIN . DS . $path)) {
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
            File::upload($_FILES['file'], PLUGIN, Config::speak('notify_success_uploaded', array($speak->plugin)));
            $P = array('data' => $_FILES);
            Weapon::fire('on_plugin_update', array($P, $P));
            Weapon::fire('on_plugin_construct', array($P, $P));
            Weapon::fire('on_plugin_' . md5($path) . '_update', array($P, $P));
            Weapon::fire('on_plugin_' . md5($path) . '_construct', array($P, $P));
            if($uploaded = File::exist(PLUGIN . DS . $name)) {
                Package::take($uploaded)->extract(); // Extract the ZIP file
                File::open($uploaded)->delete(); // Delete the ZIP file
                if(File::exist(PLUGIN . DS . $path . DS . 'launch.php')) {
                    Weapon::fire('on_plugin_mounted', array($P, $P));
                    Weapon::fire('on_plugin_' . md5($path) . '_mounted', array($P, $P));
                    Guardian::kick($config->manager->slug . '/plugin/' . $path); // Redirect to the plugin manager page
                } else {
                    Guardian::kick($config->manager->slug . '/plugin?q_id=' . $path . '#plugin:' . $path);
                }
            }
        } else {
            Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
                echo '<script>
(function($) {
    $(\'.tab-area .tab[href$="#tab-content-2"]\').trigger("click");
})(Zepto);
</script>';
            }, 11);
        }
    }
    $plugins = array();
    $folders = glob(PLUGIN . DS . Request::get('q_id', '*'), GLOB_ONLYDIR);
    sort($folders);
    if($files = Mecha::eat($folders)->chunk($offset, $config->manager->per_page)->vomit()) {
        for($i = 0, $count = count($files); $i < $count; ++$i) {
            // Check whether the localized "about" file is available
            if( ! $file = File::exist($files[$i] . DS . 'about.' . $config->language . '.txt')) {
                $file = $files[$i] . DS . 'about.txt';
            }
            $about = Text::toPage(File::open($file)->read(sprintf($e_plugin_page, ucwords(Text::parse(basename($files[$i]), '->text')))), true, 'plugin:');
            $plugins[$i]['about'] = $about;
            $plugins[$i]['slug'] = basename($files[$i]);
        }
    } else {
        $files = false;
    }
    Config::set(array(
        'page_title' => $speak->plugins . $config->title_separator . $config->manager->title,
        'offset' => $offset,
        'files' => ! empty($plugins) ? $plugins : false,
        'pagination' => Navigator::extract($folders, $offset, $config->manager->per_page, $config->manager->slug . '/plugin'),
        'cargo' => DECK . DS . 'workers' . DS . 'plugin.php'
    ));
    Shield::attach('manager', false);
});


/**
 * Plugin Configurator
 * -------------------
 */

Route::accept($config->manager->slug . '/plugin/(:any)', function($slug = "") use($config, $speak, $e_plugin_page) {
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    if( ! File::exist(PLUGIN . DS . $slug . DS . 'launch.php')) {
        Shield::abort();
    }
    // Check whether the localized "about" file is available
    if( ! $file = File::exist(PLUGIN . DS . $slug . DS . 'about.' . $config->language . '.txt')) {
        $file = PLUGIN . DS . $slug . DS . 'about.txt';
    }
    $about = Text::toPage(File::open($file)->read(sprintf($e_plugin_page, ucwords(Text::parse($slug, '->text')))), true, 'plugin:');
    if( ! isset($about['url']) && preg_match('#(.*?) *\<(https?\:\/\/)(.*?)\>#i', $about['author'], $matches)) {
        $about['author'] = $matches[1];
        $about['url'] = $matches[2] . $matches[3];
    }
    $about['configurator'] = File::exist(PLUGIN . DS . $slug . DS . 'configurator.php');
    Config::set(array(
        'page_title' => $speak->managing . ': ' . $about['title'] . $config->title_separator . $config->manager->title,
        'file' => $about,
        'cargo' => DECK . DS . 'workers' . DS . 'repair.plugin.php'
    ));
    Shield::define('the_plugin_path', $slug)->attach('manager', false);
}, 10.2); // => `manager/plugin` is on priority 10, `manager/plugin/(:num)` is on priority 10.1


/**
 * Plugin Freezer/Igniter
 * ----------------------
 */

Route::accept($config->manager->slug . '/plugin/(freeze|fire)/id:(:any)', function($path = "", $slug = "") use($config, $speak) {
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    $page_current = (int) Request::get('o', 1);
    // Toggle file name from `launch.php` to `pending.php` or vice-versa.
    File::open(PLUGIN . DS . $slug . DS . ($path == 'freeze' ? 'launch' : 'pending') . '.php')
        ->renameTo(($path == 'freeze' ? 'pending' : 'launch') . '.php');
    $G = array('data' => array('id' => $slug, 'action' => $path));
    $mode = $path == 'freeze' ? 'eject' : 'mounted';
    Notify::success(Config::speak('notify_success_updated', array($speak->plugin)));
    Weapon::fire('on_plugin_update', array($G, $G));
    Weapon::fire('on_plugin_' . $mode, array($G, $G));
    Weapon::fire('on_plugin_' . md5($slug) . '_update', array($G, $G));
    Weapon::fire('on_plugin_' . md5($slug) . '_' . $mode, array($G, $G));
    Guardian::kick($config->manager->slug . '/plugin/' . $page_current);
});


/**
 * Plugin Killer
 * -------------
 */

Route::accept($config->manager->slug . '/plugin/kill/id:(:any)', function($slug = "") use($config, $speak, $e_plugin_page) {
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    // Check whether the localized "about" file is available
    if( ! $file = File::exist(PLUGIN . DS . $slug . DS . 'about.' . $config->language . '.txt')) {
        $file = PLUGIN . DS . $slug . DS . 'about.txt';
    }
    $about = Text::toPage(File::open($file)->read(sprintf($e_plugin_page, ucwords(Text::parse($slug, '->text')))), true, 'plugin:');
    $about['slug'] = $slug;
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . $about['title'] . $config->title_separator . $config->manager->title,
        'file' => $about,
        'cargo' => DECK . DS . 'workers' . DS . 'kill.plugin.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        File::open(PLUGIN . DS . $slug)->delete();
        $P = array('data' => array('id' => $slug));
        Notify::success(Config::speak('notify_success_deleted', array($speak->plugin)));
        Weapon::fire('on_plugin_update', array($P, $P));
        Weapon::fire('on_plugin_destruct', array($P, $P));
        Weapon::fire('on_plugin_' . md5($slug) . '_update', array($P, $P));
        Weapon::fire('on_plugin_' . md5($slug) . '_destruct', array($P, $P));
        Guardian::kick($config->manager->slug . '/plugin');
    } else {
        Notify::warning(Config::speak('notify_confirm_delete_', array('<strong>' . $about['title'] . '</strong>')));
    }
    Shield::attach('manager', false);
});


/**
 * Plugin Backup
 * -------------
 */

Route::accept($config->manager->slug . '/plugin/backup/id:(:any)', function($folder = "") use($config, $speak) {
    $name = $folder . '.zip';
    Package::take(PLUGIN . DS . $folder)->pack(ROOT . DS . $name, true);
    $G = array('data' => array('path' => ROOT . DS . $name, 'file' => ROOT . DS . $name));
    Weapon::fire('on_backup_construct', array($G, $G));
    Guardian::kick($config->manager->slug . '/backup/send:' . $name);
});