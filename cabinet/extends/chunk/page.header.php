<header class="post-header">
  <?php Shield::chunk('page.title'); ?>
  <?php if(Weapon::exist('page_header')): ?>
  <div><?php Weapon::fire('page_header', array($page)); ?></div>
  <?php endif; ?>
</header>