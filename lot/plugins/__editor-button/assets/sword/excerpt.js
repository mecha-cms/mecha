(function(w, d, base) {
    if (!base.composer) return;
    var name = 'scissors plugin-editor';
    base.composer.button(name, {
        title: base.languages.MTE.buttons.excerpt,
        position: -3,
        click: function(e, editor) {
            editor = editor.grip;
            var m = /<!-- cut(\+( .*?)?)? -->/.exec(editor.area.value),
                s = editor.selection(),
                clean_B = s.before.replace(/\s*<!-- cut(\+( .*?)?)? -->\s*/g, '\n\n').replace(/\s+$/, ""),
                clean_A = s.after.replace(/\s*<!-- cut(\+( .*?)?)? -->\s*/g, '\n\n').replace(/^\s+/, ""),
                text = m && m[1] ? m[1] : "",
                o = clean_B + '\n\n<!-- cut' + text + ' -->\n\n' + clean_A;
            if (clean_B.length === 0 || o === editor.area.value) {
                return editor.select();
            }
            editor.area.value = o;
            editor.select((clean_B + text).length + 16, true);
        }
    });
    // `Ctrl + /` for "more tag"
    base.composer.shortcut('ctrl+/', function() {
        return base.composer.grip.config.buttons[name].click(null, base.composer), false;
    });
})(window, document, DASHBOARD);