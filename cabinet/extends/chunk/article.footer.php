<footer class="post-footer">
  <div><?php echo $speak->posted_by; ?> <?php Shield::chunk('article.author'); ?> <?php echo strtolower($speak->on) . ' ' . $article->date->FORMAT_5; ?></div>
  <?php if(Widget::exist('tagLinks')): ?>
  <div><?php echo Widget::tagLinks(); ?></div>
  <?php endif; ?>
  <?php if(Weapon::exist('article_footer')): ?>
  <div><?php Weapon::fire('article_footer', array($article)); ?></div>
  <?php endif; ?>
</footer>