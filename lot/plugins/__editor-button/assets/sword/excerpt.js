(function(w, d, base) {
    if (!base.composer) return;
    var classes = 'scissors plugin-editor';
    base.composer.button(classes, {
        title: base.languages.MTE.buttons.excerpt,
        position: -3,
        click: function(e, editor) {
            var editor = editor.grip,
                m = /<!-- cut(\+( .*?)?)? -->/.exec(editor.area.value),
                s = editor.selection(),
                clean_B = s.before.replace(/\s*<!-- cut(\+( .*?)?)? -->\s*/g, '\n\n').replace(/\s+$/, ""),
                clean_A = s.after.replace(/\s*<!-- cut(\+( .*?)?)? -->\s*/g, '\n\n').replace(/^\s+/, "");
            if (clean_B.length === 0) {
                editor.select();
                return;
            }
            var text = m && m[1] ? m[1] : "";
            editor.area.value = clean_B + '\n\n<!-- cut' + text + ' -->\n\n' + clean_A;
            editor.select(clean_B.length + 16 + text.length, function() {
                editor.updateHistory();
            });
        }
    });
    // `Ctrl + /` for "more tag"
    base.composer.shortcut('CTRL+191', function() {
        return base.composer.grip.config.buttons[classes].click(null, base.composer), false;
    });
})(window, document, DASHBOARD);