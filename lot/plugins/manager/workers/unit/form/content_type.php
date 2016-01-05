<?php if($config->html_parser->active !== 'HTML'): ?>
<div class="grid-group">
  <span class="grid span-1 form-label"></span>
  <span class="grid span-5"><?php echo Form::checkbox('content_type', $config->html_parser->active !== false ? $config->html_parser->active : 'HTML', $config->html_parser->active !== 'HTML' && Request::get('content_type', Guardian::wayback('content_type', $page->content_type_raw)) === $config->html_parser->active, $speak->manager->title_html_parser_enable); ?></span>
</div>
<?php endif; ?>