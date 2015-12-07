<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->author; ?></span>
  <span class="grid span-5">
    <?php if(Guardian::happy(1)): ?>
    <?php echo Form::text('author', Request::get('author', Guardian::wayback('author', $page->author_raw))); ?>
    <?php else: ?>
    <?php echo Form::hidden('author', $page->author_raw); ?>
    <span class="form-static"><?php echo Jot::icon('lock') . ' ' . $page->author_raw; ?></span>
    <?php endif; ?>
  </span>
</label>