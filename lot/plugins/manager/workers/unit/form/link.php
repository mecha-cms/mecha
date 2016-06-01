<?php if(Request::get('link') || $page->link_raw): ?>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->link; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('link', Request::get('link', Guardian::wayback('link', $page->link_raw)), $config->protocol, array(
      'class' => 'input-block',
      'pattern' => '(https?:)?\\/\\/.+'
  )); ?>
  </span>
</label>
<?php endif; ?>