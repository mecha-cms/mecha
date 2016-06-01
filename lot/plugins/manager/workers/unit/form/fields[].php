<?php

$fields = Get::state_field(null, array(), true, $segment);
$fields_e = Cell::p(Config::speak('notify_empty', strtolower($speak->fields)));

if( ! empty($fields)) {
    $html = "";
    $field = Guardian::wayback('fields', Mecha::A($page->fields_raw));
    foreach($fields as $key => $data) {
        // Define variable(s) for the included file(s)
        //
        // `$data`
        // `$key`
        // `$title`
        // `$type`
        // `$placeholder`
        // `$value`
        // `$description`
        // `$cope`
        // `$attributes`
        //
        $scope = isset($data['scope']) ? $data['scope'] : null;
        if( ! is_null($scope)) {
            if(strpos(',' . $scope . ',', ',' . $segment . ',') === false) {
                continue;
            }
        }
        $data['key'] = $key;
        if(isset($field[$key]['type'])) { // `POST` request
            $field[$key] = isset($field[$key]['value']) ? $field[$key]['value'] : "";
        }
        $type = $data['type'];
        $value = isset($field[$key]) && $field[$key] !== "" ? $field[$key] : $data['value'];
        $placeholder = Converter::str( ! empty($data['placeholder']) ? $data['placeholder'] : $value);
        $description = ! empty($data['description']) ? '&nbsp;' . Jot::info($data['description']) : "";
        $title = ( ! empty($data['title']) ? $data['title'] : '<em>' . $key . '</em>') . $description;
        $attributes = array('id' => 'unit:' . time());
        $html .= Form::hidden('fields[' . $key . '][type]', $type);
        // Default is `summary`
        $s = __DIR__ . DS . 'fields[]' . DS;
        include File::exist($s . $type . '.php', File::exist($s . '__' . $type . '.php', $s . 'summary.php'));
    }
    echo ! empty($html) ? $html : $fields_e;
} else {
    echo $fields_e;
}