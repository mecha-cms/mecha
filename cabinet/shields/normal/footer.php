      </div> <!-- .blog-content -->
      <?php Shield::chunk('block.footer'); ?>
    </div> <!-- .blog-wrapper -->
    <?php Weapon::fire('cargo_after'); ?>
    <?php Weapon::fire('sword_before'); ?>
    <?php echo Asset::javascript('assets/sword/layout.js'); ?>
    <?php Weapon::fire('sword_after'); ?>
    <?php Weapon::fire('SHIPMENT_REGION_BOTTOM'); ?>
  </body>
</html>