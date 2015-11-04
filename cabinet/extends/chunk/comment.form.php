<form class="comment-form" id="comment-form" action="<?php echo $article->url; ?>" method="post">
  <?php echo $messages; ?>
  <input name="token" type="hidden" value="<?php echo $token; ?>">
  <input name="parent" type="hidden" value="">
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->comment_name; ?></span>
    <span class="grid span-5"><input name="name" type="text" class="input-block" value="<?php echo Guardian::wayback('name'); ?>"></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->comment_email; ?></span>
    <span class="grid span-5"><input name="email" type="email" class="input-block" value="<?php echo Guardian::wayback('email'); ?>"></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->comment_url; ?></span>
    <span class="grid span-5"><input name="url" type="url" class="input-block" value="<?php echo Guardian::wayback('url'); ?>"></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->comment_message; ?></span>
    <span class="grid span-5"><textarea name="message" class="textarea-block"><?php echo Guardian::wayback('message'); ?></textarea></span>
  </label>
  <?php Weapon::fire('comment_form_input', array($article)); ?>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo Guardian::math(); ?> =</span>
    <span class="grid span-5"><input name="math" type="text" value="" autocomplete="off"></span>
  </label>
  <div class="grid-group">
    <span class="grid span-1"></span>
    <div class="grid span-5">
      <p><button class="btn btn-construct" type="submit"><?php echo $speak->publish; ?></button></p>
      <p><?php echo $speak->comment_guide; ?></p>
    </div>
  </div>
</form>