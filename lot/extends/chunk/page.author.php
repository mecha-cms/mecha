<?php if($config->author->url): ?>
<a href="<?php echo $config->author->url; ?>" rel="author"><?php echo $page->author; ?></a>
<?php else: ?>
<span class="a"><?php echo $page->author; ?></span>
<?php endif; ?>