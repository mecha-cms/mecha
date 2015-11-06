<form class="form-plugin" action="<?php echo $config->url_current; ?>/update" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <p><?php echo $speak->plugin_cache_content_description; ?></p>
  <?php

  $cache_config = File::open(PLUGIN . DS . File::B(__DIR__) . DS . 'states' . DS . 'config.txt')->unserialize();
  $content = "";
  foreach($cache_config['path'] as $path => $expire) {
      if($expire !== false) {
          $expire = ' ' . $expire;
      }
      $content .= $path . $expire . "\n";
  }

  ?>
  <p><?php echo Form::textarea('content', trim($content), 'feed/rss', array('class' => array('textarea-block', 'textarea-expand'))); ?></p>
  <p><?php echo Jot::button('action', $speak->update); ?></p>
</form>