<?php

$s = __DIR__ . DS . 'states' . DS;
$url = File::open($s . 'url.txt')->read();
$abbr = File::open($s . 'abbr.txt')->read();

?>
<fieldset>
  <legend><?php echo $speak->plugin_markdown_title_abbr; ?></legend>
  <label class="grid-group">
    <span class="grid span-6">
      <?php echo Form::textarea('abbr', $abbr, '*[CMS]: Content Management System', array(
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
  <legend><?php echo $speak->plugin_markdown_title_url; ?></legend>
  <label class="grid-group">
    <span class="grid span-6">
      <?php echo Form::textarea('url', $url, '[' . Text::parse($config->title, '->slug') . ']: &lt;' . $config->url . '&gt; &quot;' . Text::parse($config->title, '->encoded_html') . '&quot;', array(
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