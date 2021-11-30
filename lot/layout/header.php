<header>
  <h1>
    <?php if ($site->is('home')): ?>
      <span>
        <?= $site->title; ?>
      </span>
    <?php else: ?>
      <a href="<?= $url; ?>">
        <?= $site->title; ?>
      </a>
    <?php endif; ?>
  </h1>
  <p>
    <?= $site->description; ?>
  </p>
  <?= self::nav(); ?>
</header>