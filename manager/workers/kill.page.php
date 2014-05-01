<?php echo Notify::read(); ?>
<form class="form-kill" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <h3><?php echo $page->title; ?></h3>
  <p><?php echo $page->description; ?></p>
  <?php if($config->editor_type == 'article'): ?>
  <p><strong><?php echo $page->page_total_comments_text; ?></strong></p>
  <?php if( ! empty($page->css)): ?>
  <pre><code><?php echo substr(Text::parse($page->css)->to_encoded_html, 0, $config->excerpt_length * 3); ?></code></pre>
  <?php endif; ?>
  <?php if( ! empty($page->css)): ?>
  <pre><code><?php echo substr(Text::parse($page->js)->to_encoded_html, 0, $config->excerpt_length * 3); ?></code></pre>
  <?php endif; ?>
  <?php endif; ?>
  <p><button class="btn btn-primary btn-delete" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->yes; ?></button> <a href="<?php echo $config->url . '/' . $config->manager->slug . '/' . $config->editor_type . '/repair/' . $page->id; ?>" class="btn btn-danger btn-cancel"><i class="fa fa-times-circle"></i> <?php echo $speak->no; ?></a></p>
</form>