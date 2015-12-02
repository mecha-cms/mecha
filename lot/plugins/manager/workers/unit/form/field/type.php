<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->type; ?></span>
  <span class="grid span-5">
  <?php

  $cache = Request::get('type', Guardian::wayback('type', $page->type_raw));
  echo Form::select('type', array(
      't' => $speak->text,
      's' => $speak->summary,
      'b' => $speak->boolean,
      'o' => $speak->option,
      'f' => $speak->file,
      'c' => $speak->composer,
      'e' => $speak->editor
  ), $cache[0]);

  ?>
  </span>
</label>