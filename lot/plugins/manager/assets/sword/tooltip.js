/**
 * Responsive Tooltip
 * ------------------
 *
 *    <i class="fa fa-question-circle help" title="Test tooltip!"></i>
 *
 */

(function(base, $) {

    var $window = $(window),
        $document = $(document),
        $body = $(document.body),
        $target = $('.help[title], .has-tooltip[title], [data-tooltip]'),
        $tooltip = $('<div class="tooltip t hidden"></div>').appendTo($body),
        timer = null;

    if (!$target.length) return;

    $target.on("mouseenter", function(e) {

        var _this = this, $this = $(_this);

        if (!$this.data('tooltip')) return;

        timer = window.setTimeout(function() {

            $tooltip.removeAttr('style')
                .html($this.data('tooltip') + '<span class="arrow"></span>')
                    .removeClass('long t r b l hidden');

            if ($this.data('tooltip').replace(/<.*?>|&lt;.*?&gt;/g, "").length > 50) {
                $tooltip.addClass('long');
            }

            var distance = {
                vertical: parseInt($tooltip.css('padding-top'), 10),
                horizontal: parseInt($tooltip.css('padding-left'), 10)
            };

            var width = $tooltip.width(),
                height = $tooltip.height(),
                pos = $this.offset(),
                top = pos.top - height - distance.vertical,
                left = pos.left - (width / 2) + ($this.width() / 2);

            if (top - $window.scrollTop() <= 0) {
                top = pos.top + $this.height() + distance.vertical;
                $tooltip.removeClass('t').addClass('b');
            } else {
                $tooltip.removeClass('b').addClass('t');
            }

            if (left + width > $body.parent().width()) {
                left = pos.left - width + $this.width() + distance.horizontal;
                $tooltip.removeClass('r').addClass('l');
            } else if (left <= 0) {
                left = pos.left - distance.horizontal;
                $tooltip.removeClass('l').addClass('r');
            }

            $tooltip.css({
                top: top,
                left: left
            });

            base.fire('on_tooltip_show', {
                'event': e,
                'target': _this
            });

        }, 400);

    }).on("mouseleave", function(e) {
        var _this = this;
        window.clearTimeout(timer);
        timer = window.setTimeout(function() {
            $tooltip.removeAttr('style').removeClass('t r b l').addClass('hidden');
            base.fire('on_tooltip_hide', {
                'event': e,
                'target': _this
            });
        }, 400);
    }).attr('data-tooltip', function() {
        return this.title || this.getAttribute('data-tooltip') || "";
    }).removeAttr('title');

    $tooltip.on("mouseenter", function(e) {
        window.clearTimeout(timer);
        base.fire('on_tooltip_enter', {
            'event': e,
            'target': this
        });
    }).on("mouseleave", function(e) {
        var _this = this;
        window.clearTimeout(timer);
        timer = window.setTimeout(function() {
            $tooltip.removeAttr('style').removeClass('t r b l').addClass('hidden');
            base.fire('on_tooltip_exit', {
                'event': e,
                'target': _this
            });
        }, 400);
    });

    $window.on("resize", function(e) {
        window.clearTimeout(timer);
        $tooltip.removeAttr('style').removeClass('t r b l').addClass('hidden');
        base.fire('on_tooltip_hide', {
            'event': e,
            'target': this
        });
    });

})(DASHBOARD, DASHBOARD.$);