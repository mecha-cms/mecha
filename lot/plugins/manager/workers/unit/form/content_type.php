<div class="grid-group">
  <span class="grid span-1 form-label"></span>
  <span class="grid span-5"><?php echo Form::checkbox('content_type', $config->html_parser !== false ? $config->html_parser : 'HTML', Request::get('content_type', Guardian::wayback('content_type', $page->content_type_raw)) !== (strpos($config->url_path, '/id:') === false ? false : 'HTML'), $speak->manager->title_html_parser); ?></span>
</div>