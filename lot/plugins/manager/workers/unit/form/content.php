<div class="grid-group">
  <?php $_ = 'unit:' . time(); ?>
  <label class="grid span-1 form-label" for="<?php echo $_; ?>"><?php echo $speak->content; ?></label>
  <div class="grid span-5">
  <?php echo Form::textarea('content', Request::get('content', Guardian::wayback('content', $page->content_raw)), $speak->manager->placeholder_content, array(
      'class' => array(
          'textarea-block',
          'textarea-expand',
          'MTE',
          'MTE-main',
          'code'
      ),
      'id' => $_
  )); ?>
  </div>
</div>