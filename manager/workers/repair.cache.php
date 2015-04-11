<?php echo $messages; ?>
<form class="form-repair form-cache" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <p>
  <?php echo Form::textarea('content', Guardian::wayback('content', $the_content), null, array(
      'class' => array(
          'textarea-block',
          'textarea-expand',
          'code',
          'MTE'
      )
  )); ?>
  </p>
  <p>
  <?php echo UI::button('action', $speak->update); ?>
  <?php echo UI::btn('destruct', $speak->delete, $config->url . '/' . $config->manager->slug . '/cache/kill/file:' . File::url(str_replace(CACHE . DS, "", $the_name))); ?>
  </p>
</form>