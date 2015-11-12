<?php Shield::lot(array('article' => $article)); ?>
<article class="post post-index" id="post-<?php echo $article->id; ?>">
  <?php Shield::chunk('article.header.index'); ?>
  <?php Shield::chunk('article.body.index'); ?>
  <?php Shield::chunk('article.footer.index'); ?>
</article>