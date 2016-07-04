<?php Shield::chunk('header'); ?>
<main class="blog-main posts">
  <article class="post" id="post-<?php echo $page->id; ?>">
    <?php Shield::chunk('page.header'); ?>
    <?php Shield::chunk('page.body'); ?>
    <?php Shield::chunk('page.footer'); ?>
  </article>
</main>
<?php Shield::chunk('footer'); ?>