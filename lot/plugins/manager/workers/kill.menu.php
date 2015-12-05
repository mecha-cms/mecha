<?php echo $messages; ?>
<?php echo Tree::grow($file); ?>
<form class="form-kill form-menu" id="form-kill" action="<?php echo $config->url_current . $config->url_query; ?>" method="post">
  <?php echo Jot::button('action', $speak->yes); ?> <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/field/repair/key:' . $id); ?>
  <?php echo Form::hidden('token', $token); ?>
</form>