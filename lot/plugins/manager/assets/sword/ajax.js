/**
 * AJAX Request
 * ------------
 *
 *    <button class="ajax-post" data-action-url="/path/to/action" data-text-progress="Loading&hellip;" data-text-error="Error." data-scope="#my-form" data-target="#my-div">Load!</button>
 *    <form id="my-form"></form>
 *    <div id="my-div"></div>
 *
 *    <button class="ajax-get" data-url="/path/to/file.html" data-text-progress="Loading&hellip;" data-text-error="Error." data-scope="#my-scope" data-target="#my-div">Load!</button>
 *    <div id="my-div"></div>
 *
 */

(function(base, $) {

    var $btn = $('.ajax-post, .ajax-get');

    if (!$btn.length) return;

    $btn.on("click", function(e) {
        var _this = this,
            $this = $(_this),
            _source = $this.data('scope') || false,
            $source = $(_source),
            _error = $this.data('textError') || "",
            _action = $this.data('actionUrl') || $this.data('url') || $source.attr('action'),
            _progress = $this.data('textProgress') || "",
            _is_get = $this.is('.ajax-get'),
            $destination = $($this.data('target')) || $this.next(),
            _data = {
                'event': e,
                'target': _this
            };
        $destination.html(_progress);
        base.fire('on_ajax_begin', _data);
        $.ajax({
            url: _action,
            type: _is_get ? 'GET' : 'POST',
            data: _is_get || _source === false ? "" : $source.serializeArray(),
            success: function(response, status, xhr) {
                $destination.html(_is_get ? (_source !== false ? $(response).find(_source) : $(response)) : response);
                base.fire('on_ajax_success', {
                    'data': response,
                    'status': status,
                    'xhr': xhr,
                    'event': e,
                    'target': _this
                });
                base.fire('on_ajax_end', _data);
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
                base.fire('on_ajax_end', _data);
            }
        });
        return false;
    });

})(DASHBOARD, DASHBOARD.$);