<div class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->scope; ?></span>
  <span class="grid span-5">
  <?php

  $cache = Guardian::wayback('scope', $page->scope_raw);
  $cache = ',' . Request::get('scope', is_array($cache) ? implode(',', $cache) : $cache) . ',';
  $s = array('article' => $speak->article, 'page' => $speak->page, 'comment' => $speak->comment);
  foreach($s as $k => $v) {
      echo '<div>' . Form::checkbox('scope[]', $k, strpos($cache, ',' . $k . ',') !== false, $v) . '</div>';
  }

  ?>
  </span>
</div>