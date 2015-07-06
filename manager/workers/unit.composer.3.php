<?php

$fields = Get::state_field($segment);


/**
 * Allow shield to add custom field(s) dynamically by creating a file
 * called `fields.php` saved inside a folder named as `workers`.
 * This file contains array of field(s) data.
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
 * Allow plugin to add custom field(s) dynamically by creating a file
 * called `fields.php` saved inside a folder named as `workers`.
 * This file contains array of field(s) data.
 */

foreach(glob(PLUGIN . DS . '*' . DS . 'launch.php', GLOB_NOSORT) as $active) {
    if($e = File::exist(File::D($active) . DS . 'workers' . DS . 'fields.php')) {
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
        if( ! isset($value['scope']) || $value['scope'] === '*' || $value['scope'] === "") {
            $value['scope'] = $segment;
        }
        if(Notify::errors()) {
            $field[$key] = isset($field[$key]['value']) ? $field[$key]['value'] : "";
        }
        $type = $value['type'][0];
        if(strpos(',' . $value['scope'] . ',', ',' . $segment . ',') !== false) {
            $title = $value['title'] . (isset($value['description']) && trim($value['description']) !== "" ? ' <span class="text-info help" title="' . Text::parse($value['description'], '->encoded_html') . '">' . Jot::icon('question-circle') . '</span>' : "");
            $html .= Form::hidden('fields[' . $key . '][type]', $type);
            if($type === 't') {
                $html .= '<label class="grid-group grid-group-text">';
                $html .= '<span class="grid span-2 form-label">' . $title . '</span>';
                $html .= '<span class="grid span-4">';
                $html .= Form::text('fields[' . $key . '][value]', isset($field[$key]) ? $field[$key] : $value['value'], $value['value'], array(
                    'class' => 'input-block'
                ));
                $html .= '</span>';
                $html .= '</label>';
            } else if($type === 'b') {
                $html .= '<div class="grid-group grid-group-boolean">';
                $html .= '<span class="grid span-2"></span>';
                $html .= '<span class="grid span-4">';
                $html .= Form::checkbox('fields[' . $key . '][value]', ! empty($value['value']) ? $value['value'] : '1', isset($field[$key]) && ! empty($field[$key]), $title);
                $html .= '</span>';
                $html .= '</div>';
            } else if($type === 'o') {
                $html .= '<label class="grid-group grid-group-option">';
                $html .= '<span class="grid span-2 form-label">' . $title . '</span>';
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
            } else { // if($value['type'][0] === 's') {
                $html .= '<label class="grid-group grid-group-summary">';
                $html .= '<span class="grid span-2 form-label">' . $title . '</span>';
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