<?= self::before(); ?>
<main>
  <article id="page:<?= $page->id; ?>">
    <h2>
      <?= $page->title; ?>
    </h2>
    <?php if ($site->has('parent')): ?>
    <p>
      <time datetime="<?= $page->time->ISO8601; ?>">
        <?= $page->time->{r('-', '_', $site->language)}; ?>
      </time>
    </p>
    <?php endif; ?>
    <?= $page->content; ?>
    <?php if ($page->link): ?>
    <p>
      <a href="<?= $page->link; ?>" rel="nofollow" target="_blank">
        <?= i('Link'); ?> &#x21E2;
      </a>
    </p>
    <?php endif; ?>
  </article>
</main>
<?php if ($site->has('page') && $site->has('parent')): ?>
<nav>
  <?= $pager; ?>
</nav>
<?php endif; ?>
<?= self::after(); ?>