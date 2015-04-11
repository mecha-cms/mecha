<form class="form-plugin" action="<?php echo $config->url_current; ?>/update" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <p><?php echo UI::button('action', $speak->update); ?></p>
</form>