(function(base, $) {
    var $css = $('textarea[name="css"]'),
        $js = $('textarea[name="js"]'),
        $check = {
            css: $('input[name="css_preview"]'),
            js: $('input[name="js_preview"]')
        },
        $preview = {
            css: $('<div></div>').appendTo(document.body),
            js: $('<div></div>').appendTo(document.body)
        }, timer = null;
    // CSS Preview
    $css.on("keyup", function() {
        clearTimeout(timer);
        timer = setTimeout(function() {
            if ($check.css.is(':checked')) $preview.css.html($css.val());
        }, 1000);
    });
    $check.css.on("change", function() {
        $preview.css.html(this.checked ? $css.val() : "");
    });
    // JS Preview
    $js.on("keyup", function() {
        clearTimeout(timer);
        timer = setTimeout(function() {
            if ($check.js.is(':checked')) $preview.js.html($js.val());
        }, 1000);
    });
    $check.js.on("change", function() {
        $preview.js.html(this.checked ? $js.val() : "");
    });
})(DASHBOARD, DASHBOARD.$);