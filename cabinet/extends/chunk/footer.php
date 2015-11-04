    <?php Weapon::fire('cargo_after'); ?>
    <?php Weapon::fire('sword_before'); ?>
    <?php

    $_set = glob(SHIELD . DS . $config->shield . DS . 'assets' . DS . 'sword' . DS . '*.js');
    unset(SHIELD . DS . $config->shield . DS . 'assets' . DS . 'sword' . DS . 'manager.js');
    echo Asset::javascript($_set);

    ?>
    <?php Weapon::fire('sword_after'); ?>
    <?php Weapon::fire('SHIPMENT_REGION_BOTTOM'); ?>
  </body>
</html>