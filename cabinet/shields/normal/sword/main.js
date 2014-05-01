/* Toggle Mobile Navigation */

(function() {

    if (!document.querySelector) return;

    var body = document.body,
        toggle = document.querySelector('.blog-sidebar-toggle');

    if (!toggle) return;

    toggle.addEventListener("click", function(e) {
        this.classList.toggle('active');
        body.classList.toggle('blog-sidebar-is-visible');
        e.preventDefault();
    }, false);

})();