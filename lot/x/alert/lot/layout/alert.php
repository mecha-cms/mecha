<?php

if ($alert = Alert::get()) {
    $out = "";
    foreach ($alert as $v) {
        $out .= new HTML(['p', $v[1], ['class' => $v[2]['type']]]);
    }
    echo $out ? '<div class="alert p">' . $out . '</div>' : "";
}