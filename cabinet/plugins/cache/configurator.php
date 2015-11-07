<form class="form-plugin" action="<?php echo $config->url_current; ?>/update" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <p><?php echo $speak->plugin_cache_content_description . ' ' . Jot::info($speak->plugin_cache_content_info); ?></p>
  <?php

  $cache_config = File::open(PLUGIN . DS . File::B(__DIR__) . DS . 'states' . DS . 'config.txt')->unserialize();
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