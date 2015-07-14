<?php

$fields = Get::state_field($segment);

Weapon::fire('unit_composer_3_before', array($segment, $fields));

if( ! isset($default->fields)) {
    $default->fields = array();
}

if( ! empty($fields)) {
    $html = "";
    $field = Guardian::wayback('fields', Mecha::A($default->fields));
    foreach($fields as $key => $value) {
        // <= 1.1.3
        // "" is equal to `article,page`
        // '*' is equal to all scopes
        if($value['scope'] === "") $value['scope'] = 'article,page';
        if($value['scope'] === '*') $value['scope'] = $segment;
        if(isset($field[$key]['type'])) {
            $field[$key] = isset($field[$key]['value']) ? $field[$key]['value'] : "";
        }
        $type = $value['type'][0];
        if(strpos(',' . $value['scope'] . ',', ',' . $segment . ',') !== false) {
            $description = isset($value['description']) && trim($value['description']) !== "" ? ' <span class="text-info help" title="' . Text::parse($value['description'], '->encoded_html') . '">' . Jot::icon('question-circle') . '</span>' : "";
            $title = $value['title'] . $description;
            $html .= Form::hidden('fields[' . $key . '][type]', $type);
            if($type === 't') {
                $html .= '<label class="grid-group grid-group-text">';
                $html .= '<span class="grid span-2 form-label">' . $title . '</span>';
                $html .= '<span class="grid span-4">';
                $html .= Form::text('fields[' . $key . '][value]', Converter::toText(isset($field[$key]) ? $field[$key] : $value['value']), Converter::toText(isset($value['placeholder']) ? $value['placeholder'] : $value['value']), array(
                    'class' => 'input-block'
                ));
                $html .= '</span>';
                $html .= '</label>';
            } else if($type === 'b') {
                $html .= '<div class="grid-group grid-group-boolean">';
                $html .= '<span class="grid span-2"></span>';
                $html .= '<span class="grid span-4">';
                $html .= Form::checkbox('fields[' . $key . '][value]', ! empty($value['value']) ? $value['value'] : '1', isset($field[$key]) && ! empty($field[$key]), $value['title']) . $description;
                $html .= '</span>';
                $html .= '</div>';
            } else if($type === 'o') {
                $html .= '<label class="grid-group grid-group-option">';
                $html .= '<span class="grid span-2 form-label">' . $title . '</span>';
                $html .= '<span class="grid span-4">';
                $select = isset($field[$key]) ? $field[$key] : "";
                $options = is_array($value['value']) ? $value['value'] : Text::toArray($value['value'], S, '  ');
                $html .= Form::select('fields[' . $key . '][value]', $options, $select, array(
                    'class' => 'select-block'
                ));
                $html .= '</span>';
                $html .= '</label>';
            } else if($type === 'f') {
                $v = isset($value['value']) && $value['value'] !== "" ? $value['value'] : false;
                $vv = isset($field[$key]) && $field[$key] !== "" ? $field[$key] : false;
                $has_file = $vv !== false && file_exists(SUBSTANCE . DS . $vv) && is_file(SUBSTANCE . DS . $vv);
                $html .= '<div class="grid-group grid-group-file' . ($has_file ? ' grid-group-boolean' : "") . '">';
                $html .= ! $has_file ? '<span class="grid span-2 form-label">' . $title . '</span>' : '<span class="grid span-2"></span>';
                $html .= '<span class="grid span-4">';
                if( ! $has_file) {
                    $html .= Form::file($key);
                    $e = strtolower(str_replace(' ', "", $v));
                    $html .= $v !== false ? Form::hidden('fields[' . $key . '][accept]', $e) . '<br><small><strong>' . $speak->accepted . ':</strong> <code>*.' . str_replace(',', '</code>, <code>*.', $e) . '</code></small>' : "";
                } else {
                    $html .= Form::hidden('fields[' . $key . '][value]', $vv);
                    $html .= '<span title="' . strip_tags($value['title']) . '">' . Form::checkbox('fields[' . $key . '][remove]', $vv, false, $speak->delete . ' <code>' . $vv . '</code>') . '</span>';
                }
                $html .= '</span>';
                $html .= '</div>';
            } else { // if($type === 's') {
                $html .= '<label class="grid-group grid-group-summary">';
                $html .= '<span class="grid span-2 form-label">' . $title . '</span>';
                $html .= '<span class="grid span-4">';
                $html .= Form::textarea('fields[' . $key . '][value]', Converter::str(isset($field[$key]) ? $field[$key] : $value['value']), Converter::toText(isset($value['placeholder']) ? $value['placeholder'] : $value['value']), array(
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