<?php if($config->comments): ?>
<section class="comments">
  <?php Shield::chunk('comments.header'); ?>
  <?php Shield::chunk('comments.body'); ?>
  <?php Shield::chunk('comments.footer'); ?>
</section>
<?php endif; ?>