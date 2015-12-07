<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->message; ?></span>
  <span class="grid span-5">
  <?php echo Form::textarea('message', Request::get('message', Guardian::wayback('message', $page->message_raw)), null, array(
      'class' => array(
          'textarea-block',
          'textarea-expand',
          'MTE',
          'code'
      )
  )); ?>
  </span>
</label>