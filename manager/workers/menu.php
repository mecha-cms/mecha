<?php echo $messages; ?>
<form class="form-menu" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <p>
  <?php echo Form::textarea('content', Guardian::wayback('content', $the_content), null, array(
      'class' => array(
          'textarea-block',
          'textarea-expand',
          'code',
          'MTE'
      ),
      'data-MTE-config' => '{"tabSize":"    "}'
  )); ?>
  </p>
  <p><?php echo Jot::button('action', $speak->update); ?></p>
</form>
<hr>
<?php echo Config::speak('file:menu'); ?>