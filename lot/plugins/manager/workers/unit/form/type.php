<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->type; ?></span>
  <span class="grid span-5">
  <?php

  $cache = Request::get('type', Guardian::wayback('type', $page->type_raw));
  echo Form::select('type', $types, $cache);

  ?>
  </span>
</label>