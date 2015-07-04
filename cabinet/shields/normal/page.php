<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<div class="blog-main">
  <article class="post" id="post-<?php echo $page->id; ?>">
    <?php if($page->link): ?>
    <h2 class="post-title"><a href="<?php echo $page->link; ?>"><?php echo $page->title; ?></a></h2>
    <?php else: ?>
    <h2 class="post-title"><?php echo $page->title; ?></h2>
    <?php endif; ?>
    <div class="post-body"><?php echo $page->content; ?></div>
    <footer class="post-footer">
      <div><?php Weapon::fire('page_footer', array($page)); ?></div>
    </footer>
  </article>
</div>
<?php include 'footer.php'; ?>