<!DOCTYPE html>
<html dir="<?php echo strtolower($config->language_direction); ?>" class="page-<?php echo $config->page_type; ?>">
  <head>

    <meta charset="<?php echo strtolower($config->charset); ?>">
    <meta name="viewport" content="width=device-width">
    <?php Weapon::fire('before'); ?>
    <meta name="description" content="<?php echo Text::parse(isset($page->description) ? $page->description : $config->description)->to_encoded_html; ?>">
    <meta name="author" content="<?php echo $config->author; ?>">

    <title><?php echo strip_tags($config->page_title); ?></title>

    <!--[if IE]>
      <script src="<?php echo $config->protocol; ?>html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <?php Weapon::fire('shell_before'); ?>
    <link href="<?php echo $config->protocol; ?>maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    <?php echo Asset::stylesheet(array('shell/atom.css', 'shell/layout.css')); ?>
    <?php if(isset($page->css)) echo $page->css; ?>
    <?php Weapon::fire('shell_after'); ?>

    <link href="<?php echo $config->url; ?>/favicon.ico" rel="shortcut icon" type="image/x-icon">
    <link href="<?php echo $config->url_current; ?>" rel="canonical">
    <link href="<?php echo $config->url; ?>/sitemap" rel="sitemap">
    <link href="<?php echo $config->url; ?>/feeds/rss" rel="alternate" type="application/rss+xml" title="<?php echo $config->title; ?> Feed">

  </head>
  <body spellcheck="false">

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