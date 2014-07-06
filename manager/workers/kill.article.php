<?php echo Notify::read(); ?>
<form class="form-kill form-article" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <h3><?php echo $article->title; ?></h3>
  <p><?php echo $article->description; ?></p>
  <p><strong><?php echo $article->total_comments_text; ?></strong></p>
  <?php if( ! empty($article->css)): ?>
  <pre><code><?php echo substr(Text::parse($article->css)->to_encoded_html, 0, $config->excerpt_length); ?><?php if(strlen($article->css) > $config->excerpt_length) echo ' &hellip;'; ?></code></pre>
  <?php endif; ?>
  <?php if( ! empty($article->js)): ?>
  <pre><code><?php echo substr(Text::parse($article->js)->to_encoded_html, 0, $config->excerpt_length); ?><?php if(strlen($article->js) > $config->excerpt_length) echo ' &hellip;'; ?></code></pre>
  <?php endif; ?>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->yes; ?></button> <a class="btn btn-reject" href="<?php echo $config->url . '/' . $config->manager->slug . '/article/repair/id:' . $article->id; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->no; ?></a></p>
</form>