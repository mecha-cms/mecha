<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->author; ?></span>
  <span class="grid span-5"><?php echo Form::text('author', Request::get('author', Guardian::wayback('author', $page->author_raw))); ?></span>
</label>