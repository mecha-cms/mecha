<?php

$c_editor->enableSETextHeader = Request::post('MTE.enableSETextHeader', 0);
$c_editor->closeATXHeader = Request::post('MTE.closeATXHeader', 0);
if($fence = Request::post('MTE.PRE')) {
    $c_editor->PRE = Converter::DW($fence);
} else {
    unset($c_editor->PRE);
}
unset($_POST['MTE']);
File::serialize(Mecha::A($c_editor))->saveTo(PLUGIN . DS . '__editor' . DS . 'states' . DS . 'config.txt', 0600);