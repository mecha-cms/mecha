<?php

$c_markdown = $config->states->{'plugin_' . md5(File::B(__DIR__))};

// Convert array to pattern ...
$a = $abbr = "";
$t = (array) $c_markdown->predef_titles;
foreach($c_markdown->predef_urls as $k => $v) {
    $a .= '[' . $k . ']: ' . $v . (isset($t[$k]) ? ' "' . $t[$k] . '"' : "") . "\n";
}

// --ibid
foreach($c_markdown->predef_abbr as $k => $v) {
    $abbr .= '*[' . $k . ']:' . ($v ? ' ' . $v : "") . "\n";
}

?>
<fieldset>
  <legend><?php echo $speak->plugin_markdown->title->a; ?></legend>
  <label class="grid-group">
    <span class="grid span-6">
      <?php echo Form::textarea('a', trim($a), '[1]: ' . $config->url . ' &quot;' . Text::parse($config->title, '->encoded_html') . '&quot;', array(
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
  <legend><?php echo $speak->plugin_markdown->title->abbr; ?></legend>
  <label class="grid-group">
    <span class="grid span-6">
      <?php echo Form::textarea('abbr', trim($abbr), '*[CMS]: Content Management System', array(
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
    <span class="grid span-2 form-label"><?php echo $speak->plugin_markdown->title->footnote_id_prefix; ?></span>
    <span class="grid span-4"><?php echo Form::text('fn_id_prefix', $c_markdown->fn_id_prefix, null, array('class' =>'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_markdown->title->footnote_link_title; ?></span>
    <span class="grid span-4"><?php echo Form::text('fn_link_title', $c_markdown->fn_link_title, null, array('class' =>'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_markdown->title->footnote_link_back_title; ?></span>
    <span class="grid span-4"><?php echo Form::text('fn_backlink_title', $c_markdown->fn_backlink_title, null, array('class' =>'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_markdown->title->footnote_class; ?></span>
    <span class="grid span-4"><?php echo Form::text('fn_class', $c_markdown->fn_class, 'footnotes', array('class' =>'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_markdown->title->footnote_link_class; ?></span>
    <span class="grid span-4"><?php echo Form::text('fn_link_class', $c_markdown->fn_link_class, 'footnote-ref', array('class' =>'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_markdown->title->footnote_link_back_class; ?></span>
    <span class="grid span-4"><?php echo Form::text('fn_backlink_class', $c_markdown->fn_backlink_class, 'footnote-backref', array('class' =>'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_markdown->title->code_class_prefix; ?></span>
    <span class="grid span-4"><?php echo Form::text('code_class_prefix', $c_markdown->code_class_prefix, 'language-', array('class' =>'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_markdown->title->table_class; ?></span>
    <span class="grid span-4"><?php echo Form::text('table_class', $c_markdown->table_class, 'table-bordered table-full-width', array('class' =>'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->plugin_markdown->title->table_data_class; ?></span>
    <span class="grid span-4"><?php echo Form::text('table_align_class_tmpl', $c_markdown->table_align_class_tmpl, 'text-%%', array('class' =>'input-block')); ?></span>
  </label>
  <div class="grid-group">
    <span class="grid span-2"></span>
    <div class="grid span-4">
      <div><?php echo Form::checkbox('no_markup', true, $c_markdown->no_markup, $speak->plugin_markdown->title->disallow_html_tag); ?></div>
      <div><?php echo Form::checkbox('no_entities', true, $c_markdown->no_entities, $speak->plugin_markdown->title->disallow_html_entity); ?></div>
      <div><?php echo Form::checkbox('code_attr_on_pre', true, $c_markdown->code_attr_on_pre, $speak->plugin_markdown->title->code_attr_on_pre); ?></div>
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