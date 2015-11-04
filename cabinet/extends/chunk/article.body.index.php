<div class="post-body">
  <?php if($article->excerpt): ?>
  <div class="post-excerpt"><?php echo $article->excerpt; ?></div>
  <?php else: ?>
  <div class="post-description"><?php echo $article->description; ?></div>
  <?php endif; ?>
</div>