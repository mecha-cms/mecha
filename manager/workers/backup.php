<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><?php echo UI::icon('file-archive-o', 'fw') . ' ' . $speak->backup; ?></a>
  <a class="tab" href="#tab-content-2"><?php echo UI::icon('recycle', 'fw') . ' ' . $speak->restore; ?></a>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <div class="tab-content" id="tab-content-1">
    <h3><?php echo $speak->backup; ?></h3>
    <table class="table-bordered table-full-width">
      <tbody>
        <?php

        $origins = array(
            UI::icon('database', 'fw') . ' ' . $speak->site => 'root',
            UI::icon('file-text', 'fw') . ' ' . $speak->article => basename(ARTICLE),
            UI::icon('file', 'fw') . ' ' . $speak->page => basename(PAGE),
            UI::icon('leaf', 'fw') . ' ' . $speak->manager->title_custom_css_and_js => basename(CUSTOM),
            UI::icon('comments', 'fw') . ' ' . $speak->comment => basename(RESPONSE),
            UI::icon('cogs', 'fw') . ' ' . $speak->config => basename(STATE),
            UI::icon('briefcase', 'fw') . ' ' . $speak->asset => basename(ASSET),
            UI::icon('shield', 'fw') . ' ' . $speak->shield => basename(SHIELD),
            UI::icon('plug', 'fw') . ' ' . $speak->plugin => basename(PLUGIN)
        );

        ?>
        <?php foreach($origins as $title => $origin): ?>
        <tr>
          <td><?php echo $title; ?></td>
          <td class="td-icon">
          <?php echo Cell::a($config->url_current . '/origin:' . $origin, UI::icon('download'), array(
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
    <?php echo Config::speak('file:restore'); ?>
    <?php

    $destinations = array(
        UI::icon('file-text') . ' ' . $speak->article => ARTICLE,
        UI::icon('file') . ' ' . $speak->page => PAGE,
        UI::icon('leaf') . ' ' . $speak->manager->title_custom_css_and_js => CUSTOM,
        UI::icon('comments') . ' ' . $speak->comment => RESPONSE,
        UI::icon('cogs') . ' ' . $speak->config => STATE,
        UI::icon('briefcase') . ' ' . $speak->asset => ASSET,
        UI::icon('shield') . ' ' . $speak->shield => SHIELD,
        UI::icon('plug') . ' ' . $speak->plugin => PLUGIN
    );

    ?>
    <?php foreach($destinations as $title => $destination): ?>
    <div class="media no-capture">
      <h4 class="media-title"><?php echo $title; ?></h4>
      <div class="media-content">
        <p><code><?php echo $destination; ?></code></p>
        <?php echo UI::uploader($config->url_current, 'zip', array(
            'destination' => $destination,
            'title' => strip_tags($title)
        )); ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>