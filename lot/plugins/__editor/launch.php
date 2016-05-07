<?php

// Load the configuration data
$c_editor = $config->states->{'plugin_' . md5(File::B(__DIR__))};

// Fix request data
Route::over($config->manager->slug . '/plugin/' . File::B(__DIR__) . '/update', function() use($c_editor) {
    $s = $c_editor;
    foreach(Config::get('speak.MTE.buttons', array()) as $k => $v) {
        $s->buttons->{$k} = Request::post('buttons.' . $k, 0);
    }
    $s->autoComplete = Request::post('autoComplete', 0);
    $s->autoIndent = Request::post('autoIndent', 0);
    Mecha::extend($_POST, Mecha::A($s));
});

// Merge language data to the `DASHBOARD`
Weapon::add('shield_before', function() use($c_editor) {
    // Manage editor button(s)
    $editor = Config::speak('MTE');
    foreach($editor->buttons as $k => $v) {
        if(isset($c_editor->buttons->{$k}) && $c_editor->buttons->{$k} === 0) {
            $editor->buttons->{$k} = false;
        }
    }
    // Merge language data to the `DASHBOARD`
    Config::merge('DASHBOARD.languages.MTE', $editor);
}, 1);

// Inject editor's CSS
Weapon::add('shell_after', function() {
    echo Asset::stylesheet(__DIR__ . DS . 'assets' . DS . 'shell' . DS . 'editor.css', "", 'shell/editor.min.css');
}, 2);

// Set `data-MTE-config` attribute(s)
Filter::add('form:bond.textarea', function($attr) use($config, $segment, $c_editor) {
    if( ! isset($attr['class'])) return $attr;
    $s = clone $c_editor; // don't touch the previously defined `$c_editor`
    unset($s->buttons);
    if(Config::get('html_parser.active') === 'HTML') {
        unset($s->PRE);
    }
    if($segment === 'menu') {
        $s->tabSize = '    '; // force 4 space(s)
    }
    if(is_string($attr['class'])) {
        $attr['class'] = explode(' ', $attr['class']);
    }
    if(Mecha::walk($attr['class'])->has('code')) {
        unset($s->toolbar, $s->shortcut);
        $attr['data-MTE-config'] = json_encode($s);
    }
    if(Mecha::walk($attr['class'])->has('MTE')) {
        $s->toolbar = $s->shortcut = 1;
        $attr['data-MTE-config'] = json_encode($s);
    }
    return $attr;
});

// Inject editor's JavaScript
Weapon::add('SHIPMENT_REGION_BOTTOM', function() use($config, $speak) {
    $path = __DIR__ . DS . 'assets' . DS . 'sword' . DS;
    $editor = Config::get('MTE', 'HTE');
    echo Asset::javascript(array(
        $path . 'editor.min.js',
        $path . 'hte.min.js'
    ));
    echo '<script>var MTE=' . $editor . ';</script>';
    echo Asset::javascript($path . 'run.js', "", 'sword/run.min.js');
}, 2);