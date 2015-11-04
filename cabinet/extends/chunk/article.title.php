<h2 class="post-title">
  <?php if($article->link): ?>
  <a href="<?php echo $article->link; ?>"><?php echo $article->title; ?></a>
  <?php else: ?>
  <?php echo $article->title; ?>
  <?php endif; ?>
</h2>