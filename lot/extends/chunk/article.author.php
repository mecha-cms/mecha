<?php if($config->author->url): ?>
<a href="<?php echo $config->author->url; ?>" rel="author"><?php echo $article->author; ?></a>
<?php else: ?>
<span class="a"><?php echo $article->author; ?></span>
<?php endif; ?>