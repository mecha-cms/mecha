<?php if($config->comments->allow): ?>
<section class="comments">
  <?php Shield::chunk('comments.header'); ?>
  <?php Shield::chunk('comments.body'); ?>
  <?php Shield::chunk('comments.footer'); ?>
</section>
<?php endif; ?>