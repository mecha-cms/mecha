(function(w, d, base) {
    if (!base.composer) return;
    var speak = base.languages.MTE,
        classes = 'table plugin-editor';
    base.composer.button(classes, {
        title: speak.buttons.table,
        position: -3,
        click: function(e, editor) {
            var editor = editor.grip,
                s = editor.selection(),
                table = speak.placeholders.table_text;
            table = table.replace(/\t/g, TAB);
            var clean_B = s.before.replace(/\s+$/, ""),
                B = clean_B.length,
                S = 25 + (TAB.length * 6),
                E = table.indexOf('</th>'),
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
        return base.composer.grip.config.buttons[classes].click(null, base.composer), false;
    });
})(window, document, DASHBOARD);