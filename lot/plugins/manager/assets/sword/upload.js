/**
 * Custom File Input
 * -----------------
 *
 *    <form class="form-upload">
 *      <span class="input-outer btn btn-default">
 *        <span><i class="fa fa-folder-open"></i> Select a file&hellip;</span>
 *        <input type="file" name="file" data-icon-ready="fa fa-check" data-icon-error="fa fa-times">
 *      </span> <button class="btn btn-action" type="submit"><i class="fa fa-cloud-upload"></i> Upload</button>
 *    </form>
 *
 */

(function(base, $) {

    var $uploader = $('.form-upload input[type="file"]'),
        accepted = ($uploader.data('acceptedExtensions') || base.file_extension_allow).split(','),
        cache = $uploader.prev().html();

    if (!$uploader.length) return;

    $uploader.on("change", function(e) {
        var extension = base.task.file.E(this.value),
            ok = $.inArray(extension, accepted) !== -1,
            status = ok ? 'btn-accept' : 'btn-reject',
            statusIcon = ok ? 'iconReady' : 'iconError',
            data = {
                'event': e,
                'target': this
            };
        $(this).parent().next().prop('disabled', !ok);
        if (this.value === "") {
            $(this).prev().html(cache)
                .parent()
                    .removeClass('btn-accept btn-reject');
        } else {
            $(this)
                .prev()
                    .html('<i class="' + $(this).data(statusIcon) + '"></i> ' + this.value)
                        .parent()
                            .attr('title', this.value)
                                .removeClass('btn-accept btn-reject')
                                    .addClass(status);
        }
        base.fire('on_file_change', data);
        base.fire('on_file_' + (ok ? 'accept' : 'reject'), data);
    });

})(DASHBOARD, DASHBOARD.$);