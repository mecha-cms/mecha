<form class="form-plugin" action="<?php echo $config->url_current; ?>/update" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <div class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->settings; ?></span>
    <div class="grid span-5">
    <?php

    $c = File::open(__DIR__ . DS . 'states' . DS . 'config.txt')->unserialize();
    foreach($speak->plugin_minifier_title as $k => $v) {
        echo '<div>' . Form::checkbox($k, 1, isset($c[$k]), $v) . '</div>';
    }

    ?>
    </div>
  </div>
  <div class="grid-group">
    <span class="grid span-1"></span>
    <span class="grid span-5"><?php echo Jot::button('action', $speak->update); ?></span>
  </div>
</form>