<?php

function construct_refueling_point($content) {
    if( ! Text::check($content)->has('<!-- block:')) {
        return $content;
    }
    global $config;
    $form_tip = $config->language === 'id_ID' ? 'Menggunakan proyek kode sumber terbuka itu sangatlah menyenangkan dan murah, akan tetapi kita juga perlu biaya untuk memelihara dan menjaga mereka tetap ada di &lt;code&gt;www&lt;/code&gt;.' : 'Using an open source project is incredibly fun and cheap, but we also need costs to maintain and keep them exist in the &lt;code&gt;www&lt;/code&gt;.';
    $form_donate = '---

<form class="form-donate" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
  <input name="cmd" type="hidden" value="_s-xclick">
  <input name="hosted_button_id" type="hidden" value="TNVGH7NQ7E4EU">
  <input name="submit" type="image" class="help" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" alt="PayPal &ndash; The safer, easier way to pay online!" title="' . $form_tip . '">
  <img alt="" src="https://www.paypalobjects.com/id_ID/i/scr/pixel.gif" width="1" height="1">
</form>';
    return str_replace('<!-- block:donate -->', $form_donate, $content);
}

Filter::add('plugin:shortcode', 'construct_refueling_point');
Filter::add('shield:shortcode', 'construct_refueling_point');