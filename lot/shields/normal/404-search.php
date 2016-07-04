<?php Shield::chunk('header'); ?>
<main class="blog-main posts">
  <article class="post">
    <?php Shield::chunk('page.header.404'); ?>
    <div class="post-body">
      <p><?php echo $speak->notify_error_not_found; ?></p>
    </div>
  </article>
</main>
<?php Shield::chunk('footer'); ?>