<?php $segment = $segment[0]; $hooks = array($page, $segment); ?>
<div class="tab-button-area">
  <?php Weapon::fire('tab_button_before', $hooks); ?>
  <a class="tab-button active" href="#tab-content-1"><?php echo Jot::icon('pencil', 'fw') . ' ' . $speak->edit; ?></a>
  <a class="tab-button" href="#tab-content-2"><?php echo Jot::icon('th-list', 'fw') . ' ' . $speak->fields; ?></a>
  <?php Weapon::fire('tab_button_after', $hooks); ?>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <form class="form-repair form-<?php echo $segment; ?>" id="form-repair" action="<?php echo $config->url_current; ?>" method="post" enctype="multipart/form-data">
    <?php echo Form::hidden('token', $token); ?>
    <?php Weapon::fire('tab_content_before', $hooks); ?>
    <div class="tab-content" id="tab-content-1">
      <?php Weapon::fire('unit_composer_1_before', $hooks); ?>
      <?php if(isset($page->ip)): ?>
      <div class="grid-group">
        <span class="grid span-1 form-label"><abbr title="Internet Protocol">IP</abbr></span>
        <span class="grid span-5 form-static"><strong><?php echo $page->ip; ?></strong></span>
      </div>
      <?php endif; ?>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->{$segment . '_name'}; ?></span>
        <span class="grid span-5">
        <?php echo Form::text('name', Guardian::wayback('name', $page->name_raw), null, array(
            'class' => 'input-block'
        )); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->{$segment . '_email'}; ?></span>
        <span class="grid span-5">
        <?php echo Form::text('email', Guardian::wayback('email', $page->email), null, array(
            'class' => 'input-block'
        )); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->{$segment . '_url'}; ?></span>
        <span class="grid span-5">
        <?php echo Form::text('url', Guardian::wayback('url', $page->url_raw), null, array(
            'class' => 'input-block'
        )); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->{$segment . '_status'}; ?></span>
        <span class="grid span-5">
        <?php echo Form::select('status', array(
            1 => $speak->pilot,
            2 => $speak->passenger,
            0 => $speak->intruder
        ), Guardian::wayback('status', $page->status_raw)); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->{$segment . '_message'}; ?></span>
        <span class="grid span-5">
        <?php echo Form::textarea('message', Guardian::wayback('message', $page->message_raw), null, array(
            'class' => array(
                'textarea-block',
                'textarea-expand',
                'MTE',
                'code'
            ),
            'data-MTE-config' => '{"toolbar":true,"shortcut":true}'
        )); ?>
        </span>
      </label>
      <div class="grid-group">
        <span class="grid span-1 form-label"></span>
        <span class="grid span-5"><?php echo Form::checkbox('content_type', $config->html_parser !== false ? $config->html_parser : 'HTML', Guardian::wayback('content_type', $page->content_type_raw) !== (strpos($config->url_path, '/id:') === false ? false : 'HTML'), $speak->manager->title_html_parser); ?></span>
      </div>
      <?php Weapon::fire('unit_composer_1_after', $hooks); ?>
    </div>
    <div class="tab-content hidden" id="tab-content-2">
      <?php include __DIR__ . DS . 'unit.composer.3.php'; ?>
    </div>
    <?php Weapon::fire('tab_content_after', $hooks); ?>
    <hr>
    <p>
      <?php if(Guardian::wayback('state', $page->state) === 'pending'): ?>
      <?php echo Jot::button('accept', $speak->approve, 'action:publish'); ?>
      <?php echo Jot::button('action:clock-o', $speak->update, 'action:save'); ?>
      <?php else: ?>
      <?php echo Jot::button('action', $speak->update, 'action:publish'); ?>
      <?php echo Jot::button('action:history', $speak->unapprove, 'action:save'); ?>
      <?php endif; ?>
      <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/' . $segment . '/kill/id:' . Guardian::wayback('id', $page->id)); ?>
    </p>
  </form>
</div>