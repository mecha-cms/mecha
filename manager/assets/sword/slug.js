/**
 * Slug Generator
 * --------------
 */

(function($, base) {
    $.slug = function(input, output, connect) {
        input.off("keyup change").on("keyup change", function() {
            output.val(base.task.slug(this.value.toLowerCase(), connect));
        });
        return input;
    };
})(window.Zepto || window.jQuery, DASHBOARD);