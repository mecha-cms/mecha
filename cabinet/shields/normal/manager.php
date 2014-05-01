<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="blog-posts">
  <article class="post">
    <h2 class="post-title"><?php echo $config->page_title; ?></h2>
    <div class="post-body"><?php include $config->cargo; ?></div>
  </article>
</div>

<?php include 'footer.php'; ?>