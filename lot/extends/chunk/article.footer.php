<footer class="post-footer">
  <div><?php echo $speak->posted_by; ?> <?php Shield::chunk('article.author'); ?> <?php echo strtolower($speak->on) . ' ' . $article->date->FORMAT_5; ?></div>
  <div><?php echo Shield::chunk('article.tags'); ?></div>
  <?php if(Weapon::exist('article_footer')): ?>
  <div><?php Weapon::fire('article_footer', array($article)); ?></div>
  <?php endif; ?>
</footer>