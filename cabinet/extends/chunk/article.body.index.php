<div class="post-body">
  <?php if($article->excerpt): ?>
  <div class="post-excerpt"><?php echo $article->excerpt; ?></div>
  <?php else: ?>
  <div class="post-description">
    <p><?php echo Text::parse($article->description, '->text', '<a><abbr><b><code><del><dfn><em><i><ins><kbd><mark><strong><sub><sup><time><u>'); ?></p>
  </div>
  <?php endif; ?>
</div>