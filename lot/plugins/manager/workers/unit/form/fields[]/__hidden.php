<?php

Mecha::extend($attributes, $data['attributes']);

$html .= Form::hidden('fields[' . $key . '][value]', $value, $attributes);