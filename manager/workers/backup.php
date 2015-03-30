<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><i class="fa fa-fw fa-file-archive-o"></i> <?php echo $speak->backup; ?></a>
  <a class="tab" href="#tab-content-2"><i class="fa fa-fw fa-recycle"></i> <?php echo $speak->restore; ?></a>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <div class="tab-content" id="tab-content-1">
    <h3><?php echo $speak->backup; ?></h3>
    <table class="table-bordered table-full-width">
      <tbody>
        <?php

        $origins = array(
            '<i class="fa fa-fw fa-database"></i> ' . $speak->site => 'root',
            '<i class="fa fa-fw fa-file-text"></i> ' . $speak->article => basename(ARTICLE),
            '<i class="fa fa-fw fa-file"></i> ' . $speak->page => basename(PAGE),
            '<i class="fa fa-fw fa-leaf"></i> ' . $speak->manager->title_custom_css_and_js => basename(CUSTOM),
            '<i class="fa fa-fw fa-comments"></i> ' . $speak->comment => basename(RESPONSE),
            '<i class="fa fa-fw fa-cogs"></i> ' . $speak->config => basename(STATE),
            '<i class="fa fa-fw fa-briefcase"></i> ' . $speak->asset => basename(ASSET),
            '<i class="fa fa-fw fa-shield"></i> ' . $speak->shield => basename(SHIELD),
            '<i class="fa fa-fw fa-plug"></i> ' . $speak->plugin => basename(PLUGIN)
        );

        ?>
        <?php foreach($origins as $title => $origin): ?>
        <tr>
          <td><?php echo $title; ?></td>
          <td class="td-icon"><a href="<?php echo $config->url_current; ?>/origin:<?php echo $origin; ?>" title="<?php echo $speak->download; ?>"><i class="fa fa-download"></i></a></td>
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
        '<i class="fa fa-file-text"></i> ' . $speak->article => ARTICLE,
        '<i class="fa fa-file"></i> ' . $speak->page => PAGE,
        '<i class="fa fa-leaf"></i> ' . $speak->manager->title_custom_css_and_js => CUSTOM,
        '<i class="fa fa-comments"></i> ' . $speak->comment => RESPONSE,
        '<i class="fa fa-cogs"></i> ' . $speak->config => STATE,
        '<i class="fa fa-briefcase"></i> ' . $speak->asset => ASSET,
        '<i class="fa fa-shield"></i> ' . $speak->shield => SHIELD,
        '<i class="fa fa-plug"></i> ' . $speak->plugin => PLUGIN
    );

    ?>
    <?php foreach($destinations as $title => $destination): ?>
    <div class="media no-capture">
      <h4 class="media-title"><?php echo $title; ?></h4>
      <div class="media-content">
        <p><code><?php echo $destination; ?></code></p>
        <form class="form-upload" action="<?php echo $config->url_current; ?>" method="post" enctype="multipart/form-data">
          <input name="token" type="hidden" value="<?php echo $token; ?>">
          <input name="destination" type="hidden" value="<?php echo $destination; ?>">
          <input name="title" type="hidden" value="<?php echo strip_tags($title); ?>">
          <span class="input-outer btn btn-default">
            <span><i class="fa fa-folder-open"></i> <?php echo $speak->manager->placeholder_file; ?></span>
            <input type="file" name="file" title="<?php echo $speak->manager->placeholder_file; ?>" data-icon-ready="fa fa-check" data-icon-error="fa fa-times" data-accepted-extensions="zip">
          </span> <button class="btn btn-action" type="submit"><i class="fa fa-cloud-upload"></i> <?php echo $speak->upload; ?></button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>