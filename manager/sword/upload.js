/**
 * Custom File Input
 * -----------------
 */

(function($) {

    var $uploader = $('input[type="file"]'),
        accepted = $uploader.attr('data-accepted-extensions') ? $uploader.data('acceptedExtensions').split(',') : 'css,html,js,md,txt,bmp,cur,gif,ico,jpg,jpeg,png,eot,ttf,woff,gz,rar,tar,zip,zipx'.split(','),
        cache = $uploader.prev().html();

    $uploader.on("change", function() {

        var segments = this.value.split('.'),
            extension = segments[segments.length - 1].toLowerCase(),
            ok = $.inArray(extension, accepted) !== -1,
            status = ok ? 'btn-success' : 'btn-danger',
            statusIcon = ok ? 'iconReady' : 'iconError';

        if (this.value === "") {

            $(this).prev().html(cache)
                .parent()
                    .removeClass(status);

        } else {

            $(this).attr('title', this.value)
                .prev()
                    .html('<i class="' + $(this).data(statusIcon) + '"></i> ' + this.value)
                        .parent()
                            .removeClass('btn-success btn-danger')
                                .addClass(status);

        }

    });

})(Zepto);