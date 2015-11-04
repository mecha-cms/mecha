<?php Shield::chunk('header'); ?>
<?php Shield::chunk('sidebar'); ?>
<div class="blog-main">
  <?php if($config->total_articles > 0): ?>
  <?php foreach($articles as $article): ?>
  <?php Shield::lot('article', $article); ?>
  <article class="post post-index" id="post-<?php echo $article->id; ?>">
    <?php Shield::chunk('article.header.index'); ?>
    <?php Shield::chunk('article.body.index'); ?>
    <?php Shield::chunk('article.footer.index'); ?>
  </article>
  <?php endforeach; ?>
  <?php else: ?>
  <article class="post">
    <?php Shield::chunk('204.body', array('s' => $speak->articles)); ?>
  </article>
  <?php endif; ?>
  <?php Shield::chunk('pager'); ?>
</div>
<?php Shield::chunk('footer'); ?>