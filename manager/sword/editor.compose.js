/**
 * Article/Page Editor
 * -------------------
 */

(function($, base) {

    var $base = $(document.body),
        $form = $('#form-compose'),
        $tab = $('.tab-area a'),
        $title = $($form[0].title),
        $slug = $($form[0].slug),
        $content = $($form[0].content),
        $css = $($form[0].css),
        $js = $($form[0].js),
        $css_check = $($form[0].css_live_check),
        $js_check = $($form[0].js_live_check);

    var $preview_css = $('<div id="live-preview-css"></div>').appendTo($base),
        $preview_js = $('<div id="live-preview-js"></div>').appendTo($base);

    var speak = base.languages.MTE;

    base.composer.button('table', {
        title: speak.others.table,
        position: 8,
        click: function(e, editor) {
            var s = editor.grip.selection(),
                p = base.is_html_parser_enabled,
                table = speak.others['table_text_' + (p ? 'raw' : 'html')];
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
            title: speak.others.help,
            click: function() {
                window.open('http://mecha-cms.com/article/markdown-syntax');
            }
        });
    }

    if ($('.btn-destruct').length === 0) {
        $.slug($title, $slug, '-');
        $title.trigger("keyup");
    }

    var timer = null;

    $css.on("keyup", function() {
        window.clearTimeout(timer);
        timer = window.setTimeout(function() {
            if ($css_check.is(':checked')) $preview_css.html($css.val());
        }, 15);
    });

    $js.on("keyup", function() {
        window.clearTimeout(timer);
        timer = window.setTimeout(function() {
            if ($js_check.is(':checked')) $preview_js.html($js.val());
        }, 15);
    });

    $css_check.on("change", function() {
        if (this.checked) {
            $css.trigger("keyup");
        } else {
            $preview_css.html("");
        }
    });

    $js_check.on("change", function() {
        if (this.checked) {
            $js.trigger("keyup");
        } else {
            $preview_js.html("");
        }
    });

})(Zepto, DASHBOARD);