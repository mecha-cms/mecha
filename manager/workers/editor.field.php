<?php

$fields = File::exist(STATE . DS . 'fields.txt') ? unserialize(File::open(STATE . DS . 'fields.txt')->read()) : array();

if( ! empty($fields)) {
    foreach($fields as $key => $value) {
        $extra = $cache['fields'];
        if(isset($extra[$key]) && is_array($extra[$key])) {
            $extra[$key] = isset($extra[$key]['value']) ? $extra[$key]['value'] : "";
        }
        if($value['type'] == 'text') {
            echo '<label class="grid-group">';
            echo '<span class="grid span-1 form-label">' . $value['title'] . '</span>';
            echo '<span class="grid span-5">';
            echo '<input name="fields[' . $key . '][value]" type="text" class="input-block" value="' . (isset($extra[$key]) ? Text::parse($extra[$key])->to_encoded_html : "") . '">';
            echo '</span>';
            echo '</label>';
        }
        if($value['type'] == 'summary') {
            echo '<label class="grid-group">';
            echo '<span class="grid span-1 form-label">' . $value['title'] . '</span>';
            echo '<span class="grid span-5">';
            echo '<textarea name="fields[' . $key . '][value]" class="input-block">' . (isset($extra[$key]) ? Text::parse($extra[$key])->to_encoded_html : "") . '</textarea>';
            echo '</span>';
            echo '</label>';
        }
        if($value['type'] == 'boolean') {
            echo '<div class="grid-group">';
            echo '<span class="grid span-1"></span>';
            echo '<span class="grid span-5">';
            echo '<label><input name="fields[' . $key . '][value]" type="checkbox"' . ( ! empty($extra[$key]) ? ' checked' : "") . '> <span>' . $value['title'] . '</span></label>';
            echo '</span>';
            echo '</div>';
        }
        echo '<input name="fields[' . $key . '][type]" type="hidden" value="' . $value['type'] . '">';
    }
} else {
    echo '<p>' . Config::speak('notify_empty', array(strtolower($speak->fields))) . '</p>';
}