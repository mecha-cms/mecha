      </div> <!-- .blog-content -->
      <footer class="blog-footer">
        <div class="blog-footer-left">&copy; <a href="<?php echo $config->url; ?>"><?php echo $config->title; ?></a>, <?php echo $speak->manager->powered . ' ' . MECHA_VERSION; ?></div>
        <div class="blog-footer-right">
          <?php if($manager): ?>
          <a href="<?php echo $config->url . '/' . $config->manager->slug; ?>/logout" rel="nofollow"><i class="fa fa-sign-out"></i> <?php echo $speak->log_out; ?></a>
          <?php else: ?>
          <a href="<?php echo $config->url . '/' . $config->manager->slug; ?>/login" rel="nofollow"><i class="fa fa-sign-in"></i> <?php echo $speak->log_in; ?></a>
          <?php endif; ?>
        </div>
      </footer>
    </div> <!-- .blog-wrapper -->
    <?php Weapon::fire('sword_before'); ?>
    <?php echo Asset::javascript('sword/main.js'); ?>
    <?php if(isset($page->js)) echo $page->js; ?>
    <?php Weapon::fire('sword_after'); ?>
    <?php Weapon::fire('cargo_after'); ?>
    <?php Weapon::fire('SHIPMENT_REGION_BOTTOM'); ?>
  </body>
</html>