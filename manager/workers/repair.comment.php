<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><?php echo Jot::icon('pencil', 'fw') . ' ' . $speak->edit; ?></a>
  <a class="tab" href="#tab-content-3"><?php echo Jot::icon('th-list', 'fw') . ' ' . $speak->fields; ?></a>
  <a class="tab ajax-post" href="#tab-content-2" data-action-url="<?php echo $config->url . '/' . $config->manager->slug . '/ajax/preview:comment'; ?>" data-text-progress="<?php echo $speak->previewing; ?>&hellip;" data-text-error="<?php echo $speak->error; ?>." data-scope="#form-repair" data-target="#form-repair-preview"><?php echo Jot::icon('eye', 'fw') . ' ' . $speak->preview; ?></a>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <form class="form-repair form-comment" id="form-repair" action="<?php echo $config->url_current; ?>" method="post" enctype="multipart/form-data">
    <?php echo Form::hidden('token', $token); ?>
    <div class="tab-content" id="tab-content-1">
      <?php Weapon::fire('unit_composer_1_before', array($segment)); ?>
      <?php if(isset($default->ip)): ?>
      <div class="grid-group">
        <span class="grid span-1 form-label">IP</span>
        <span class="grid span-5 form-static"><strong><?php echo $default->ip; ?></strong></span>
      </div>
      <?php endif; ?>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->comment_name; ?></span>
        <span class="grid span-5">
        <?php echo Form::text('name', Guardian::wayback('name', $default->name), null, array(
            'class' => 'input-block'
        )); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->comment_email; ?></span>
        <span class="grid span-5">
        <?php echo Form::text('email', Guardian::wayback('email', $default->email), null, array(
            'class' => 'input-block'
        )); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->comment_url; ?></span>
        <span class="grid span-5">
        <?php echo Form::text('url', Guardian::wayback('url', $default->url), null, array(
            'class' => 'input-block'
        )); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->comment_status; ?></span>
        <span class="grid span-5">
        <?php echo Form::select('status', array(
            'pilot' => $speak->pilot,
            'passenger' => $speak->passenger,
            'intruder' => $speak->intruder
        ), Guardian::wayback('status', $default->status)); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->comment_message; ?></span>
        <span class="grid span-5">
        <?php echo Form::textarea('message', Guardian::wayback('message', $default->message_raw), null, array(
            'class' => array(
                'textarea-block',
                'textarea-expand',
                'code',
                'MTE'
            ),
            'data-MTE-config' => '{"toolbar":true,"shortcut":true}'
        )); ?>
        </span>
      </label>
      <div class="grid-group">
        <span class="grid span-1 form-label"></span>
        <span class="grid span-5"><?php echo Form::checkbox('content_type', HTML_PARSER, Guardian::wayback('content_type', $default->content_type) === HTML_PARSER, $speak->manager->title_html_parser); ?></span>
      </div>
      <?php Weapon::fire('unit_composer_1_after', array($segment)); ?>
    </div>
    <div class="tab-content hidden" id="tab-content-2">
      <?php Weapon::fire('unit_composer_2_before', array($segment)); ?>
      <div id="form-repair-preview"></div>
      <?php Weapon::fire('unit_composer_2_after', array($segment)); ?>
    </div>
    <div class="tab-content hidden" id="tab-content-3">
      <?php include 'unit.composer.3.php'; ?>
    </div>
    <hr>
    <p>
      <?php if(Guardian::wayback('state', $default->state) === 'pending'): ?>
      <?php echo Jot::button('accept', $speak->approve, 'action:publish'); ?>
      <?php echo Jot::button('action:clock-o', $speak->update, 'action:save'); ?>
      <?php else: ?>
      <?php echo Jot::button('action', $speak->update, 'action:publish'); ?>
      <?php echo Jot::button('action:history', $speak->unapprove, 'action:save'); ?>
      <?php endif; ?>
      <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/comment/kill/id:' . Guardian::wayback('id', $default->id)); ?>
    </p>
  </form>
</div>