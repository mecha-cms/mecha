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

foreach(glob(PLUGIN . DS . '*' . DS . 'launch.php', GLOB_NOSORT) as $active) {
    if($e = File::exist(dirname($active) . DS . 'workers' . DS . 'fields.php')) {
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
        // "" means `article` or `page`
        if( ! isset($value['scope']) || $value['scope'] === "") {
            $value['scope'] = $segment != 'comment' ? $segment : "";
        }
        if(Notify::errors()) {
            $field[$key] = isset($field[$key]['value']) ? $field[$key]['value'] : "";
        }
        $type = $value['type'][0];
        if($value['scope'] == $segment) {
            $html .= Form::hidden('fields[' . $key . '][type]', $type);
            if($type == 't') {
                $html .= '<label class="grid-group">';
                $html .= '<span class="grid span-2 form-label">' . $value['title'] . '</span>';
                $html .= '<span class="grid span-4">';
                $html .= Form::text('fields[' . $key . '][value]', isset($field[$key]) ? $field[$key] : $value['value'], $value['value'], array(
                    'class' => 'input-block'
                ));
                $html .= '</span>';
                $html .= '</label>';
            } else if($type == 'b') {
                $html .= '<div class="grid-group">';
                $html .= '<span class="grid span-2"></span>';
                $html .= '<span class="grid span-4">';
                $html .= Form::checkbox('fields[' . $key . '][value]', ! empty($value['value']) ? $value['value'] : '1', isset($field[$key]) && ! empty($field[$key]), $value['title']);
                $html .= '</span>';
                $html .= '</div>';
            } else if($type == 'o') {
                $html .= '<label class="grid-group">';
                $html .= '<span class="grid span-2 form-label">' . $value['title'] . '</span>';
                $html .= '<span class="grid span-4">';
                $options = array();
                $selected = isset($field[$key]) ? isset($field[$key]) : "";
                foreach(explode("\n", $value['value']) as $v) {
                    $v = trim($v);
                    if(strpos($v, ':') !== false) {
                        $v = explode(':', $v, 2);
                        $options[trim($v[0])] = trim($v[1]);
                    } else {
                        $options[$v] = $v;
                    }
                }
                $html .= Form::select('fields[' . $key . '][value]', $options, $selected, array(
                    'class' => 'select-block'
                ));
                $html .= '</span>';
                $html .= '</label>';
            } else { // if($value['type'][0] == 's') {
                $html .= '<label class="grid-group">';
                $html .= '<span class="grid span-2 form-label">' . $value['title'] . '</span>';
                $html .= '<span class="grid span-4">';
                $html .= Form::textarea('fields[' . $key . '][value]', isset($field[$key]) ? $field[$key] : $value['value'], $value['value'], array(
                    'class' => 'input-block'
                ));
                $html .= '</span>';
                $html .= '</label>';
            }
        }
    }
    echo ! empty($html) ? $html : Cell::p(Config::speak('notify_empty', strtolower($speak->fields)));
} else {
    echo Cell::p(Config::speak('notify_empty', strtolower($speak->fields)));
}

Weapon::fire('unit_composer_3_after', array($segment, $fields));