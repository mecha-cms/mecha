<!DOCTYPE html>
<html dir="<?php echo $config->language_direction; ?>" class="page-<?php echo $config->page_type ? $config->page_type : 'home'; ?>">
  <head>
    <?php Weapon::fire('SHIPMENT_REGION_TOP'); ?>
    <?php Weapon::fire('shell_before'); ?>
    <?php

    $_set = glob(SHIELD . DS . $config->shield . DS . 'assets' . DS . 'shell' . DS . '*.css');
    unset(SHIELD . DS . $config->shield . DS . 'assets' . DS . 'shell' . DS . 'manager.css');
    echo Asset::stylesheet($_set);

    ?>
    <?php Weapon::fire('shell_after'); ?>
  </head>
  <body>
    <?php Weapon::fire('cargo_before'); ?>