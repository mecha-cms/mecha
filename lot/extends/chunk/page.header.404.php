<header class="post-header">
  <?php Shield::chunk('page.title.404'); ?>
  <?php if(Weapon::exist('page_header')): ?>
  <div><?php Weapon::fire('page_header', array($page)); ?></div>
  <?php endif; ?>
</header>