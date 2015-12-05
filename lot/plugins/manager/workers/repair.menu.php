<?php echo $messages; ?>
<form class="form-<?php echo $id === false ? 'ignite' : 'repair'; ?> form-menu" id="form-<?php echo $id === false ? 'ignite' : 'repair'; ?>" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <p>
  <?php echo Form::textarea('content', Request::get('content', Guardian::wayback('content', $content)), null, array(
      'class' => array(
          'textarea-block',
          'textarea-expand',
          'code'
      ),
      'data-MTE-config' => '{"tabSize":"    "}'
  )); ?>
  </p>
  <p>
    <?php echo Form::text('key', Request::get('key', Guardian::wayback('key', $id ? 'Menu::' . $id . '()' : "")), 'Menu::navigation()'); ?>
    <?php if(strpos($config->url_path, '/repair/key:') === false): ?>
    <?php echo Jot::button('construct', $speak->create); ?>
    <?php else: ?>
    <?php echo Jot::button('action', $speak->update); ?>
    <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/menu/kill/key:' . $id); ?>
    <?php endif; ?>
  </p>
</form>
<hr>
<?php echo Guardian::wizard($segment); ?>