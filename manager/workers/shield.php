<div class="tab-area cf">
  <a class="tab active" href="#tab-content-1"><i class="fa fa-fw fa-file-archive-o"></i> <?php echo $speak->upload; ?></a>
</div>
<div class="tab-content-area">
  <?php echo Notify::read(); ?>
  <div class="tab-content" id="tab-content-1">
    <h3 class="media-headline"><?php echo $speak->manager->title_shield_upload; ?></h3>
    <form class="form-upload" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/shield" method="post" enctype="multipart/form-data">
      <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
      <div class="grid-group">
        <span class="grid span-6">
          <span class="input-wrapper btn">
            <span><i class="fa fa-folder-open"></i> <?php echo $speak->manager->placeholder_file; ?></span>
            <input type="file" name="file" title="<?php echo $speak->manager->placeholder_file; ?>" data-icon-ready="fa fa-check" data-icon-error="fa fa-times" data-accepted-extensions="zip,rar">
          </span> <button class="btn btn-primary btn-upload" type="submit"><i class="fa fa-cloud-upload"></i> <?php echo $speak->upload; ?></button>
        </span>
      </div>
    </form>
    <hr>
    <?php echo Config::speak('file:shield'); ?>
  </div>
</div>