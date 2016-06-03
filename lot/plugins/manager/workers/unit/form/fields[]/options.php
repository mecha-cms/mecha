<?php

Mecha::extend($attributes, $data['attributes']);

$html .= '<div class="grid-group grid-group-' . $type . '">';
$html .= '<span class="grid span-2 form-label">' . $title . $description . '</span>';
$html .= '<div class="grid span-4">';
$options = Converter::toArray($data['value'], S, '  ');
$html .= '<div>';
$i = 0;
$value_o = ! empty($field[$key]) ? $field[$key] : array();
foreach($options as $k => $v) {
    if(is_array($v)) {
        $html .= '<fieldset>';
		$html .= '<legend>' . $k . '</legend>';
        $html .= '<div class="p">';
        foreach($v as $kk => $vv) {
            $html .= '<div>' . Form::checkbox('fields[' . $key . '][' . $i . '][]', $kk, isset($value_o[$i]) && Mecha::walk($value_o[$i])->has($kk), $vv, $attributes) . '</div>';
        }
        $html .= '</div>';
		$html .= '</fieldset>';
    } else {
        $html .= '<div>' . Form::checkbox('fields[' . $key . '][]', $k, Mecha::walk($value_o)->has($k), $v, $attributes) . '</div>';
    }
    $i++;
}
$html .= '</div>';
$html .= '</div>';
$html .= '</div>';