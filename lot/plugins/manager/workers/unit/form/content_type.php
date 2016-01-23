<?php $parser = Config::get('html_parser.active'); if($parser !== 'HTML'): ?>
<div class="grid-group">
  <span class="grid span-1 form-label"></span>
  <span class="grid span-5"><?php echo Form::checkbox('content_type', $parser !== false ? $parser : 'HTML', $parser !== 'HTML' && Request::get('content_type', Guardian::wayback('content_type', $page->content_type_raw)) === $parser, $speak->manager->title_html_parser_enable); ?></span>
</div>
<?php endif; ?>