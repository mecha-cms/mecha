<?php $hooks = array($page, $segment); echo $messages; ?>
<form class="form-repair form-cache" id="form-repair" action="<?php echo $config->url_current . str_replace('&', '&amp;', $config->url_query); ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <?php

  $e = File::E($path !== false ? $path : "");
  $is_text = $path === false || strpos(',' . SCRIPT_EXT . ',', ',' . $e . ',') !== false;
  $path = File::url($path);

  ?>
  <?php if($is_text && $content !== false): ?>
  <p>
  <?php echo Form::textarea('content', Request::get('content', Guardian::wayback('content', $content)), $speak->manager->placeholder_content, array(
      'class' => array(
          'textarea-block',
          'textarea-expand',
          'code'
      )
  )); ?>
  </p>
  <?php endif; ?>
  <p>
    <?php echo Form::hidden('name', $path); ?>
    <?php Weapon::fire('action_before', $hooks); ?>
    <?php echo $is_text ? Jot::button('action', $speak->update) : ""; ?>
    <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/cache/kill/file:' . $path); ?>
    <?php Weapon::fire('action_after', $hooks); ?>
  </p>
</form>