/**
 * Article/Page Editor
 * -------------------
 */

(function($, base) {

    var $base = $(document.body),
        $form = $('#form-ignite, #form-repair'),
        $tab = $('.tab-area a'),
        $title = $($form[0].title),
        $slug = $($form[0].slug),
        $content = $($form[0].content),
        $css = $($form[0].css),
        $js = $($form[0].js),
        $check_css = $($form[0].css_live_check),
        $check_js = $($form[0].js_live_check),
        editor = base.composer.grip;

    var $preview_css = $('<div></div>').appendTo($base),
        $preview_js = $('<div></div>').appendTo($base);

    var speak = base.languages.MTE;

    base.composer.button('table default-table-button', {
        title: speak.others.table,
        position: -3,
        click: function(e) {
            var s = editor.selection(),
                p = base.is_html_parser_enabled,
                table = speak.others['table_text_' + (p ? 'raw' : 'html')];
            table = table.replace(/\t/g, TAB);
            var clean_B = s.before.replace(/\s+$/, ""),
                B = clean_B.length,
                S = p ? 0 : 25 + (TAB.length * 6),
                E = table.indexOf(p ? ' |' : '</th>'),
                X = B ? 2 : 0,
                start = B + S + X,
                end = B + E + X;
            if (s.value.length && s.value === table.substring(S, E)) {
                editor.select();
            } else {
                editor.tidy('\n\n', function() {
                    editor.insert(table, function() {
                        editor.select(start, end, function() {
                            editor.updateHistory();
                        });
                    });
                }, '\n\n', true);
            }
        }
    });

    // `Ctrl + T` for "table"
    base.composer.shortcut('CTRL+84', function() {
        return editor.config.buttons['table default-table-button'].click(), false;
    });

    base.composer.button('scissors default-more-tag-button', {
        title: speak.others.excerpt,
        position: -3,
        click: function(e) {
            var m = /<!-- cut(\+( .*?)?)? -->/.exec(editor.area.value),
                s = editor.selection(),
                clean_B = s.before.replace(/\s*<!-- cut(\+( .*?)?)? -->\s*/g, '\n\n').replace(/\s+$/, ""),
                clean_A = s.after.replace(/\s*<!-- cut(\+( .*?)?)? -->\s*/g, '\n\n').replace(/^\s+/, "");
            if (clean_B.length === 0) {
                editor.select();
                return;
            }
            var more_text = m && m[1] ? m[1] : "";
            editor.area.value = clean_B + '\n\n<!-- cut' + more_text + ' -->\n\n' + clean_A;
            editor.select(clean_B.length + 16 + more_text.length, function() {
                editor.updateHistory();
            });
        }
    });

    // `Ctrl + /` for "more tag"
    base.composer.shortcut('CTRL+191', function() {
        return editor.config.buttons['scissors default-more-tag-button'].click(), false;
    });

    if (base.is_html_parser_enabled) {
        base.composer.button('question-circle default-help-button', {
            title: speak.others.help,
            click: function() {
                window.open('http://mecha-cms.com/article/markdown-syntax');
            }
        });
    }

    if (!$('.btn-destruct').length) {
        $.slug($title, $slug, '-');
        if ($slug.val() === "") $title.trigger("keyup");
    }

    var timer = null;

    $css.on("keyup", function() {
        window.clearTimeout(timer);
        timer = window.setTimeout(function() {
            if ($check_css.is(':checked')) $preview_css.html($css.val());
        }, 15);
    });

    $js.on("keyup", function() {
        window.clearTimeout(timer);
        timer = window.setTimeout(function() {
            if ($check_js.is(':checked')) $preview_js.html($js.val());
        }, 15);
    });

    $check_css.on("change", function() {
        if (this.checked) {
            $css.trigger("keyup");
        } else {
            $preview_css.html("");
        }
    });

    $check_js.on("change", function() {
        if (this.checked) {
            $js.trigger("keyup");
        } else {
            $preview_js.html("");
        }
    });

})(window.Zepto || window.jQuery, DASHBOARD);