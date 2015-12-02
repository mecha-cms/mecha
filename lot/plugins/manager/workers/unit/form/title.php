<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->title; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('title', Request::get('title', Guardian::wayback('title', $page->title_raw)), $speak->manager->placeholder_title, array(
      'class' => 'input-block'
  )); ?>
  </span>
</label>