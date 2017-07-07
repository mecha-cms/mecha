<ul><!--
  --><li>
    <?php if (!$url->path || $url->path === $site->path): ?>
    <span><?php echo $language->home; ?></span>
    <?php else: ?>
    <a href="<?php echo $url; ?>"><?php echo $language->home; ?></a>
    <?php endif; ?>
  </li><!--
  <?php if ($menus = Get::pages(PAGE, 'page', [1, 'slug'], 'slug')): ?>
    <?php foreach ($menus as $menu): ?>
    <?php if ($menu === $site->path) continue; ?>
    <?php

    $p = Page::open(PAGE . DS . $menu . '.page')->get([
        'url' => null,
        'title' => To::title($menu),
        'link' => null
    ]);

    ?>
    --><li>
      <?php if ($url->path === $menu || strpos($url->path . '/', $menu . '/') === 0): ?>
      <span><?php echo $p['title']; ?></span>
      <?php else: ?>
      <a href="<?php echo $p['link'] ?: $p['url']; ?>"><?php echo $p['title']; ?></a>
      <?php endif; ?>
    </li><!--
    <?php endforeach; ?>
  <?php endif; ?>
--></ul>