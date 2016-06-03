<?php $hooks = array($config, $segment); echo $messages; ?>
<form class="form-repair form-config" id="form-repair" action="<?php echo $config->url_current . str_replace('&', '&amp;', $config->url_query); ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <fieldset>
    <legend><?php echo $speak->general; ?></legend>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->time_zone; ?></span>
      <span class="grid span-4">
      <?php echo Form::select('timezone', Get::timezone(), Guardian::wayback('timezone', $config->timezone), array(
          'class' => 'select-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_html_charset; ?></span>
      <span class="grid span-4">
      <?php echo Form::text('charset', Guardian::wayback('charset', $config->charset), null, array(
          'class' => 'input-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->language; ?></span>
      <span class="grid span-4">
      <?php

      $language = array();
      foreach(glob(LANGUAGE . DS . '*', GLOB_ONLYDIR) as $folder) {
          $s = Converter::toArray(File::open($folder . DS . 'speak.txt')->get('  title:'), S, '  ');
          $folder = File::B($folder);
          $language[$folder] = isset($s['__']['title']) ? $s['__']['title'] : $folder;
      }
      echo Form::select('language', $language, Guardian::wayback('language', $config->language), array(
          'class' => 'select-block'
      ));

      ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_language_direction; ?></span>
      <span class="grid span-4">
      <?php echo Form::select('language_direction', array(
          'ltr' => 'Left to Right (LTR)',
          'rtl' => 'Right to Left (RTL)'
      ), Guardian::wayback('language_direction', $config->language_direction), array(
          'class' => 'select-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->shield; ?></span>
      <span class="grid span-4">
      <?php

      $info = array();
      foreach(glob(SHIELD . DS . '*', GLOB_ONLYDIR) as $folder) {
          $s = File::B($folder);
          if(File::hidden($s)) continue;
          $info[$s] = Shield::info($s)->title;
      }
      echo Form::select('shield', $info, Guardian::wayback('shield', $config->shield), array(
          'class' => 'select-block'
      ));

      ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_html_parser_type; ?></span>
      <span class="grid span-4"><?php echo Form::select('html_parser[active]', Mecha::eat($config->html_parser->type)->order('ASC', null, true)->vomit(), $config->html_parser->active, array(
          'class' => 'select-block'
      )); ?></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?> (<?php echo $speak->all; ?>)</span>
      <span class="grid span-4">
      <?php echo Form::number('per_page', Guardian::wayback('per_page', $config->per_page), null, array(
          'min' => 1
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_page_excerpt_length; ?></span>
      <span class="grid span-4">
      <?php echo Form::number('excerpt[length]', Guardian::wayback('excerpt.length', $config->excerpt->length)); ?>
      </span>
    </label>
    <label class="grid-group hidden">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_page_excerpt_prefix; ?></span>
      <span class="grid span-4">
      <?php echo Form::text('excerpt[prefix]', Guardian::wayback('excerpt.prefix', $config->excerpt->prefix)); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_page_excerpt_suffix; ?></span>
      <span class="grid span-4">
      <?php echo Form::text('excerpt[suffix]', Guardian::wayback('excerpt.suffix', $config->excerpt->suffix)); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_page_excerpt_id; ?></span>
      <span class="grid span-4">
      <?php echo Form::text('excerpt[id]', Guardian::wayback('excerpt.id', $config->excerpt->id)); ?>
      </span>
    </label>
    <div class="grid-group">
      <span class="grid span-2"></span>
      <div class="grid span-4">
        <div><?php echo Form::checkbox('widget_include_css', true, Guardian::wayback('widget_include_css', $config->widget_include_css), $speak->manager->title_widget_include_css); ?></div>
        <div><?php echo Form::checkbox('widget_include_js', true, Guardian::wayback('widget_include_js', $config->widget_include_js), $speak->manager->title_widget_include_js); ?></div>
      </div>
    </div>
    <div class="grid-group">
      <span class="grid span-2"></span>
      <div class="grid span-4">
        <div><?php echo Form::checkbox('comments[allow]', true, Guardian::wayback('comments.allow', $config->comments->allow), $speak->manager->title_comment_allow); ?></div>
        <div><?php echo Form::checkbox('comments[moderation]', true, Guardian::wayback('comments.moderation', $config->comments->moderation), $speak->manager->title_comment_moderation); ?></div>
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend><?php echo $speak->site; ?></legend>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
      <span class="grid span-4">
      <?php echo Form::text('title', Guardian::wayback('title', $config->title), null, array(
          'class' => 'input-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_title_separator; ?></span>
      <span class="grid span-4">
      <?php echo Form::text('title_separator', Guardian::wayback('title_separator', $config->title_separator), null, array(
          'class' => 'input-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->slogan; ?></span>
      <span class="grid span-4">
      <?php echo Form::textarea('slogan', Guardian::wayback('slogan', $config->slogan), null, array(
          'class' => 'textarea-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->description; ?></span>
      <span class="grid span-4">
      <?php echo Form::textarea('description', Guardian::wayback('description', $config->description), null, array(
          'class' => 'textarea-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->keywords; ?></span>
      <span class="grid span-4">
      <?php echo Form::text('keywords', Guardian::wayback('keywords', $config->keywords), null, array(
          'class' => 'input-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_keyword_spam; ?></span>
      <span class="grid span-4">
      <?php echo Form::textarea('keywords_spam', Guardian::wayback('keywords_spam', $config->keywords_spam), $speak->manager->placeholder_keyword_spam, array(
          'class' => 'textarea-block'
      )); ?>
      </span>
    </label>
  </fieldset>
  <fieldset>
    <legend><?php echo $speak->manager->title_authorization; ?></legend>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->name; ?></span>
      <span class="grid span-4">
      <?php echo Form::text('author[name]', Guardian::wayback('author.name', $config->author->name), null, array(
          'class' => 'input-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->email; ?></span>
      <span class="grid span-4">
      <?php echo Form::text('author[email]', Guardian::wayback('author.email', $config->author->email), null, array(
          'class' => 'input-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->url; ?></span>
      <span class="grid span-4">
      <?php echo Form::text('author[url]', Guardian::wayback('author.url', $config->author->url), null, array(
          'class' => 'input-block'
      )); ?>
      </span>
    </label>
  </fieldset>
  <fieldset>
    <legend><?php echo $speak->page; ?></legend>
    <?php foreach(array('index', 'tag', 'archive', 'search', 'manager') as $page): ?>
    <fieldset>
      <legend><?php echo Config::speak('manager.title_page_' . $page); ?></legend>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-4">
        <?php

        $t = Config::get($page . '.title');
        $t = $t === Config::speak('manager.title_' . $page) ? "" : $t;

        ?>
        <?php echo Form::text($page . '[title]', Guardian::wayback($page . '.title', $t), null, array('readonly' => $page === 'manager' ? true : null)); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-4">
        <?php echo Form::text($page . '[slug]', Guardian::wayback($page . '.slug', Config::get($page . '.slug'))); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?></span>
        <span class="grid span-4">
        <?php echo Form::number($page . '[per_page]', Guardian::wayback($page . '.per_page', Config::get($page . '.per_page')), null, array(
            'min' => 1
        )); ?>
        </span>
      </label>
    </fieldset>
    <?php endforeach; ?>
  </fieldset>
  <fieldset>
    <legend><?php echo $speak->default; ?></legend>
    <?php $pages = Mecha::walk(glob(POST . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR), function($v) {
        return File::B($v);
    }); ?>
    <?php foreach($pages as $page): ?>
    <fieldset>
      <legend><?php $title = Config::speak($page); echo $title; ?></legend>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-4">
        <?php echo Form::text('defaults[' . $page . '_title]', Guardian::wayback('defaults.' . $page . '_title', Config::get('defaults.' . $page . '_title', "")), null, array(
            'class' => 'input-block'
        )); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->content; ?></span>
        <span class="grid span-4">
        <?php echo Form::textarea('defaults[' . $page . '_content]', Guardian::wayback('defaults.' . $page . '_content', Config::get('defaults.' . $page . '_content', "")), null, array(
            'class' => array(
                'textarea-block',
                'code'
            )
        )); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->manager->title_css_custom; ?></span>
        <span class="grid span-4">
        <?php echo Form::textarea('defaults[' . $page . '_css]', Guardian::wayback('defaults.' . $page . '_css', Config::get('defaults.' . $page . '_css', "")), null, array(
            'class' => array(
                'textarea-block',
                'code'
            )
        )); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->manager->title_js_custom; ?></span>
        <span class="grid span-4">
        <?php echo Form::textarea('defaults[' . $page . '_js]', Guardian::wayback('defaults.' . $page . '_js', Config::get('defaults.' . $page . '_js', "")), null, array(
            'class' => array(
                'textarea-block',
                'code'
            )
        )); ?>
        </span>
      </label>
    </fieldset>
    <?php endforeach; ?>
  </fieldset>
  <p>
    <?php Weapon::fire('action_before', $hooks); ?>
    <?php echo Jot::button('action', $speak->update); ?>
    <?php Weapon::fire('action_after', $hooks); ?>
  </p>
</form>