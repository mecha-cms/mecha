<header class="post-header">
  <?php Shield::chunk('article.time'); ?>
  <h4 class="post-title">
    <?php if($article->link): ?>
    <a href="<?php echo $article->link; ?>"><?php echo $article->title; ?></a>
    <?php elseif($article->url): ?>
    <a href="<?php echo $article->url; ?>"><?php echo $article->title; ?></a>
    <?php else: ?>
    <span class="a"><?php echo $article->title; ?></span>
    <?php endif; ?>
  </h4>
  <?php if(Weapon::exist('article_header')): ?>
  <div><?php Weapon::fire('article_header', array($article)); ?></div>
  <?php endif; ?>
</header>