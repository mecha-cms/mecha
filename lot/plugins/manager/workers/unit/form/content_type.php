<?php $s = Config::get('html_parser'); if(count((array) $s->type) > 2): ?>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->manager->title_html_parser_type; ?></span>
    <span class="grid span-5"><?php echo Form::select('content_type', Mecha::eat($s->type)->order('ASC', null, true)->vomit(), $page->content_type_raw); ?></span>
  </label>
<?php else: ?>
  <?php $parser = $s->active; if($parser !== 'HTML'): ?>
  <div class="grid-group">
    <span class="grid span-1 form-label"></span>
    <span class="grid span-5"><?php echo Form::checkbox('content_type', $parser !== false ? $parser : 'HTML', $parser !== 'HTML' && Request::get('content_type', Guardian::wayback('content_type', $page->content_type_raw)) === $parser, $speak->manager->title_html_parser_enable); ?></span>
  </div>
  <?php endif; ?>
<?php endif; ?>