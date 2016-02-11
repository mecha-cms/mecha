<?php $s = __DIR__ . DS . 'states' . DS; ?>
<fieldset>
  <legend><?php echo $speak->plugin_markdown->title->abbr; ?></legend>
  <label class="grid-group">
    <span class="grid span-6">
      <?php echo Form::textarea('abbr', File::open($s . 'abbr.txt')->read(), '*[CMS]: Content Management System', array(
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
  <legend><?php echo $speak->plugin_markdown->title->a; ?></legend>
  <label class="grid-group">
    <span class="grid span-6">
      <?php echo Form::textarea('a', File::open($s . 'a.txt')->read(), '[' . Text::parse($config->title, '->slug') . ']: &lt;' . $config->url . '&gt; &quot;' . Text::parse($config->title, '->encoded_html') . '&quot;', array(
          'class' => array(
              'textarea-block',
              'textarea-expand',
              'code'
          )
      )); ?>
    </span>
  </label>
</fieldset>
<?php if($c_editor = Config::get('states.plugin_' . md5('__editor'))): ?>
<fieldset>
  <legend><?php echo $speak->plugin_markdown->title->editor; ?></legend>
  <div class="p">
    <div><?php echo Form::checkbox('enableSETextHeader', 1, $c_editor->enableSETextHeader, $speak->plugin_markdown->title->toggle_enableSETextHeader); ?></div>
    <div><?php echo Form::checkbox('closeATXHeader', 1, $c_editor->closeATXHeader, $speak->plugin_markdown->title->toggle_closeATXHeader); ?></div>
    <div><?php echo Form::checkbox('PRE', '~~~\n%s\n~~~', isset($c_editor->PRE), $speak->plugin_markdown->title->toggle_PRE); ?></div>
  </div>
</fieldset>
<?php endif; ?>
<p><?php echo Jot::button('action', $speak->update); ?></p>