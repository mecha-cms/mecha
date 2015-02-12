/**
 * AJAX Request
 * ------------
 *
 *    <button class="ajax-post" data-url="/path/to/action" data-loading-text="Loading&hellip;" data-error-text="Error." data-source="#my-form" data-destination="#my-div">Load!</button>
 *    <form id="my-form"></form>
 *    <div id="my-div"></div>
 *
 *    <button class="ajax-get" data-url="/path/to/file.html" data-loading-text="Loading&hellip;" data-error-text="Error." data-source="#my-container" data-destination="#my-div">Load!</button>
 *    <div id="my-div"></div>
 *
 */

(function($, base) {

    var $btn = $('.ajax-post, .ajax-get');

    if (!$btn.length) return;

    $btn.on("click", function(e) {

        var _this = this,
            $this = $(_this),
            _source = $this.data('source') || false,
            $source = $(_source),
            _error = $this.data('errorText') || "",
            _action = $this.data('url') || $source.attr('action'),
            _loading = $this.data('loadingText') || "",
            _is_get = $this.is('.ajax-get'),
            $destination = $($this.data('destination')) || $this.next();

        $destination.html(_loading);

        base.fire('on_ajax_begin', [e, _this]);

        $.ajax({
            url: _action,
            type: _is_get ? 'GET' : 'POST',
            data: _is_get || _source === false ? "" : $source.serializeArray(),
            success: function(data, status, xhr) {
                $destination.html(_is_get ? (_source !== false ? $(data).find(_source) : $(data)) : data);
                base.fire('on_ajax_success', {
                    'data': data,
                    'status': status,
                    'xhr': xhr,
                    'event': e,
                    'target': _this
                });
                base.fire('on_ajax_end', {
                    'event': e,
                    'target': _this
                });
            },
            error: function(xhr, status, error) {
                $destination.html(_error);
                base.fire('on_ajax_error', {
                    'xhr': xhr,
                    'status': status,
                    'error': error,
                    'event': e,
                    'target': _this
                });
                base.fire('on_ajax_end', {
                    'event': e,
                    'target': _this
                });
            }
        });

        return false;

    });

})(Zepto, DASHBOARD);