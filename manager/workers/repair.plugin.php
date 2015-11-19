<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><?php echo Jot::icon('cog', 'fw') . ' ' . $speak->config; ?></a>
  <a class="tab" href="#tab-content-2"><?php echo Jot::icon('user', 'fw') . ' ' . $speak->about; ?></a>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <div class="tab-content" id="tab-content-1">
  <?php if($file->configurator): ?>
  <?php include $file->configurator; ?>
  <?php else: ?>
  <p><?php echo Config::speak('notify_not_available', $speak->config); ?></p>
  <?php endif; ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <p class="about-author">
    <?php echo Cell::strong($speak->author . ':') . ' ' . $file->author; ?><?php if(isset($file->url) && $file->url !== '#'): ?> <?php echo Cell::a($file->url, Jot::icon('external-link-square'), '_blank', array(
        'class' => array(
            'about-url',
            'help'
        ),
        'title' => $speak->link,
        'rel' => 'nofollow'
    )); ?>
    <?php endif; ?>
    </p>
    <h3 class="about-title"><?php echo $file->title; ?><?php if(isset($file->version)): ?> <code class="about-version"><?php echo $file->version; ?></code><?php endif; ?></h3>
    <div class="about-content"><?php echo $file->content; ?></div>
  </div>
</div>