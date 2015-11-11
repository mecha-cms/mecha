<footer class="post-footer">
  <?php if(Weapon::exist('page_footer')): ?>
  <div><?php Weapon::fire('page_footer', array($page)); ?></div>
  <?php endif; ?>
</footer>