<?php

$custom_ = CUSTOM . DS . Date::slug($post->date->W3C);
if(file_exists($custom_ . $extension_o)) {
    Weapon::fire('on_custom_update', array($G, $P));
    if(trim(File::open($custom_ . $extension_o)->read()) === "" || trim(File::open($custom_ . $extension_o)->read()) === SEPARATOR || (empty($css) && empty($js)) || ($css === $post_css && $js === $post_js)) {
        // Always delete empty custom CSS and JavaScript file(s) ...
        File::open($custom_ . $extension_o)->delete();
        Weapon::fire('on_custom_destruct', array($G, $P));
    } else {
        Page::content($css)->content($js)->saveTo($custom_ . $extension_o);
        File::open($custom_ . $extension_o)->renameTo(Date::slug($date) . $extension);
        Weapon::fire('on_custom_repair', array($G, $P));
    }
} else {
    if(( ! empty($css) && $css !== $post_css) || ( ! empty($js) && $js !== $post_js)) {
        Page::content($css)->content($js)->saveTo(CUSTOM . DS . Date::slug($date) . $extension_o);
        Weapon::fire(array('on_custom_update', 'on_custom_construct'), array($G, $P));
    }
}