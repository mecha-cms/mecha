<?php if($menus = Menu::navigation()): ?>
<nav class="blog-navigation menus">
  <?php echo $menus; ?>
</nav>
<?php endif; ?>