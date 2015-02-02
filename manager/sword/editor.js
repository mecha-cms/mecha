/**
 * Article/Page Editor
 * -------------------
 */

(function($, base) {

    var $base = $(document.body),
        $editor = $('.form-compose').first(),
        $tab = $('.tab-area a'),
        $title = $('[name="title"]', $editor),
        $slug = $('[name="slug"]', $editor),
        $content = $('[name="content"]', $editor),
        $check = $('[type="checkbox"]', $editor),
        $css = $('[name="css"]', $editor),
        $js = $('[name="js"]', $editor),
        $css_check = $('[name="css_live_check"]', $editor),
        $js_check = $('[name="js_live_check"]', $editor);

    var $css_preview = $('<div id="live-preview-css"></div>').appendTo($base),
        $js_preview = $('<div id="live-preview-js"></div>').appendTo($base);

    var languages = $content.data('mteLanguages'), FT = $content.data('ft');

    base.add('on_ajax_success', function(data) {
        base.fire('on_preview_complete', data);
    });

    base.add('on_ajax_error', function(data) {
        base.fire('on_preview_failure', data);
    });

    if ($content.length && typeof MTE != "undefined") {
        base.fire('on_control_begin', [FT, 'content']);
        base.composer = new MTE($content[0], {
            tabSize: base.tab_size,
            shortcut: true,
            toolbarClass: 'editor-toolbar cf',
            buttonClassPrefix: 'editor-toolbar-button editor-toolbar-button-',
            buttons: languages.buttons,
            prompt: languages.prompt,
            placeholder: languages.placeholder,
            click: function(e, editor, type) {
                base.fire('on_control_event_click', [e, editor, type, [FT, 'content']]);
            },
            keydown: function(e, editor) {
                base.fire('on_control_event_keydown', [e, editor, [FT, 'content']]);
            },
            ready: function(editor) {
                base.fire('on_control_event_ready', [editor, [FT, 'content']]);
            }
        });
        base.composer_content = base.composer;
        base.fire('on_control_end', [FT, 'content']);
        base.fire('on_control_begin', [FT, 'css']);
        base.editor_css = new MTE($css[0], {
            tabSize: base.tab_size,
            toolbar: false,
            click: function(e, editor, type) {
                base.fire('on_control_event_click', [e, editor, type, [FT, 'css']]);
            },
            keydown: function(e, editor) {
                base.fire('on_control_event_keydown', [e, editor, [FT, 'css']]);
            },
            ready: function(editor) {
                base.fire('on_control_event_ready', [editor, [FT, 'css']]);
            }
        });
        base.fire('on_control_end', [FT, 'css']);
        base.fire('on_control_begin', [FT, 'js']);
        base.editor_js = new MTE($js[0], {
            tabSize: base.tab_size,
            toolbar: false,
            click: function(e, editor, type) {
                base.fire('on_control_event_click', [e, editor, type, [FT, 'js']]);
            },
            keydown: function(e, editor) {
                base.fire('on_control_event_keydown', [e, editor, [FT, 'js']]);
            },
            ready: function(editor) {
                base.fire('on_control_event_ready', [editor, [FT, 'js']]);
            }
        });
        base.fire('on_control_end', [FT, 'js']);
        base.composer.button('table', {
            title: languages.others.table,
            position: 8,
            click: function(e, editor) {
                var s = editor.grip.selection(),
                    p = base.is_html_parser_enabled,
                    table = languages.others['table_text_' + (p ? 'raw' : 'html')];
                table = table.replace(/\t/g, base.tab_size);
                editor.grip.insert(table, function() {
                    editor.grip.select(s.start + (p ? 0 : 25 + (base.tab_size.length * 6)), s.start + table.indexOf(p ? ' |' : '</th>'), function() {
                        editor.grip.updateHistory();
                    });
                });
            }
        });
        if (base.is_html_parser_enabled === true) {
            base.composer.button('question-circle', {
                title: languages.others.help,
                click: function() {
                    window.open('http://mecha-cms.com/article/markdown-syntax');
                }
            });
        }
    }

    if ($('.btn-destruct').length === 0) {
        $.slugger($title, $slug, '-');
        $title.trigger("keyup");
    }

    var timer = null;

    $css.on("keyup", function() {
        window.clearTimeout(timer);
        timer = window.setTimeout(function() {
            if ($css_check.is(':checked')) $css_preview.html($css.val());
        }, 15);
    });

    $js.on("keyup", function() {
        window.clearTimeout(timer);
        timer = window.setTimeout(function() {
            if ($js_check.is(':checked')) $js_preview.html($js.val());
        }, 15);
    });

    $css_check.on("change", function() {
        if (this.checked) {
            $css.trigger("keyup");
        } else {
            $css_preview.html("");
        }
    });

    $js_check.on("change", function() {
        if (this.checked) {
            $js.trigger("keyup");
        } else {
            $js_preview.html("");
        }
    });

})(Zepto, DASHBOARD);