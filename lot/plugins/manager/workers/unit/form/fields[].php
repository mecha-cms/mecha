<?php

$field = Guardian::wayback('fields', Mecha::A($page->fields_raw));
$field_d = Get::state_field(null, array(), true, $segment);
$x = Cell::p(Config::speak('notify_empty', strtolower($speak->fields)));

if( ! empty($field_d)) {
    $html = "";
    foreach($field_d as $key => $data) {
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
        $type = isset($data['attributes']['type']) ? $data['attributes']['type'] : $data['type'];
        $value = isset($field[$key]) && $field[$key] !== "" ? $field[$key] : $data['value'];
        $placeholder = Converter::str(isset($data['placeholder']) && trim($data['placeholder']) !== "" ? $data['placeholder'] : str_replace("\n", ' ', $value));
        $description = isset($data['description']) && trim($data['description']) !== "" ? '&nbsp;' . Jot::info($data['description']) : "";
        $title = isset($data['title']) && trim($data['title']) !== "" ? $data['title'] : '<code>' . $key . '</code>';
        $attributes = array('id' => 'unit:' . time());
        // Default is `summary`
        $s = __DIR__ . DS . 'fields[]' . DS;
        include File::exist($s . $data['type'] . '.php', File::exist($s . '__' . $data['type'] . '.php', $s . 'summary.php'));
    }
    echo $html ? $html : $x;
} else {
    echo $x;
}