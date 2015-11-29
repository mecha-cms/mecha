(function(w, d, base) {
    if (!base.composer) return;
    var speak = base.languages.MTE,
        editor = base.composer.grip;
    base.composer.button('table default-table-button', {
        title: speak.others.table,
        position: -3,
        click: function(e) {
            var s = editor.selection(),
                table = speak.others.table_text;
            table = table.replace(/\t/g, TAB);
            var clean_B = s.before.replace(/\s+$/, ""),
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
})(window, document, DASHBOARD);