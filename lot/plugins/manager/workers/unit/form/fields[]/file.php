<?php

Mecha::extend($attributes, $data['attributes']);

$e = isset($data['value']) && $data['value'] !== "" ? $data['value'] : false;
$path = $value ? File::path($value) : false;
$exist = $path !== false && is_file(SUBSTANCE . DS . File::E($path, '*') . DS . $path);

$html .= '<div class="grid-group grid-group-' . $type . ($exist ? ' grid-group-boolean' : "") . '">';
$html .= ! $exist ? '<span class="grid span-2 form-label">' . $title . $description . '</span>' : '<span class="grid span-2"></span>';
$html .= '<div class="grid span-4">';
if( ! $exist) {
    $html .= Form::file($key, $attributes);
    $ee = strtolower(str_replace(' ', "", (string) $e));
    $html .= $e !== false ? '<br><small><strong>' . $speak->accepted . ':</strong> <code>*.' . str_replace(',', '</code>, <code>*.', $ee) . '</code></small>' : "";
} else {
    $html .= Form::hidden('fields[' . $key . ']', $path);
    $html .= '<div title="' . Text::parse($title, '->text') . '">';
    $html .= Form::checkbox('fields[' . $key . ']', '*' . $path, false, $speak->delete . ' <code>' . $path . '</code>', $attributes) . $description;
    $html .= '</div>';
}
$html .= '</div>';
$html .= '</div>';