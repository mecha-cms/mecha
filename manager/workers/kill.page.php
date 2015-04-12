<?php echo $messages; ?>
<form class="form-kill form-page" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <h3><?php echo $page->title; ?></h3>
  <p><?php echo $page->description; ?></p>
  <?php if( ! empty($page->css)): ?>
  <pre><code><?php echo substr(Text::parse($page->css, '->encoded_html'), 0, $config->excerpt_length); ?><?php if(strlen($page->css) > $config->excerpt_length) echo ' &hellip;'; ?></code></pre>
  <?php endif; ?>
  <?php if( ! empty($page->js)): ?>
  <pre><code><?php echo substr(Text::parse($page->js, '->encoded_html'), 0, $config->excerpt_length); ?><?php if(strlen($page->js) > $config->excerpt_length) echo ' &hellip;'; ?></code></pre>
  <?php endif; ?>
  <p>
  <?php echo UI::button('action', $speak->yes); ?>
  <?php echo UI::btn('reject', $speak->no, $config->manager->slug . '/page/repair/id:' . $page->id); ?>
  </p>
</form>