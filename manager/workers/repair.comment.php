<?php echo Notify::read(); ?>
<form class="form-repair form-comment" action="<?php echo $config->url_current; ?>" method="post">
  <?php $cache = Guardian::wayback(); ?>
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->comment_name; ?></span>
    <span class="grid span-5"><input name="name" type="text" class="input-block" value="<?php echo $cache['name']; ?>"></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->comment_email; ?></span>
    <span class="grid span-5"><input name="email" type="text" class="input-block" value="<?php echo $cache['email']; ?>"></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->comment_url; ?></span>
    <span class="grid span-5"><input name="url" type="text" class="input-block" value="<?php echo $cache['url']; ?>"></span>
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
    <span class="grid span-5"><textarea name="message" class="textarea-block code" data-mte-languages='<?php echo Text::parse($speak->MTE)->to_encoded_json; ?>'><?php echo Text::parse($cache['message_raw'])->to_encoded_html; ?></textarea></span>
  </label>
  <div class="grid-group">
    <span class="grid span-1"></span>
    <span class="grid span-5">
      <?php if($cache['state'] == 'pending'): ?><button class="btn btn-accept" name="action" type="submit" value="publish"><i class="fa fa-check-circle"></i> <?php echo $speak->approve; ?></button><?php else: ?><button class="btn btn-action" name="action" type="submit" value="publish"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button> <button class="btn btn-action" name="action" type="submit" value="save"><i class="fa fa-history"></i> <?php echo $speak->unpublish; ?></button><?php endif; ?> <a class="btn btn-destruct" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/comment/kill/id:<?php echo $cache['id']; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a>
    </span>
  </div>
</form>