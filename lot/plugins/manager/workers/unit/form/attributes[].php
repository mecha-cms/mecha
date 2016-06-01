<div class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->format; ?></span>
  <span class="grid span-5">
  <?php

  $hints = array(
      '\\d+',
      '\\w+',
      '[a-z0-9\\-]+',
      '[a-z_][a-z0-9_]*',
      'rgba?\\((?:\\s*\\d{1,3}\\s*,){3}\\s*,\\s*(?:(?:\\d*\\.)?\\d+?)\\)',
      '#(?:[A-Fa-f0-9]{3}){1,2}',
      '&(?:[A-Za-z0-9]+|#[0-9]+|#x[a-f0-9]+);'
  );

  ?>
  <?php echo Form::text('attributes[pattern]', Converter::EW(Request::get('attributes.pattern', Guardian::wayback('attributes.pattern', $page->attributes->pattern))), Mecha::eat($hints)->shake()->vomit(0), array(
      'class' => array(
          'input-block',
          'code'
      )
  )); ?>
  </span>
</div>
<div class="grid-group">
  <span class="grid span-1"></span>
  <div class="grid span-5">
    <div><?php echo Form::checkbox('attributes[required]', true, Request::get('attributes.required', Guardian::wayback('attributes.required', isset($page->attributes->required))), $speak->required); ?></div>
  </div>
</div>