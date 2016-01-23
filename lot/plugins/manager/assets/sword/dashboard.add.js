DASHBOARD.add('FORM_REFRESH', function() {
    var $$ = false;
    if (typeof Zepto === "function") {
        $$ = Zepto;
    } else if (typeof jQuery === "function") {
        $$ = jQuery;
    }
    if ($$) {
        $$('input[type="checkbox"], input[type="radio"], input[type="range"], select').trigger("change");
        $$('input[type="color"], input[type="date"], input[type="email"], input[type="number"], input[type="password"], input[type="search"], input[type="tel"], input[type="text"], input[type="url"], textarea').trigger("keyup").trigger("keydown").trigger("input");
    }
});