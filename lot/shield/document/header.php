<!DOCTYPE html>
<html dir="<?php echo $site->direction; ?>" class="page-<?php echo $site->is ?: 'home'; ?>">
  <head>
    <meta charset="<?php echo $site->charset; ?>">
    <meta name="viewport" content="width=device-width">
    <?php if ($s = To::text($page->description($site->description))): ?>
    <meta name="description" content="<?php echo $s; ?>">
    <?php endif; ?>
    <?php if ($page->state === 'archive'): ?>
    <!-- Prevent search engines from indexing a page with `archive` state -->
    <meta name="robots" content="noindex">
    <?php endif; ?>
    <meta name="author" content="<?php echo $page->author; ?>">
    <title><?php echo To::text($site->page->title); ?></title>
    <link href="<?php echo $url; ?>/favicon.ico" rel="shortcut icon">
  </head>
  <body>
    <header>
      <h1>
        <?php if (!$url->path || $url->path === $site->slug): ?>
        <span><?php echo $site->title; ?></span>
        <?php else: ?>
        <a href="<?php echo $url; ?>"><?php echo $site->title; ?></a>
        <?php endif; ?>
      </h1>
      <p><?php echo $site->description; ?></p>
      <nav><?php Shield::get('nav'); ?></nav>
    </header>