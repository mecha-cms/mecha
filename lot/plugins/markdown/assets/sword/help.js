(function(w, d, base) {
    if (!base.composer) return;
    var name = 'question-circle plugin-markdown';
    base.composer.button(name, {
        title: base.languages.MTE.buttons.help,
        position: -3,
        click: function() {
            window.open('http://mecha-cms.com/article/markdown-syntax');
        }
    });
    // `F1` for "help"
    base.composer.shortcut('f1', function() {
        return base.composer.grip.config.buttons[name].click(), false;
    });
})(window, document, DASHBOARD);