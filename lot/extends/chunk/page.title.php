<h2 class="post-title">
  <?php if($page->link): ?>
  <a href="<?php echo $page->link; ?>"><?php echo $page->title; ?></a>
  <?php else: ?>
  <span class="a"><?php echo $page->title; ?></span>
  <?php endif; ?>
</h2>