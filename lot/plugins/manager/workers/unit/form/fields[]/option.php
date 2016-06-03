<?php

Mecha::concat($attributes, array(
    'class' => array(
        'select-block'
    )
));

Mecha::extend($attributes, $data['attributes']);

$html .= '<label class="grid-group grid-group-' . $type . '">';
$html .= '<span class="grid span-2 form-label">' . $title . $description . '</span>';
$html .= '<span class="grid span-4">';
$select = isset($field[$key]) ? $field[$key] : "";
$options = Converter::toArray($data['value'], S, '  ');
if(isset($data['placeholder']) && $data['placeholder'] !== "") {
    $options = array("" => $data['placeholder']) + $options;
}
$html .= Form::select('fields[' . $key . ']', $options, $select, $attributes);
$html .= '</span>';
$html .= '</label>';