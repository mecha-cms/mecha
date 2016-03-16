<form class="comment-form" id="comment-form" action="<?php echo $article->url . str_replace('&', '&amp;', $config->url_query); ?>" method="post">
  <?php $hooks = array($article); echo $messages; ?>
  <?php echo Form::hidden('token', $token); ?>
  <?php Weapon::fire('comment_form_input_before', $hooks); ?>
  <?php Shield::chunk('comment.form.name'); ?>
  <?php Shield::chunk('comment.form.email'); ?>
  <?php Shield::chunk('comment.form.url'); ?>
  <?php echo Form::hidden('parent', Guardian::wayback('parent')); ?>
  <?php Weapon::fire('comment_form_input_after', $hooks); ?>
  <?php Weapon::fire('comment_form_textarea_before', $hooks); ?>
  <?php Shield::chunk('comment.form.message'); ?>
  <?php Weapon::fire('comment_form_textarea_after', $hooks); ?>
  <?php Shield::chunk('comment.form.math'); ?>
  <div class="grid-group">
    <span class="grid span-1"></span>
    <div class="grid span-5">
      <p>
        <?php Weapon::fire('comment_form_button_before', $hooks); ?>
        <?php echo Form::button($speak->publish, null, 'submit', null, array('class' => array('btn', 'btn-construct'))); ?>
        <?php Weapon::fire('comment_form_button_after', $hooks); ?>
      </p>
      <?php if(strpos($speak->comment_wizard, '</p>') === false): ?>
      <p><?php echo $speak->comment_wizard; ?></p>
      <?php else: ?>
      <?php echo $speak->comment_wizard; ?>
      <?php endif; ?>
    </div>
  </div>
</form>