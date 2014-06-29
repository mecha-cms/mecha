<!DOCTYPE html>
<html dir="<?php echo $config->language_direction; ?>" class="page-<?php echo $config->page_type; ?>">
  <head>
    <?php Weapon::fire('before'); ?>
    <?php Weapon::fire('shell_before'); ?>
    <link href="<?php echo $config->protocol; ?>maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    <?php echo Asset::stylesheet(array('shell/atom.css', 'shell/layout.css')); ?>
    <?php if(isset($page->css)) echo $page->css; ?>
    <?php Weapon::fire('shell_after'); ?>
  </head>
  <body>
    <?php Weapon::fire('cargo_before'); ?>
    <div class="blog-wrapper">
      <header class="blog-header">
        <?php if($config->url_current == $config->url): ?>
        <h1 class="blog-title"><?php echo $config->title; ?></h1>
        <?php else: ?>
        <h1 class="blog-title"><a href="<?php echo $config->url; ?>"><?php echo $config->title; ?></a></h1>
        <?php endif; ?>
        <p class="blog-slogan"><?php echo $config->slogan; ?></p>
      </header>
      <nav class="blog-navigation">
        <?php echo Menu::get(); ?>
      </nav>
      <div class="blog-content">