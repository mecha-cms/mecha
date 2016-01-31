<?php $hooks = array($page, $segment); ?>
<div class="tab-area">
  <div class="tab-button-area">
    <?php Weapon::fire('tab_button_before', $hooks); ?>
    <a class="tab-button active" href="#tab-content-1"><?php echo Jot::icon('cog', 'fw') . ' ' . $speak->config; ?></a>
    <a class="tab-button" href="#tab-content-2"><?php echo Jot::icon('user', 'fw') . ' ' . $speak->about; ?></a>
    <?php Weapon::fire('tab_button_after', $hooks); ?>
  </div>
  <div class="tab-content-area">
    <?php echo $messages; ?>
    <?php Weapon::fire('tab_content_before', $hooks); ?>
    <div class="tab-content" id="tab-content-1">
    <?php

    if($page->configurator) {
        $test = file_get_contents($page->configurator);
        if(strpos($test, '</form>') === false) { // allow plugin configurator without `<form>` tag
            echo '<form class="form-plugin" action="' . $config->url_current . '/update' . str_replace('&', '&amp;', $config->url_query) . '" method="post">';
            echo Form::hidden('token', $token);
            include $page->configurator;
            if(strpos($test, 'Jot::button(\'action\', $speak->update)') === false && strpos($test, 'Jot::button("action", $speak->update)') === false) {
                echo '<div class="grid-group">';
                echo '<span class="grid span-1"></span>';
                echo '<span class="grid span-5">';
                Weapon::fire('action_before', $hooks);
                echo Jot::button('action', $speak->update);
                Weapon::fire('action_after', $hooks);
                echo '</span>';
                echo '</div>';
            }
            echo '</form>';
        } else {
            include $page->configurator;
        }
    } else {
        echo Cell::p(Config::speak('notify_not_available', $speak->config));
    }

    ?>
    </div>
    <div class="tab-content hidden" id="tab-content-2">
      <p class="about-author">
      <?php echo Cell::strong($speak->author . ':') . ' ' . $page->author; ?><?php if(isset($page->url) && $page->url !== '#'): ?> <?php echo Cell::a($page->url, Jot::icon('external-link-square'), true, array(
          'class' => array(
              'about-url',
              'help'
          ),
          'title' => $speak->link,
          'rel' => 'nofollow'
      )); ?>
      <?php endif; ?>
      </p>
      <h3 class="about-title"><?php echo $page->title; ?><?php if(isset($page->version)): ?> <code class="about-version"><?php echo $page->version; ?></code><?php endif; ?></h3>
      <div class="about-content"><?php echo $page->content; ?></div>
    </div>
    <?php Weapon::fire('tab_content_after', $hooks); ?>
  </div>
</div>