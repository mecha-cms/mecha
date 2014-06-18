<?php echo Notify::read(); ?>
<form class="form-kill form-field" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <table class="table-bordered table-full">
    <thead>
      <tr>
        <th><?php echo $speak->title; ?></th>
        <th><?php echo $speak->key; ?></th>
        <th><?php echo $speak->type; ?></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?php echo $page->title; ?></td>
        <td><?php echo $config->key; ?></td>
        <td><?php echo $page->type; ?></td>
      </tr>
    </tbody>
  </table>
  <p><button class="btn btn-primary btn-delete" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->yes; ?></button> <a href="<?php echo $config->url . '/' . $config->manager->slug; ?>/field/repair/key:<?php echo $config->key; ?>" class="btn btn-danger btn-cancel"><i class="fa fa-times-circle"></i> <?php echo $speak->no; ?></a></p>
</form>