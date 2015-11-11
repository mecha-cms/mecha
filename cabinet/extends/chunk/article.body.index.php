<div class="post-body">
  <?php if($article->excerpt): ?>
  <div class="post-excerpt"><?php echo $article->excerpt; ?></div>
  <?php else: ?>
  <div class="post-description">
    <p><?php echo Text::parse($article->description, '->text', '<a><abbr><b><dfn><em><i><strong><sub><sup>'); ?></p>
  </div>
  <?php endif; ?>
</div>