<?php $hooks = array($page, $segment); ?>
<div class="tab-area">
  <div class="tab-button-area">
    <?php Weapon::fire('tab_button_before', $hooks); ?>
    <a class="tab-button active" href="#tab-content-1"><?php echo Jot::icon('file-archive-o', 'fw') . ' ' . $speak->backup; ?></a>
    <a class="tab-button" href="#tab-content-2"><?php echo Jot::icon('recycle', 'fw') . ' ' . $speak->restore; ?></a>
    <?php Weapon::fire('tab_button_after', $hooks); ?>
  </div>
  <div class="tab-content-area">
    <?php echo $messages; ?>
    <?php Weapon::fire('tab_content_before', $hooks); ?>
    <div class="tab-content" id="tab-content-1">
      <h3><?php echo $speak->backup; ?></h3>
      <table class="table-bordered table-full-width">
        <tbody>
          <?php

          $origins = array(
              Jot::icon('database', 'fw') . ' ' . $speak->site => '.',
              Jot::icon('file-text', 'fw') . ' ' . $speak->article => str_replace(CARGO . DS, "", ARTICLE),
              Jot::icon('file', 'fw') . ' ' . $speak->page => str_replace(CARGO . DS, "", PAGE),
              Jot::icon('magnet', 'fw') . ' ' . $speak->extend => str_replace(CARGO . DS, "", EXTEND),
              Jot::icon('comments', 'fw') . ' ' . $speak->comment => str_replace(CARGO . DS, "", COMMENT),
              Jot::icon('cogs', 'fw') . ' ' . $speak->config => str_replace(CARGO . DS, "", STATE),
              Jot::icon('briefcase', 'fw') . ' ' . $speak->asset => str_replace(CARGO . DS, "", ASSET),
              Jot::icon('shield', 'fw') . ' ' . $speak->shield => str_replace(CARGO . DS, "", SHIELD),
              Jot::icon('plug', 'fw') . ' ' . $speak->plugin => str_replace(CARGO . DS, "", PLUGIN)
          );

          ?>
          <?php foreach($origins as $title => $origin): ?>
          <tr>
            <td><?php echo $title; ?></td>
            <td class="td-icon">
            <?php echo Cell::a($config->url_current . '/origin:' . File::url($origin), Jot::icon('download'), null, array(
                'title' => $speak->download
            )); ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="tab-content hidden" id="tab-content-2">
      <h3><?php echo $speak->restore; ?></h3>
      <?php
  
      $destinations = array(
          Jot::icon('file-text') . ' ' . $speak->article => ARTICLE,
          Jot::icon('file') . ' ' . $speak->page => PAGE,
          Jot::icon('magnet') . ' ' . $speak->extend => EXTEND,
          Jot::icon('comments') . ' ' . $speak->comment => COMMENT,
          Jot::icon('cogs') . ' ' . $speak->config => STATE,
          Jot::icon('briefcase') . ' ' . $speak->asset => ASSET,
          Jot::icon('shield') . ' ' . $speak->shield => SHIELD,
          Jot::icon('plug') . ' ' . $speak->plugin => PLUGIN
      );
  
      ?>
      <?php foreach($destinations as $title => $destination): ?>
      <div class="media no-capture">
        <h4 class="media-title"><?php echo $title; ?></h4>
        <div class="media-content">
          <p><code><?php echo $destination; ?></code></p>
          <?php echo Jot::uploader($config->manager->slug . '/backup', 'zip', array(
              'destination' => $destination,
              'title' => trim(strip_tags($title))
          )); ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php Weapon::fire('tab_content_after', $hooks); ?>
  </div>
</div>