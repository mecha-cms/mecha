<?php

// Load the configuration data
$c = $config->states->{'plugin_' . md5(File::B(__DIR__))};

// Merge language data to the `DASHBOARD`
Weapon::add('shield_before', function() use($c) {
    // Manage editor button(s)
    $editor = Config::speak('MTE');
    foreach($editor->buttons as $k => $v) {
        if( ! Text::check($k)->in(array('yes', 'no', 'ok', 'cancel', 'open', 'close'))) {
            if( ! isset($c->buttons->{$k})) {
                $editor->buttons->{$k} = false;
            }
        }
    }
    // Merge language data to the `DASHBOARD`
    Config::merge('DASHBOARD.languages.MTE', $editor);
}, 1);

// Inject editor's CSS
Weapon::add('shell_after', function() {
    echo Asset::stylesheet(__DIR__ . DS . 'assets' . DS . 'shell' . DS . 'editor.css', "", 'shell/editor.min.css');
}, 2);

// Inject editor's JavaScript
Weapon::add('SHIPMENT_REGION_BOTTOM', function() use($config, $speak) {
    echo '<script>(function($){$(\'.MTE\').attr(\'data-MTE-config\',\'{"toolbar":true,"shortcut":true}\')})(DASHBOARD.$);</script>';
    $path = __DIR__ . DS . 'assets' . DS . 'sword' . DS;
    $editor = Config::get('MTE', 'HTE');
    echo Asset::javascript(array(
        $path . 'editor.min.js',
        $path . 'hte.min.js'
    ));
    echo '<script>var MTE=' . $editor . ';</script>';
    echo Asset::javascript($path . 'run.js', "", 'sword/run.min.js');
}, 2);