<?php

if($config->page_type === 'article' && Request::method('post')) {
    if( ! isset($_POST['fields'])) $_POST['fields'] = array();
    Mecha::extend($_POST['fields'], array(
        'user_ip' => array('type' => 'hidden', 'value' => Get::IP()),
        'user_agent' => array('type' => 'hidden', 'value' => Get::UA())
    ));
    // Block comment by IP address
    $fucking_words = explode(',', $config->keywords_spam);
    foreach($fucking_words as $spam) {
        if($fuck = trim($spam)) {
            if(Get::IP() === $fuck) {
                Notify::warning($speak->notify_warning_intruder_detected . ' <strong class="text-error pull-right">' . $fuck . '</strong>');
                break;
            }
        }
    }
}