<?php

if(( ! empty($css) && $css !== $task_connect_page_css) || ( ! empty($js) && $js !== $task_connect_page_js)) {
    Page::content($css)->content($js)->saveTo(CUSTOM . DS . Date::slug($date) . $extension);
    Weapon::fire('on_custom_update', array($G, $P));
    Weapon::fire('on_custom_construct', array($G, $P));
}