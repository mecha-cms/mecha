/**
 * Slug Generator
 * --------------
 */

(function(base, $) {
    $.slug = function(input, output, connect) {
        if (typeof input === "string") {
            input = $('[name="' + input + '"]');
        }
        if (typeof output === "string") {
            output = $('[name="' + output + '"]');
        }
        input.off("keyup change input").on("keyup change input", function() {
            output.val(base.task.slug(this.value.toLowerCase(), connect));
        });
        return input;
    };
})(DASHBOARD, DASHBOARD.$);