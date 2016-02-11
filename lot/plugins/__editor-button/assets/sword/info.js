(function(w, d, base) {
    if (!base.composer) return;
    var speak = base.languages.MTE,
        name = 'question plugin-editor';
    base.composer.button(name, {
        title: speak.buttons.info,
        position: -3,
        click: function(e, editor) {
            var s = editor.grip.selection(),
                sv = s.value.replace(/^\s+|\s+$/g, ""),
                is_abbr = sv.indexOf(' ') === -1 && sv === sv.toUpperCase(); // `abbr` or `dfn` ?
            editor.prompt(speak.prompts[(is_abbr ? 'abbr' : 'dfn') + '_title_title'], "", false, function(r) {
                var clean_B = s.before.replace(/\s*<(abbr|dfn)(>| .*?>)\s*$|\s+$/, ""),
                    clean_A = s.after.replace(/^\s+|^\s*<\/(abbr|dfn)>\s*/, ""),
                    clean_V = sv.replace(/^<(?:abbr|dfn)(?:>| .*?>)\s*([\s\S]+?)\s*(?:<\/(?:abbr|dfn)>)$/, '$1'),
                    B = clean_B.length ? ' ' : "",
                    A = clean_A.length ? ' ' : "",
                    t = r ? ' title="' + r.replace(/"/g, '&quot;') + '"' : "",
                    o = is_abbr ? '<abbr' + t + '>' : '<dfn' + t + '>',
                    c = is_abbr ? '</abbr>' : '</dfn>', start;
                clean_V = clean_V || speak.placeholders.text;
                editor.grip.area.value = clean_B + B + o + clean_V + c + A + clean_A;
                start = (clean_B + B + o).length;
                editor.grip.select(start, start + clean_V.length, true);
            });
        }
    });
    // `Ctrl + ?` for "abbreviation/definition"
    base.composer.shortcut('ctrl+?', function() {
        return base.composer.grip.config.buttons[name].click(null, base.composer), false;
    });
})(window, document, DASHBOARD);