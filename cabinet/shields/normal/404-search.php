<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<div class="blog-posts">
  <article class="post">
    <h2 class="post-title"><?php echo $page->title; ?></h2>
    <div class="post-body"><?php echo $page->content; ?></div>
  </article>
</div>
<?php include 'footer.php'; ?>