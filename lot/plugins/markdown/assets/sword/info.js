(function(w, d, base) {
    if (!base.composer) return;
    var speak = base.languages.MTE,
        name = 'question plugin-markdown';
    base.composer.button(name, {
        title: speak.buttons.info,
        position: -3,
        click: function(e, editor) {
            var s = editor.grip.selection(),
                sv = s.value.replace(/^\s+|\s+$/g, "").replace(/\s+/g, ' '),
                v = editor.grip.area.value,
                pos = v.indexOf('\n *[' + sv + ']:'), vn;
            editor.grip.area.scrollTop = editor.grip.area.scrollHeight;
            if (pos !== -1) { // already exists ...
                return editor.grip.select(pos + 4, pos + 4 + sv.length);
            }
            sv = sv || speak.placeholders.text;
            editor.prompt(speak.prompts.abbr_title_title, "", false, function(r) {
                r = r ? ' ' + r : "";
                v = v.replace(/\s+$/, "");
                if (v.indexOf('\n *[') === -1) {
                    vn = v + '\n\n' + ' *[' + sv + ']:' + r;
                } else {
                    vn = v + '\n' + ' *[' + sv + ']:' + r;
                }
                editor.grip.area.value = vn;
                pos = vn.indexOf('\n *[' + sv + ']:');
                editor.grip.select(pos + 4, pos + 4 + sv.length, true);
            });
        }
    });
    // `Ctrl + ?` for "abbreviation"
    base.composer.shortcut('ctrl+?', function() {
        return base.composer.grip.config.buttons[name].click(null, base.composer), false;
    });
})(window, document, DASHBOARD);