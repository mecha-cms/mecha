<?php

$fields = Get::state_field(null, array(), true, $segment);

if( ! empty($fields)) {
    $html = "";
    $field = Guardian::wayback('fields', Mecha::A($page->fields_raw));
    $_ = 'unit:' . time() . '--';
    foreach($fields as $key => $value) {
        if(isset($field[$key]['type'])) { // `POST` request
            $field[$key] = isset($field[$key]['value']) ? $field[$key]['value'] : "";
        }
        $type = $value['type'];
        if( ! isset($value['scope']) || strpos(',' . $value['scope'] . ',', ',' . $segment . ',') !== false) {
            $description = isset($value['description']) && trim($value['description']) !== "" ? ' ' . Jot::info($value['description']) : "";
            $title = $value['title'] . $description;
            $html .= Form::hidden('fields[' . $key . '][type]', $type);
            if($type === 'hidden' || $type === 'h') {
                $html .= Form::hidden('fields[' . $key . '][value]', isset($field[$key]) ? $field[$key] : $value['value']);
            } else if($type === 'text' || $type === 't') {
                $html .= '<label class="grid-group grid-group-text">';
                $html .= '<span class="grid span-2 form-label">' . $title . '</span>';
                $html .= '<span class="grid span-4">';
                $html .= Form::text('fields[' . $key . '][value]', Converter::toText(isset($field[$key]) ? $field[$key] : $value['value']), Converter::toText(isset($value['placeholder']) ? $value['placeholder'] : $value['value']), array(
                    'class' => 'input-block'
                ));
                $html .= '</span>';
                $html .= '</label>';
            } else if($type === 'boolean' || $type === 'b') {
                $html .= '<div class="grid-group grid-group-boolean">';
                $html .= '<span class="grid span-2"></span>';
                $html .= '<span class="grid span-4">';
                $html .= Form::checkbox('fields[' . $key . '][value]', ! empty($value['value']) ? $value['value'] : 1, isset($field[$key]) && ! empty($field[$key]), $value['title']) . $description;
                $html .= '</span>';
                $html .= '</div>';
            } else if($type === 'option' || $type === 'o') {
                $html .= '<label class="grid-group grid-group-option">';
                $html .= '<span class="grid span-2 form-label">' . $title . '</span>';
                $html .= '<span class="grid span-4">';
                $select = isset($field[$key]) ? $field[$key] : "";
                $options = Converter::toArray($value['value'], S, '  ');
                if(isset($value['placeholder']) && trim($value['placeholder']) !== "") {
                    $options = array("" => $value['placeholder']) + $options;
                }
                $html .= Form::select('fields[' . $key . '][value]', $options, $select, array(
                    'class' => 'select-block'
                ));
                $html .= '</span>';
                $html .= '</label>';
            } else if($type === 'file' || $type === 'f') {
                $v = isset($value['value']) && $value['value'] !== "" ? $value['value'] : false;
                $vv = isset($field[$key]) && $field[$key] !== "" ? File::E($field[$key]) . DS . File::path($field[$key]) : false;
                $has_file = $vv !== false && file_exists(SUBSTANCE . DS . $vv) && is_file(SUBSTANCE . DS . $vv);
                $html .= '<div class="grid-group grid-group-file' . ($has_file ? ' grid-group-boolean' : "") . '">';
                $html .= ! $has_file ? '<span class="grid span-2 form-label">' . $title . '</span>' : '<span class="grid span-2"></span>';
                $html .= '<span class="grid span-4">';
                if( ! $has_file) {
                    $html .= Form::file($key);
                    $e = strtolower(str_replace(' ', "", (string) $v));
                    $html .= $v !== false ? Form::hidden('fields[' . $key . '][accept]', $e) . '<br><small><strong>' . $speak->accepted . ':</strong> <code>*.' . str_replace(',', '</code>, <code>*.', $e) . '</code></small>' : "";
                } else {
                    $html .= Form::hidden('fields[' . $key . '][value]', $vv);
                    $html .= '<span title="' . strip_tags($value['title']) . '">' . Form::checkbox('fields[' . $key . '][remove]', $vv, false, $speak->delete . ' <code>' . $vv . '</code>') . '</span>';
                }
                $html .= '</span>';
                $html .= '</div>';
            } else if($type === 'composer' || $type === 'c') {
                $html .= '<div class="grid-group grid-group-composer">';
                $html .= '<label class="grid span-2 form-label" for="' . $_ . $key . '">' . $title . '</label>';
                $html .= '<span class="grid span-4">';
                $html .= Form::textarea('fields[' . $key . '][value]', Converter::str(isset($field[$key]) ? $field[$key] : $value['value']), Converter::toText(isset($value['placeholder']) ? $value['placeholder'] : $value['value']), array(
                    'class' => array(
                        'textarea-block',
                        'MTE',
                        'code'
                    ),
                    'id' => $_ . $key
                ));
                $html .= '</span>';
                $html .= '</div>';
            } else if($type === 'editor' || $type === 'e') {
                $html .= '<div class="grid-group grid-group-editor">';
                $html .= '<label class="grid span-2 form-label" for="' . $_ . $key . '">' . $title . '</label>';
                $html .= '<span class="grid span-4">';
                $html .= Form::textarea('fields[' . $key . '][value]', Converter::str(isset($field[$key]) ? $field[$key] : $value['value']), Converter::toText(isset($value['placeholder']) ? $value['placeholder'] : $value['value']), array(
                    'class' => array(
                        'textarea-block',
                        'code'
                    ),
                    'id' =>  $_ . $key
                ));
                $html .= '</span>';
                $html .= '</div>';
            } else { // if($type === 'summary' || $type === 's') {
                $html .= '<label class="grid-group grid-group-summary">';
                $html .= '<span class="grid span-2 form-label">' . $title . '</span>';
                $html .= '<span class="grid span-4">';
                $html .= Form::textarea('fields[' . $key . '][value]', Converter::str(isset($field[$key]) ? $field[$key] : $value['value']), Converter::toText(isset($value['placeholder']) ? $value['placeholder'] : $value['value']), array(
                    'class' => 'textarea-block'
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