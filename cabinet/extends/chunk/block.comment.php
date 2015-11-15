<section class="comment comment-<?php echo $comment->status; ?>" id="comment-<?php echo $comment->id; ?>">
  <?php Shield::chunk('comment.avatar'); ?>
  <?php Shield::chunk('comment.header'); ?>
  <?php Shield::chunk('comment.body'); ?>
  <?php Shield::chunk('comment.footer'); ?>
</section>