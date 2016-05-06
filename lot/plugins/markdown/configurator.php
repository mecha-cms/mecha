<?php

$c_markdown = $config->states->{'plugin_' . md5(File::B(__DIR__))};

// Convert array to pattern ...
$links = $abbreviations = "";
foreach($c_markdown->links as $k => $v) {
    $links .= '[' . $k . ']: ' . $v->url . (isset($v->title) ? ' "' . $v->title . '"' : "") . "\n";
}

// --ibid
foreach($c_markdown->abbreviations as $k => $v) {
    $abbreviations .= '*[' . $k . ']:' . ($v ? ' ' . $v : "") . "\n";
}

?>
<fieldset>
  <legend><?php echo $speak->plugin_markdown->title->links; ?></legend>
  <label class="grid-group">
    <span class="grid span-6">
      <?php echo Form::textarea('links', trim($links), '[1]: ' . $config->url . ' &quot;' . Text::parse($config->title, '->encoded_html') . '&quot;', array(
          'class' => array(
              'textarea-block',
              'textarea-expand',
              'code'
          )
      )); ?>
    </span>
  </label>
</fieldset>
<fieldset>
  <legend><?php echo $speak->plugin_markdown->title->abbreviations; ?></legend>
  <label class="grid-group">
    <span class="grid span-6">
      <?php echo Form::textarea('abbreviations', trim($abbreviations), '*[CMS]: Content Management System', array(
          'class' => array(
              'textarea-block',
              'textarea-expand',
              'code'
          )
      )); ?>
    </span>
  </label>
</fieldset>
<fieldset>
  <legend><?php echo $speak->settings; ?></legend>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_markdown->title->code_class; ?></span>
    <span class="grid span-4"><?php echo Form::text('code_class', $c_markdown->code_class, 'language-%s', array('class' =>'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_markdown->title->table_class; ?></span>
    <span class="grid span-4"><?php echo Form::text('table_class', $c_markdown->table_class, 'table-bordered table-full-width', array('class' =>'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_markdown->title->table_align_class; ?></span>
    <span class="grid span-4"><?php echo Form::text('table_align_class', $c_markdown->table_align_class, 'text-%s', array('class' =>'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_markdown->title->footnote_class; ?></span>
    <span class="grid span-4"><?php echo Form::text('footnote_class', $c_markdown->footnote_class, 'footnotes', array('class' =>'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_markdown->title->footnote_link_class; ?></span>
    <span class="grid span-4"><?php echo Form::text('footnote_link_class', $c_markdown->footnote_link_class, 'footnote-ref', array('class' =>'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_markdown->title->footnote_back_link_class; ?></span>
    <span class="grid span-4"><?php echo Form::text('footnote_back_link_class', $c_markdown->footnote_back_link_class, 'footnote-backref', array('class' =>'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_markdown->title->footnote_link_id; ?></span>
    <span class="grid span-4"><?php echo Form::text('footnote_link_id', $c_markdown->footnote_link_id, 'fn:%s', array('class' =>'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_markdown->title->footnote_back_link_id; ?></span>
    <span class="grid span-4"><?php echo Form::text('footnote_back_link_id', $c_markdown->footnote_back_link_id, 'fnref%s:%s', array('class' =>'input-block')); ?></span>
  </label>
  <div class="grid-group">
    <div class="grid span-2"></div>
    <div class="grid span-4">
      <div><?php echo Form::checkbox('links_external_attr[rel]', 'nofollow', isset($c_markdown->links_external_attr->rel), $speak->plugin_markdown->title->is_links_external_attr_rel); ?></div>
      <div><?php echo Form::checkbox('links_external_attr[target]', '_blank', isset($c_markdown->links_external_attr->target), $speak->plugin_markdown->title->is_links_external_attr_target); ?></div>
      <div><?php echo Form::checkbox('code_block_attr_on_parent', true, isset($c_markdown->code_block_attr_on_parent), $speak->plugin_markdown->title->is_code_block_attr_on_parent_active); ?></div>
      <div><?php echo Form::checkbox('__setBreaksEnabled', true, isset($c_markdown->__setBreaksEnabled), $speak->plugin_markdown->title->is_set_break_enable_active . ' ' . Jot::info($speak->plugin_markdown->description->is_set_break_enable_active)); ?></div>
      <div><?php echo Form::checkbox('__setUrlsLinked', true, isset($c_markdown->__setUrlsLinked), $speak->plugin_markdown->title->is_set_urls_link_active . ' ' . Jot::info($speak->plugin_markdown->description->is_set_urls_link_active)); ?></div>
      <div><?php echo Form::checkbox('__setMarkupEscaped', true, isset($c_markdown->__setMarkupEscaped), $speak->plugin_markdown->title->is_set_markup_escape_active); ?></div>
    </div>
  </div>
</fieldset>
<?php if($c_editor = Config::get('states.plugin_' . md5('__editor'))): ?>
<fieldset>
  <legend><?php echo $speak->plugin_markdown->title->editor; ?></legend>
  <div class="p">
    <div><?php echo Form::checkbox('MTE[enableSETextHeader]', 1, $c_editor->enableSETextHeader, $speak->plugin_markdown->title->is_header_setext_active); ?></div>
    <div><?php echo Form::checkbox('MTE[closeATXHeader]', 1, $c_editor->closeATXHeader, $speak->plugin_markdown->title->is_header_atx_close_active); ?></div>
    <div><?php echo Form::checkbox('MTE[PRE]', '~~~\n%s\n~~~', isset($c_editor->PRE), $speak->plugin_markdown->title->is_code_block_fence_active); ?></div>
  </div>
</fieldset>
<?php endif; ?>
<p><?php echo Jot::button('action', $speak->update); ?></p>