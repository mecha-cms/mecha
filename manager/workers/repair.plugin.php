<div class="tab-area cf">
  <a class="tab active" href="#tab-content-1"><i class="fa fa-cog"></i> <?php echo $speak->config; ?></a>
  <a class="tab" href="#tab-content-2"><i class="fa fa-user"></i> <?php echo $speak->about; ?></a>
</div>
<div class="tab-content-area">
  <?php echo Notify::read(); ?>
  <div class="tab-content" id="tab-content-1">
    <?php

    if($page->configurator) {
        include $page->configurator;
    } else {
        echo '<p>' . Config::speak('notify_not_available', array($speak->config)) . '</p>';
    }

    ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <div class="plugin-about">
    <p class="plugin-author"><strong><?php echo $speak->author; ?>:</strong> <?php echo Text::parse($page->author)->to_encoded_html; ?></p>
    <h3 class="plugin-title"><?php echo $page->title; if(isset($page->version)) echo ' ' . $page->version; ?></h3>
    <div class="plugin-description"><?php echo $page->content; ?></div>
    </div>
  </div>
</div>