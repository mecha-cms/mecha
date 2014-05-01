/**
 * Generate slug from title input
 */

(function($) {

    $.slugger = function(input, output, connector) {

        input.on("keyup", function() {

            output.val(
                this.value
                    .replace(/[^a-z0-9-]+/gi, "-")
                    .replace(/\-+/g, "-")
                    .replace(/^\-|\-$/g, "")
                    .toLowerCase()
                    .replace(/\-/g, connector)
            );

        });

        return input;

    };

})(Zepto);