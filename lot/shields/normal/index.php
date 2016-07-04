<?php Shield::chunk('header'); ?>
<main class="blog-main posts">
  <?php if($config->total_articles > 0): ?>
  <?php foreach($articles as $article): ?>
  <?php Shield::lot(array('article' => $article)); ?>
  <article class="post post-index" id="post-<?php echo $article->id; ?>">
    <?php Shield::chunk('article.header.index'); ?>
    <?php Shield::chunk('article.body.index'); ?>
    <?php Shield::chunk('article.footer.index'); ?>
  </article>
  <?php endforeach; ?>
  <?php else: ?>
  <article class="post">
    <?php Shield::chunk('article.body.204'); ?>
  </article>
  <?php endif; ?>
  <?php Shield::chunk('pager'); ?>
</main>
<?php Shield::chunk('footer'); ?>