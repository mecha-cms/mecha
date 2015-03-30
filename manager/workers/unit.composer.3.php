<?php

$fields = Get::state_field(array());


/**
 * Allow shield to add custom fields dynamically by creating
 * a file called `fields.php` saved inside a folder named as `workers`.
 * This file contains array of fields data.
 *
 * -- EXAMPLE CONTENT OF `fields.php`: --------------------------------
 *
 *    return array(
 *        'break_title_text' => array(
 *            'title' => 'Break Title Text?',
 *            'type' => 'b',
 *            'value' => "",
 *            'scope' => 'article'
 *        )
 *    );
 *
 * --------------------------------------------------------------------
 *
 */

if($e = File::exist(SHIELD . DS . $config->shield . DS . 'workers' . DS . 'fields.php')) {
    $extra_fields = include $e;
    $fields = $fields + $extra_fields;
}


/**
 * Allow plugin to add custom fields dynamically by creating
 * a file called `fields.php` saved inside a folder named as `workers`.
 * This file contains array of fields data.
 */

foreach(glob(PLUGIN . DS . '*', GLOB_ONLYDIR) as $folder) {
    if(File::exist($folder . DS . 'launch.php') && $e = File::exist($folder . DS . 'workers' . DS . 'fields.php')) {
        $extra_fields = include $e;
        $fields = $fields + $extra_fields;
    }
}

Weapon::fire('unit_composer_3_before', array($segment, $fields));

if( ! isset($default->fields)) {
    $default->fields = array();
}

if( ! empty($fields)) {
    $html = "";
    $field = Guardian::wayback('fields', Mecha::A($default->fields));
    foreach($fields as $key => $value) {
        if( ! isset($value['value'])) {
            $value['value'] = "";
        }
        if( ! isset($value['scope']) || $value['scope'] !== 'article' && $value['scope'] !== 'page' && $segment !== 'comment') {
            $value['scope'] = $segment;
        }
        if(Notify::errors()) {
            $field[$key] = isset($field[$key]['value']) ? $field[$key]['value'] : "";
        }
        $type = $value['type'][0];
        if($value['scope'] == $segment) {
            $html .= '<input name="fields[' . $key . '][type]" type="hidden" value="' . $type . '">';
            if($type == 't') {
                $html .= '<label class="grid-group">';
                $html .= '<span class="grid span-2 form-label">' . $value['title'] . '</span>';
                $html .= '<span class="grid span-4">';
                $html .= '<input name="fields[' . $key . '][value]" type="text" class="input-block" value="' . (isset($field[$key]) ? Text::parse($field[$key], '->encoded_html') : $value['value']) . '">';
                $html .= '</span>';
                $html .= '</label>';
            } elseif($type == 'b') {
                $html .= '<div class="grid-group">';
                $html .= '<span class="grid span-2"></span>';
                $html .= '<span class="grid span-4">';
                $html .= '<label><input name="fields[' . $key . '][value]" type="checkbox"' . ( ! empty($value['value']) ? ' value="' . $value['value'] . '"' : "") . (isset($field[$key]) && ! empty($field[$key]) ? ' checked' : "") . '> <span>' . $value['title'] . '</span></label>';
                $html .= '</span>';
                $html .= '</div>';
            } elseif($type == 'o') {
                $html .= '<label class="grid-group">';
                $html .= '<span class="grid span-2 form-label">' . $value['title'] . '</span>';
                $html .= '<span class="grid span-4">';
                $html .= '<select name="fields[' . $key . '][value]" class="select-block">';
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
            } else { // if($value['type'][0] == 's') {
                $html .= '<label class="grid-group">';
                $html .= '<span class="grid span-2 form-label">' . $value['title'] . '</span>';
                $html .= '<span class="grid span-4">';
                $html .= '<textarea name="fields[' . $key . '][value]" class="textarea-block">' . (isset($field[$key]) ? Text::parse($field[$key], '->encoded_html') : $value['value']) . '</textarea>';
                $html .= '</span>';
                $html .= '</label>';
            }
        }
    }
    echo ! empty($html) ? $html : '<p>' . Config::speak('notify_empty', array(strtolower($speak->fields))) . '</p>';
} else {
    echo '<p>' . Config::speak('notify_empty', array(strtolower($speak->fields))) . '</p>';
}

Weapon::fire('unit_composer_3_after', array($segment, $fields));