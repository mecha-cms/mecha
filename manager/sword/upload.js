/**
 * Custom file input
 */

(function($) {

    var $uploader = $('input[type="file"]'), cache = $uploader.prev().html();

    $uploader.on("change", function() {

        if (this.value === "") {

            $(this).prev().html(cache)
                .parent()
                    .removeClass('btn-success');

        } else {

            $(this).attr('title', this.value)
                .prev()
                    .html($(this).data('iconReady') + this.value)
                        .parent()
                            .addClass('btn-success');

        }

    });

})(Zepto);