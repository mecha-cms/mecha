<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->type; ?></span>
  <span class="grid span-5">
  <?php

  $options = array();
  foreach($types as $type) {
      if($hidden = File::hidden($type)) {
          $type = substr($type, 2);
      }
      $options[($hidden ? '.' : "") . $type] = isset($speak->{$type}) ? $speak->{$type} : Text::parse($type, '->title');
  }

  $cache = Request::get('type', Guardian::wayback('type', $page->type_raw));
  echo Form::select('type', $options, $cache);

  ?>
  </span>
</label>