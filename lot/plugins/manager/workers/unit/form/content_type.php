<?php $s = Mecha::A(Config::get('html_parser')); $count = count($s['type']); asort($s['type']); ?>
<?php if($count > 2): ?>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->manager->title_html_parser_type; ?></span>
    <span class="grid span-5"><?php echo Form::select('content_type', $s['type'], $page->content_type_raw); ?></span>
  </label>
<?php else: ?>
  <?php $parser_o = Get::state_config('html_parser.active', 'HTML'); $parser = $s['active']; ?>
  <?php if($count > 1 || $parser_o !== 'HTML' || $parser !== 'HTML'): ?>
  <?php unset($s['type']['HTML']); $value = array_keys($s['type']); ?>
  <?php $value = $parser !== 'HTML' ? $parser : reset($value); ?>
  <div class="grid-group">
    <span class="grid span-1 form-label"></span>
    <span class="grid span-5"><?php echo Form::checkbox('content_type', $value, $parser !== 'HTML' && Request::get('content_type', Guardian::wayback('content_type', $page->content_type_raw)) === $parser, $speak->manager->title_html_parser_enable); ?></span>
  </div>
  <?php endif; ?>
<?php endif; ?>