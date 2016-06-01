<?php

Mecha::extend($attributes, $data['attributes']);

$e = isset($data['value']) && $data['value'] !== "" ? $data['value'] : false;
$path = $value ? File::E($value) . DS . File::path($value) : false;
$exist = $path !== false && is_file(SUBSTANCE . DS . $path);

$html .= '<div class="grid-group grid-group-' . $type . ($exist ? ' grid-group-boolean' : "") . '">';
$html .= ! $exist ? '<span class="grid span-2 form-label">' . $title . '</span>' : '<span class="grid span-2"></span>';
$html .= '<span class="grid span-4">';
if( ! $exist) {
    $html .= Form::file($key, $attributes);
    $ee = strtolower(str_replace(' ', "", (string) $e));
    $html .= $e !== false ? '<br><small><strong>' . $speak->accepted . ':</strong> <code>*.' . str_replace(',', '</code>, <code>*.', $ee) . '</code></small>' : "";
} else {
    $html .= Form::hidden('fields[' . $key . '][value]', $path);
    $html .= '<span title="' . Text::parse($data['title'], '->text') . '">' . Form::checkbox('fields[' . $key . '][x]', $path, false, $speak->delete . ' <code>' . $path . '</code>', $attributes) . '</span>';
}
$html .= '</span>';
$html .= '</div>';