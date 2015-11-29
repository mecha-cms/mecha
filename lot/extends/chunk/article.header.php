<header class="post-header">
  <?php Shield::chunk('article.time'); ?>
  <?php Shield::chunk('article.title'); ?>
  <?php if(Weapon::exist('article_header')): ?>
  <div><?php Weapon::fire('article_header', array($article)); ?></div>
  <?php endif; ?>
</header>