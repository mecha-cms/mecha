<section class="comment comment-<?php echo $response->status; ?>" id="comment-<?php echo $response->id; ?>">
  <?php Shield::chunk('comment.avatar'); ?>
  <?php Shield::chunk('comment.header'); ?>
  <?php Shield::chunk('comment.body'); ?>
  <?php Shield::chunk('comment.footer'); ?>
</section>