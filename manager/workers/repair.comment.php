<?php echo Notify::read(); ?>
<form class="form-comment" action="<?php echo $config->url_current; ?>" method="post">
  <?php $cache = Guardian::wayback(); ?>
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->comment_name; ?></span>
    <span class="grid span-5"><input name="name" type="text" class="input-block" value="<?php echo $cache['name']; ?>"></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->comment_email; ?></span>
    <span class="grid span-5"><input name="email" type="email" class="input-block" value="<?php echo $cache['email']; ?>"></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->comment_url; ?></span>
    <span class="grid span-5"><input name="url" type="url" class="input-block" value="<?php echo $cache['url']; ?>"></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->comment_status; ?></span>
    <span class="grid span-5">
      <select name="status">
        <?php

        foreach(array('pilot' => $speak->pilot, 'passenger' => $speak->passenger, 'intruder' => $speak->intruder) as $key => $value) {
            echo '<option value="' . $key . '"' . ($cache['status'] == $key ? ' selected' : "") . '>' . $value . '</option>';
        }

        ?>
      </select>
    </span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->comment_message; ?></span>
    <span class="grid span-5"><textarea name="message" class="input-block"><?php echo $cache['message_raw']; ?></textarea></span>
  </label>
  <div class="grid-group">
    <span class="grid span-1">&nbsp;</span>
    <span class="grid span-5"><button class="btn btn-primary btn-update" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button> <a class="btn btn-danger btn-delete" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/comment/kill/<?php echo $cache['id']; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a></span>
</form>