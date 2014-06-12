/**
 * Responsive Tooltip
 * ------------------
 *
 *    <i class="fa fa-question-circle help" title="Test tooltip!"></i>
 *
 */

(function($) {

    var $window = $(window),
        $document = $(document),
        $body = $(document.body),
        $target = $('.help[title]'),
        $tooltip = $('<div class="tooltip t hidden"></div>').appendTo($body),
        timer = null;

    if (!$target.length) return;

    $target.on("mouseenter", function() {

        if (!$(this).data('title')) return;

        var $this = $(this);

        timer = window.setTimeout(function() {

            $tooltip.removeAttr('style')
                .html($this.data('title') + '<span class="tooltip-arrow"></span>')
                    .removeClass('t r b l hidden');


            /**
             * padding vertical = 6
             * padding horizontal = 8
             */

            var distance = {
                vertical: 6,
                horizontal: 8
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

            if (left + width > $window.width()) {
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

        }, 400);

    }).on("mouseleave", function() {
        window.clearTimeout(timer);
        timer = window.setTimeout(function() {
            $tooltip.removeAttr('style').removeClass('t r b l').addClass('hidden');
        }, 400);
    }).data('title', function() {
        return this.title ? this.title : false;
    }).removeAttr('title');

    $tooltip.on("mouseenter", function() {
        window.clearTimeout(timer);
    }).on("mouseleave", function() {
        window.clearTimeout(timer);
        timer = window.setTimeout(function() {
            $tooltip.removeAttr('style').removeClass('t r b l').addClass('hidden');
        }, 400);
    });

    $window.on("resize", function() {
        window.clearTimeout(timer);
        $tooltip.removeAttr('style').removeClass('t r b l').addClass('hidden');
    });

})(Zepto);