<?php

Mecha::extend($attributes, $data['attributes']);

$html .= Form::hidden('fields[' . $key . ']', $value ? Converter::str($value) : null, $attributes);