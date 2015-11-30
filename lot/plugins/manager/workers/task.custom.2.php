<?php

if(( ! empty($css) && $css !== $config->defaults->{$segment . '_css'}) || ( ! empty($js) && $js !== $config->defaults->{$segment . '_js'})) {
    Page::content($css)->content($js)->saveTo(CUSTOM . DS . Date::slug($date) . $extension);
    Weapon::fire(array('on_custom_update', 'on_custom_construct'), array($G, $P));
}