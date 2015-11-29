(function(w, d, base) {
    if (!base.composer) return;
    base.composer.button('question-circle plugin-markdown', {
        title: base.languages.MTE.buttons.help,
        position: -3,
        click: function() {
            window.open('http://mecha-cms.com/article/markdown-syntax');
        }
    });
})(window, document, DASHBOARD);