<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<div class="blog-main">
  <article class="post">
    <h2 class="post-title"><?php echo $config->page_title; ?></h2>
    <div class="post-body">
      <?php if($cargo = File::exist($config->cargo)) include $cargo; ?>
    </div>
  </article>
</div>
<?php include 'footer.php'; ?>