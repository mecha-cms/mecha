/* Toggle Mobile Navigation */

(function() {

    if (!document.querySelector) return;

    var body = document.body,
        toggle = document.querySelector('.blog-sidebar-toggle');

    body.spellcheck = false;

    if (!toggle) return;

    toggle.addEventListener("click", function(e) {
        this.classList.toggle('active');
        body.classList.toggle('blog-sidebar-is-visible');
        body.scrollTop = 0;
        body.parentNode.scrollTop = 0;
        e.preventDefault();
    }, false);

})();