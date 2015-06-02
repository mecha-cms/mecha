<p>
<?php echo Form::textarea('content', Guardian::wayback('content', $the_content), null, array(
    'class' => array(
        'textarea-block',
        'textarea-expand',
        'code',
        'MTE'
    ),
    'data-MTE-config' => '{"tabSize":"' . (isset($editor_tab_size) ? $editor_tab_size : TAB) . '"}'
)); ?>
</p>
<p>
  <?php if(isset($action_update) && $action_update !== false): ?>
  <?php echo Jot::button('action', $speak->update); ?>
  <?php endif; ?>
  <?php if(isset($action_create) && $action_create !== false): ?>
  <?php echo Jot::button('construct', $speak->create); ?>
  <?php endif; ?>
  <?php if(isset($path_destruct) && $path_destruct !== false): ?>
  <?php echo Jot::btn('destruct', $speak->delete, $path_destruct); ?>
  <?php endif; ?>
  <?php if(isset($path_reject) && $path_reject !== false): ?>
  <?php echo Jot::btn('reject', $speak->cancel, $path_reject); ?>
  <?php endif; ?>
</p>