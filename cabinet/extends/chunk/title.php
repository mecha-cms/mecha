<h1 class="blog-title">
  <?php if($config->url !== $config->url_current): ?>
  <a href="<?php echo $config->url; ?>"><?php echo $config->title; ?></a>
  <?php else: ?>
  <?php echo $config->title; ?>
  <?php endif; ?>
</h1>