<?php echo Menu::get(Converter::toArray(Get::state_menu(array(
    $speak->home => '/',
    $speak->feed => '/feed/rss'
)), S, '    '), 'ul', "", 'navigation:'); ?>