<?php

$e = File::E( ! is_null($path) ? $path : "");
$is_text = is_null($path) || strpos(',' . SCRIPT_EXT . ',', ',' . $e . ',') !== false;

?>
<?php if($is_text && $content !== false): ?>
<p>
<?php echo Form::textarea('content', Guardian::wayback('content', $content), $speak->manager->placeholder_content, array(
    'class' => array(
        'textarea-block',
        'textarea-expand',
        'code'
    )
)); ?>
</p>
<?php endif; ?>
<p>
  <?php if($e === 'cache'): ?>
  <?php echo Form::hidden('name', File::url($path)); ?>
  <?php else: ?>
  <?php echo Form::text('name', Guardian::wayback('name', File::url($path)), $speak->manager->placeholder_file_name); ?>
  <?php endif; ?>
  <?php if(strpos($config->url_path, '/repair/file:') === false): ?>
  <?php echo Jot::button('construct', $speak->create); ?>
  <?php else: ?>
  <?php echo Jot::button('action', $is_text ? $speak->update : $speak->rename); ?>
  <?php endif; ?> <?php if(strpos($config->url_path, '/repair/file:') !== false): ?>
  <?php echo Jot::btn('destruct', $speak->delete, $path_destruct); ?>
  <?php else: ?>
  <?php echo Jot::btn('reject', $speak->cancel, $path_reject); ?>
  <?php endif; ?>
</p>