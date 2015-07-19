<?php

$custom_ = CUSTOM . DS . Date::format($task_connect_page->date->W3C, 'Y-m-d-H-i-s');
if(file_exists($custom_ . $extension_o)) {
    Weapon::fire('on_custom_update', array($G, $P));
    if(trim(File::open($custom_ . $extension_o)->read()) === "" || trim(File::open($custom_ . $extension_o)->read()) === SEPARATOR || (empty($css) && empty($js)) || ($css === $task_connect_page_css && $js === $task_connect_page_js)) {
        // Always delete empty custom CSS and JavaScript file(s) ...
        File::open($custom_ . $extension_o)->delete();
        Weapon::fire('on_custom_destruct', array($G, $P));
    } else {
        Page::content($css)->content($js)->saveTo($custom_ . $extension_o);
        File::open($custom_ . $extension_o)->renameTo(Date::format($date, 'Y-m-d-H-i-s') . $extension);
        Weapon::fire('on_custom_repair', array($G, $P));
    }
} else {
    if(( ! empty($css) && $css !== $task_connect_page_css) || ( ! empty($js) && $js !== $task_connect_page_js)) {
        Page::content($css)->content($js)->saveTo(CUSTOM . DS . Date::format($date, 'Y-m-d-H-i-s') . $extension_o);
        Weapon::fire('on_custom_update', array($G, $P));
        Weapon::fire('on_custom_construct', array($G, $P));
    }
}