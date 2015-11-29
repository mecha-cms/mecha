<h1 class="blog-title">
  <?php if($config->url !== $config->url_current): ?>
  <a href="<?php echo $config->url; ?>"><?php echo $config->title; ?></a>
  <?php else: ?>
  <span class="a"><?php echo $config->title; ?></span>
  <?php endif; ?>
</h1>