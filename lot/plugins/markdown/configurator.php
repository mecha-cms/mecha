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
  <legend><?php echo $speak->plugin_markdown->title->url; ?></legend>
  <label class="grid-group">
    <span class="grid span-6">
      <?php echo Form::textarea('url', File::open($s . 'url.txt')->read(), '[' . Text::parse($config->title, '->slug') . ']: &lt;' . $config->url . '&gt; &quot;' . Text::parse($config->title, '->encoded_html') . '&quot;', array(
          'class' => array(
              'textarea-block',
              'textarea-expand',
              'code'
          )
      )); ?>
    </span>
  </label>
</fieldset>
<p><?php echo Jot::button('action', $speak->update); ?></p>