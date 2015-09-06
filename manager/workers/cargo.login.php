<?php $form_id = time(); echo $messages; ?>
<form class="form-login" id="form-login:<?php echo $form_id; ?>" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->username; ?></span>
    <span class="grid span-4">
    <?php echo Form::text('user', Guardian::wayback('user'), null, array(
        'autocomplete' => 'off'
    )); ?>
    </span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->password; ?></span>
    <span class="grid span-4">
    <?php echo Form::password('pass', null, null, array(
        'autocomplete' => 'off'
    )); ?>
    </span>
  </label>
  <?php echo Form::hidden('kick', Request::get('kick', Guardian::wayback('url_origin', $config->manager->slug . '/article'))); ?>
  <div class="grid-group">
    <span class="grid span-2"></span>
    <span class="grid span-4">
    <?php echo Form::button(Cell::i("", array(
        'class' => array(
            'fa',
            'fa-key'
        )
    )) . ' ' . $speak->login, null, null, null, array(
        'class' => array(
            'btn',
            'btn-action'
        )
    )); ?>
    </span>
  </div>
</form>
<script>document.getElementById('form-login:<?php echo $form_id; ?>').username.focus();</script>