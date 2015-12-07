<form class="form-plugin" action="<?php echo $config->url_current; ?>/update" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <p><?php echo $speak->plugin_cache_description_content . ' ' . Jot::info($speak->plugin_cache_info_content); ?></p>
  <?php

  $cache_config = File::open(__DIR__ . DS . 'states' . DS . 'config.txt')->unserialize();
  $content = "";
  foreach($cache_config['path'] as $path => $exp) {
      if($exp !== true) {
          $exp = ' ' . $exp;
      } else {
          $exp = "";
      }
      $content .= $path . $exp . "\n";
  }

  ?>
  <p><?php echo Form::textarea('content', trim($content), 'feed/rss', array('class' => array('textarea-block', 'textarea-expand'))); ?></p>
  <p><?php echo Jot::button('action', $speak->update); ?></p>
</form>