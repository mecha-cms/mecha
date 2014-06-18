/**
 * Custom File Input
 * -----------------
 *
 *    <form class="form-upload">
 *      <span class="input-wrapper btn">
 *        <span><i class="fa fa-folder-open"></i> Select a file&hellip;</span>
 *        <input type="file" name="file" data-icon-ready="fa fa-check" data-icon-error="fa fa-times">
 *      </span> <button class="btn btn-primary btn-upload" type="submit"><i class="fa fa-cloud-upload"></i> Upload</button>
 *    </form>
 *
 */

(function($, base) {

    var $uploader = $('input[type="file"]'),
        accepted = $uploader.attr('data-accepted-extensions') ? $uploader.data('acceptedExtensions').split(',') : 'css,html,js,md,txt,bmp,cur,gif,ico,jpg,jpeg,png,eot,ttf,woff,gz,rar,tar,zip,zipx'.split(','),
        cache = $uploader.prev().html();

    if (!$uploader.length) return;

    $uploader.on("change", function(e) {

        var segments = this.value.split('.'),
            extension = segments[segments.length - 1].toLowerCase(),
            ok = $.inArray(extension, accepted) !== -1,
            status = ok ? 'btn-success' : 'btn-danger',
            statusIcon = ok ? 'iconReady' : 'iconError';

        if (this.value === "") {

            $(this).prev().html(cache)
                .parent()
                    .removeClass('btn-success btn-danger');

        } else {

            $(this).attr('title', this.value)
                .prev()
                    .html('<i class="' + $(this).data(statusIcon) + '"></i> ' + this.value)
                        .parent()
                            .removeClass('btn-success btn-danger')
                                .addClass(status);

        }

        base.fire('on_file_change', [e, this]);

    });

})(Zepto, DASHBOARD);