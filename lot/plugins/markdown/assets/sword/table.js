(function(w, d, base) {
    if (!base.composer) return;
    var speak = base.languages.MTE,
        name = 'table plugin-markdown';
    base.composer.button(name, {
        title: speak.buttons.table,
        position: -3,
        click: function(e, editor) {
            editor = editor.grip;
            var s = editor.selection(),
                table = speak.placeholders.table_text,
                clean_B = s.before.replace(/\s+$/, ""),
                B = clean_B.length,
                S = 0,
                E = table.indexOf(' |'),
                X = B ? 2 : 0,
                start = B + S + X,
                end = B + E + X;
            if (s.value.length && s.value === table.substring(S, E)) {
                editor.select();
            } else {
                editor.tidy('\n\n', function() {
                    editor.insert(table, function() {
                        editor.select(start, end, true);
                    });
                }, '\n\n', true);
            }
        }
    });
    // `Ctrl + T` for "table"
    base.composer.shortcut('ctrl+t', function() {
        return base.composer.grip.config.buttons[name].click(null, base.composer), false;
    });
})(window, document, DASHBOARD);