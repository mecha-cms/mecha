<?php Shield::chunk('header'); ?>
<main class="blog-main posts">
  <article class="post" id="post-<?php echo $article->id; ?>">
    <?php Shield::chunk('article.header'); ?>
    <?php Shield::chunk('article.body'); ?>
    <?php Shield::chunk('article.footer'); ?>
  </article>
  <?php Shield::chunk('pager'); ?>
  <?php Shield::chunk('comments'); ?>
</main>
<?php Shield::chunk('footer'); ?>