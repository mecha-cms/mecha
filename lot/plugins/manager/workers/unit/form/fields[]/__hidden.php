<?php

Mecha::extend($attributes, $data['attributes']);

$html .= Form::hidden('fields[' . $key . ']', $value ? Converter::toText($value) : null, $attributes);