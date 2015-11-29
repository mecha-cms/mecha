(function(w, d, base) {
    if (!base.composer) return;
    var speak = base.languages.MTE;
    base.composer.button('question-circle default-help-button', {
        title: speak.others.help,
        click: function() {
            window.open('http://mecha-cms.com/article/markdown-syntax');
        }
    });
})(window, document, DASHBOARD);