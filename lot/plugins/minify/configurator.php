<div class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->settings; ?></span>
  <div class="grid span-5">
  <?php

  $c_minify = $config->states->{'plugin_' . md5(File::B(__DIR__))};
  foreach($speak->plugin_minify->title as $k => $v) {
      echo '<div>' . Form::checkbox($k, 1, isset($c_minify->{$k}), $v) . '</div>';
  }

  ?>
  </div>
</div>