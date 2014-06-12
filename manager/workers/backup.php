<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><i class="fa fa-fw fa-file-archive-o"></i> <?php echo $speak->backup; ?></a>
  <a class="tab" href="#tab-content-2"><i class="fa fa-fw fa-recycle"></i> <?php echo $speak->restore; ?></a>
</div>
<div class="tab-content-area">
  <?php echo Notify::read(); ?>
  <div class="tab-content" id="tab-content-1">
    <h3 class="media-head"><?php echo $speak->backup; ?></h3>
    <table class="table-bordered table-full">
      <colgroup>
        <col>
        <col style="width:8em;">
      </colgroup>
      <tbody>
        <?php

        $origins = array(
            $speak->manager->title_whole_site => 'root',
            $speak->articles => basename(ARTICLE),
            $speak->pages => basename(PAGE),
            $speak->manager->title_custom_css_and_js => basename(CUSTOM),
            $speak->comments => basename(RESPONSE),
            $speak->configs => basename(STATE),
            $speak->assets => basename(ASSET),
            $speak->shields => basename(SHIELD),
            $speak->plugins => basename(PLUGIN)
        );

        ?>
        <?php foreach($origins as $title => $origin): ?>
        <tr>
          <th><?php echo $title; ?></th>
          <td><a href="<?php echo $config->url_current; ?>/origin:<?php echo $origin; ?>"><i class="fa fa-download"></i> <?php echo $speak->download; ?></a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <h3 class="media-head"><?php echo $speak->restore; ?></h3>
    <?php echo Config::speak('file:restore'); ?>
    <?php

    $destinations = array(
        $speak->articles => ARTICLE,
        $speak->pages => PAGE,
        $speak->manager->title_custom_css_and_js => CUSTOM,
        $speak->comments => RESPONSE,
        $speak->configs => STATE,
        $speak->assets => ASSET,
        $speak->shields => SHIELD,
        $speak->plugins => PLUGIN
    );

    ?>
    <?php foreach($destinations as $title => $destination): ?>
    <div class="media-item">
      <h4><?php echo $title; ?></h4>
      <p><code><?php echo $destination; ?></code></p>
      <form class="form-upload" action="<?php echo $config->url_current; ?>" method="post" enctype="multipart/form-data">
        <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
        <input name="destination" type="hidden" value="<?php echo $destination; ?>">
        <input name="title" type="hidden" value="<?php echo $title; ?>">
        <span class="input-wrapper btn">
          <span><i class="fa fa-folder-open"></i> <?php echo $speak->manager->placeholder_file; ?></span>
          <input type="file" name="file" title="<?php echo $speak->manager->placeholder_file; ?>" data-icon-ready="fa fa-check" data-icon-error="fa fa-times" data-accepted-extensions="zip">
        </span> <button class="btn btn-primary btn-upload" type="submit"><i class="fa fa-cloud-upload"></i> <?php echo $speak->upload; ?></button>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
</div>