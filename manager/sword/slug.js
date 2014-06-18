/**
 * Slug Generator
 * --------------
 */

(function($) {

    $.slugger = function(input, output, connector) {

        input.off("keyup").on("keyup", function() {

            output.val(
                this.value
                    .replace(/<.*?>/g, "")
                    .replace(/[^a-z0-9-]+/gi, "-")
                    .replace(/\-+/g, "-")
                    .replace(/^\-|\-$/g, "")
                    .toLowerCase()
                    .replace(/\-/g, connector)
            );

        }).trigger("keyup");

        return input;

    };

})(Zepto);