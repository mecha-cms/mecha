<div class="post-body">
  <?php if($article->excerpt): ?>
  <div class="post-excerpt"><?php echo $article->excerpt; ?></div>
  <?php else: ?>
  <div class="post-description">
    <p><?php echo Text::parse($article->description, '->text', WISE_CELL_I); ?></p>
  </div>
  <?php endif; ?>
</div>