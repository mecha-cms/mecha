<?= self::before(); ?>
<main>
  <article id="page:<?= $page->id; ?>">
    <h2>
      <?= $page->title; ?>
    </h2>
    <p>
      <?= $page->description; ?>
    </p>
    <?php foreach ($pages as $page): ?>
      <article id="page:<?= $page->id; ?>">
        <h3>
          <?php if ($page->link): ?>
            <a href="<?= $page->link; ?>" rel="nofollow" target="_blank">
              <?= $page->title; ?> &#x21E2;
            </a>
          <?php else: ?>
            <a href="<?= $page->url; ?>">
              <?= $page->title; ?>
            </a>
          <?php endif; ?>
        </h3>
        <p>
          <?= $page->description; ?>
        </p>
      </article>
    <?php endforeach; ?>
  </article>
</main>
<nav>
  <?= $pager; ?>
</nav>
<?= self::after(); ?>