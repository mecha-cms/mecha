<?php echo Notify::read(); ?>
<form class="form-kill form-page" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <h3><?php echo $page->title; ?></h3>
  <p><?php echo $page->description; ?></p>
  <?php if($config->file_type == 'article'): ?>
  <p><strong><?php echo $page->total_comments_text; ?></strong></p>
  <?php endif; ?>
  <?php if( ! empty($page->css)): ?>
  <pre><code><?php echo substr(Text::parse($page->css)->to_encoded_html, 0, $config->excerpt_length); ?><?php if(strlen($page->css) > $config->excerpt_length) echo ' &hellip;'; ?></code></pre>
  <?php endif; ?>
  <?php if( ! empty($page->js)): ?>
  <pre><code><?php echo substr(Text::parse($page->js)->to_encoded_html, 0, $config->excerpt_length); ?><?php if(strlen($page->js) > $config->excerpt_length) echo ' &hellip;'; ?></code></pre>
  <?php endif; ?>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->yes; ?></button> <a class="btn btn-reject" href="<?php echo $config->url . '/' . $config->manager->slug . '/' . $config->file_type . '/repair/id:' . $page->id; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->no; ?></a></p>
</form>