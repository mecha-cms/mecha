<h2 class="post-title">
  <?php if($article->link): ?>
  <a href="<?php echo $article->link; ?>"><?php echo $article->title; ?></a>
  <?php else: ?>
  <span class="a"><?php echo $article->title; ?></span>
  <?php endif; ?>
</h2>