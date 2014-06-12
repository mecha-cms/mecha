<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="blog-posts">
  <article class="post" id="post-<?php echo $page->id; ?>">
    <h2 class="post-title"><?php echo $page->title; ?></h2>
    <div class="post-body"><?php echo $page->content; ?></div>
    <footer class="post-footer">
      <div><?php Weapon::fire('page_footer', array($page)); ?></div>
    </footer>
  </article>
</div>

<?php include 'footer.php'; ?>