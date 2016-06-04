<!DOCTYPE html>
<html dir="<?php echo $config->language_direction; ?>" class="page-<?php echo $config->page_type ? $config->page_type : 'home'; ?>">
  <head>
    <?php Weapon::fire('SHIPMENT_REGION_TOP'); ?>
    <?php Weapon::fire('shell_before'); ?>
    <?php echo Asset::stylesheet(array(
        $config->scheme . ':' . ICON_LIBRARY_PATH,
        'assets/shell/atom.css',
        'assets/shell/layout.css'
    )); ?>
    <?php Weapon::fire('shell_after'); ?>
  </head>
  <body>
    <?php Weapon::fire('cargo_before'); ?>
    <div class="blog-wrapper">
      <?php Shield::chunk('block.header'); ?>
      <?php Shield::chunk('navigation'); ?>
      <div class="blog-content">
        <?php Shield::chunk('sidebar'); ?>