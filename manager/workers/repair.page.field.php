<?php

$fields = File::exist(STATE . DS . 'fields.txt') ? unserialize(File::open(STATE . DS . 'fields.txt')->read()) : array();

if($e = File::exist(SHIELD . DS . $config->shield . DS . 'workers' . DS . 'fields.php')) {
    $extra_fields = include $e;
    $fields = $fields + $extra_fields;
}

if( ! empty($fields)) {
    $html = "";
    foreach($fields as $key => $value) {
        $field = $cache['fields'];
        if( ! isset($value['value'])) {
            $value['value'] = "";
        }
        if( ! isset($value['scope']) || isset($value['scope']) && $value['scope'] == 'all') {
            $value['scope'] = $config->editor_type;
        }
        if(Notify::errors()) {
            $field[$key] = isset($field[$key]['value']) ? $field[$key]['value'] : "";
        }
        if($value['type'] == 'text' && $value['scope'] == $config->editor_type) {
            $html .= '<label class="grid-group">';
            $html .= '<span class="grid span-1 form-label">' . $value['title'] . '</span>';
            $html .= '<span class="grid span-5">';
            $html .= '<input name="fields[' . $key . '][value]" type="text" class="input-block" value="' . (isset($field[$key]) ? Text::parse($field[$key])->to_encoded_html : $value['value']) . '">';
            $html .= '</span>';
            $html .= '</label>';
        }
        if($value['type'] == 'summary' && $value['scope'] == $config->editor_type) {
            $html .= '<label class="grid-group">';
            $html .= '<span class="grid span-1 form-label">' . $value['title'] . '</span>';
            $html .= '<span class="grid span-5">';
            $html .= '<textarea name="fields[' . $key . '][value]" class="input-block">' . (isset($field[$key]) ? Text::parse($field[$key])->to_encoded_html : $value['value']) . '</textarea>';
            $html .= '</span>';
            $html .= '</label>';
        }
        if($value['type'] == 'boolean' && $value['scope'] == $config->editor_type) {
            $html .= '<div class="grid-group">';
            $html .= '<span class="grid span-1"></span>';
            $html .= '<span class="grid span-5">';
            $html .= '<label><input name="fields[' . $key . '][value]" type="checkbox"' . ( ! empty($value['value']) ? ' value="' . $value['value'] . '"' : "") . (isset($field[$key]) && ! empty($field[$key]) ? ' checked' : "") . '> <span>' . $value['title'] . '</span></label>';
            $html .= '</span>';
            $html .= '</div>';
        }
        if($value['type'] == 'option' && $value['scope'] == $config->editor_type) {
            $html .= '<label class="grid-group">';
            $html .= '<span class="grid span-1 form-label">' . $value['title'] . '</span>';
            $html .= '<span class="grid span-5">';
            $html .= '<select name="fields[' . $key . '][value]" class="input-block">';
            foreach(explode("\n", $value['value']) as $v) {
                $v = trim($v);
                if(strpos($v, ':') !== false) {
                    $v = explode(':', $v, 2);
                    $html .= '<option value="' . trim($v[1]) . '"' . (isset($field[$key]) && $field[$key] == trim($v[1]) ? ' selected': "") . '>' . trim($v[0]) . '</option>';
                } else {
                    $html .= '<option value="' . $v . '"' . (isset($field[$key]) && $field[$key] == trim($v) ? ' selected': "") . '>' . $v . '</option>';
                }
            }
            $html .= '</select>';
            $html .= '</span>';
            $html .= '</label>';
        }
        if($value['scope'] == $config->editor_type) {
            $html .= '<input name="fields[' . $key . '][type]" type="hidden" value="' . $value['type'] . '">';
        }
    }
    echo ! empty($html) ? $html : '<p>' . Config::speak('notify_empty', array(strtolower($speak->fields))) . '</p>';
} else {
    $html .= '<p>' . Config::speak('notify_empty', array(strtolower($speak->fields))) . '</p>';
}