<?php

Mecha::extend($attributes, $data['attributes']);

$html .= '<div class="grid-group grid-group-' . $type . '">';
$html .= '<span class="grid span-2"></span>';
$html .= '<div class="grid span-4">';
$html .= '<div class="option">' . Form::checkbox('fields[' . $key . '][value]', isset($data['value']) && $data['value'] !== "" ? Converter::str($data['value']) : 1, ! empty($value), $data['title'], $attributes) . $description . '</div>';
$html .= '</div>';
$html .= '</div>';