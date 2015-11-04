<h2 class="post-title">
  <?php if($page->link): ?>
  <a href="<?php echo $page->link; ?>"><?php echo $page->title; ?></a>
  <?php else: ?>
  <?php echo $page->title; ?>
  <?php endif; ?>
</h2>