<ul>
  <li>
    <?php if ($site->is('home')): ?>
    <span>
      <?= i('Home'); ?>
    </span>
    <?php else: ?>
    <a href="<?= $url; ?>">
      <?= i('Home'); ?>
    </a>
    <?php endif; ?>
  </li>
  <?php foreach ($links as $link): ?>
  <li>
    <?php if ($link->active): ?>
    <span>
      <?= $link->title; ?>
    </span>
    <?php else: ?>
    <a href="<?= $link->link ?: $link->url; ?>">
      <?= $link->title; ?>
    </a>
    <?php endif; ?>
  </li>
  <?php endforeach; ?>
</ul>