<?php

Mecha::extend($attributes, $data['attributes']);

$html .= '<div class="grid-group grid-group-' . $type . '">';
$html .= '<span class="grid span-2 form-label">' . $title . '</span>';
$html .= '<div class="grid span-4">';
$options = Converter::toArray($data['value'], S, '  ');
$html .= '<div class="options">';
$i = 0;
foreach($options as $k => $v) {
    if(is_array($v)) {
        $html .= '<div class="option"><em>' . $k . '</em></div>';
        $html .= '<div class="options">';
        foreach($v as $kk => $vv) {
            $html .= '<div class="option">' . Form::checkbox('fields[' . $key . '][value][' . $i . '][]', $kk, isset($value[$i]) && Mecha::walk($value[$i])->has($kk), $vv, $attributes) . '</div>';
        }
        $html .= '</div>';
    } else {
        $html .= '<div class="option">' . Form::checkbox('fields[' . $key . '][value][]', $k, Mecha::walk($value)->has($k), $v, $attributes) . '</div>';
    }
    $i++;
}
$html .= '</div>';
$html .= '</div>';
$html .= '</div>';