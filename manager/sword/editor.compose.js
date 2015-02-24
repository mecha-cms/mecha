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
            var editor = editor.grip,
                s = editor.selection(),
                clean_B = s.before.replace(/\s*$/, ""),
                clean_A = s.after.replace(/^\s*/, ""),
                s_B = clean_B.length > 0 ? '\n\n' : "",
                p = base.is_html_parser_enabled,
                table = speak.others['table_text_' + (p ? 'raw' : 'html')],
            table = s_B + table.replace(/\t/g, TAB) + '\n\n';
            editor.area.value = clean_B + table + clean_A;
            editor.select(clean_B.length + s_B.length + (p ? 0 : 25 + (TAB.length * 6)), clean_B.length + table.indexOf(p ? ' |' : '</th>'), function() {
                editor.updateHistory();
            });
        }
    });

    base.composer.button('scissors', {
        title: speak.others.excerpt,
        click: function(e, editor) {
            var editor = editor.grip,
                s = editor.selection(),
                clean_B = s.before.replace(/\s+$/, "").replace(/\s*<!-- cut -->\s*/g, '\n\n'),
                clean_A = s.after.replace(/^\s+/, "").replace(/\s*<!-- cut -->\s*/g, '\n\n');
            if (s.before.length === 0) {
                editor.select(0);
                return;
            }
            editor.area.value = clean_B + '\n\n<!-- cut -->\n\n' + clean_A;
            editor.select(clean_B.length + 16, function() {
                editor.updateHistory();
            });
        }
    });

    if (base.is_html_parser_enabled) {
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

})(window.Zepto || window.jQuery, DASHBOARD);