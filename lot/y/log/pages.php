<?= self::before(); ?>
<main>
  <?php if ($page->exist): ?>
    <article id="page:<?= $page->id; ?>">
      <h2>
        <?= $page->title; ?>
      </h2>
      <p>
        <?= $page->description; ?>
      </p>
      <?php if ($pages->count): ?>
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
      <?php else: ?>
        <?php if ($site->has('part')): ?>
          <p role="status">
            <?= i('No more %s to show.', 'pages'); ?>
          </p>
        <?php else: ?>
          <p role="status">
            <?= i('No %s yet.', 'pages'); ?>
          </p>
        <?php endif; ?>
      <?php endif; ?>
    </article>
  <?php else: ?>
    <article id="page:0">
      <h2>
        <?= i('Error'); ?>
      </h2>
      <p>
        <?= i('%s does not exist.', 'Page'); ?>
      </p>
    </article>
  <?php endif; ?>
</main>
<nav>
  <?= $pager; ?>
</nav>
<?= self::after(); ?>