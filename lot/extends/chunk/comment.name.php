<h5 class="comment-name">
  <?php if($comment->url !== '#'): ?>
  <a href="<?php echo $comment->url; ?>" rel="nofollow" target="_blank"><?php echo $comment->name; ?></a>
  <?php else: ?>
  <span class="a"><?php echo $comment->name; ?></span>
  <?php endif; ?>
</h5>