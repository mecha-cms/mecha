<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->author; ?></span>
  <span class="grid span-5">
    <?php if(Guardian::get('status') === 'pilot'): ?>
      <?php echo Form::text('author', Guardian::wayback('author', $page->author)); ?>
    <?php else: ?>
      <?php echo Form::hidden('author', $page->author); ?>
      <span class="form-static"><?php echo Jot::icon('lock') . ' ' . $page->author; ?></span>
    <?php endif; ?>
  </span>
</label>