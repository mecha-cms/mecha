<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><?php echo Jot::icon('file-archive-o', 'fw') . ' ' . $speak->backup; ?></a>
  <a class="tab" href="#tab-content-2"><?php echo Jot::icon('recycle', 'fw') . ' ' . $speak->restore; ?></a>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <div class="tab-content" id="tab-content-1">
    <h3><?php echo $speak->backup; ?></h3>
    <table class="table-bordered table-full-width">
      <tbody>
        <?php

        $origins = array(
            Jot::icon('database', 'fw') . ' ' . $speak->site => 'ROOT',
            Jot::icon('file-text', 'fw') . ' ' . $speak->article => basename(ARTICLE),
            Jot::icon('file', 'fw') . ' ' . $speak->page => basename(PAGE),
            Jot::icon('leaf', 'fw') . ' ' . $speak->manager->title_custom_css_and_js => basename(CUSTOM),
            Jot::icon('comments', 'fw') . ' ' . $speak->comment => basename(RESPONSE),
            Jot::icon('cogs', 'fw') . ' ' . $speak->config => basename(STATE),
            Jot::icon('briefcase', 'fw') . ' ' . $speak->asset => basename(ASSET),
            Jot::icon('shield', 'fw') . ' ' . $speak->shield => basename(SHIELD),
            Jot::icon('plug', 'fw') . ' ' . $speak->plugin => basename(PLUGIN)
        );

        ?>
        <?php foreach($origins as $title => $origin): ?>
        <tr>
          <td><?php echo $title; ?></td>
          <td class="td-icon">
          <?php echo Cell::a($config->url_current . '/origin:' . $origin, Jot::icon('download'), null, array(
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
        Jot::icon('file-text') . ' ' . $speak->article => ARTICLE,
        Jot::icon('file') . ' ' . $speak->page => PAGE,
        Jot::icon('leaf') . ' ' . $speak->manager->title_custom_css_and_js => CUSTOM,
        Jot::icon('comments') . ' ' . $speak->comment => RESPONSE,
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
</div>