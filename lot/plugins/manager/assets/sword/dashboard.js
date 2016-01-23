/**
 * DASHBOARD
 * ---------
 */

window.DASHBOARD = {
    $: window.Zepto || window.jQuery, // library ...
    hook: [],
    task: {}, // stuff ...
    add: function(name, fn, stack) {
        stack = stack || 10;
        if (typeof DASHBOARD.hook[name] === "undefined") {
            DASHBOARD.hook[name] = [];
        }
        DASHBOARD.hook[name].push({
            'fn': fn,
            'stack': stack
        });
    },
    fire: function(name, arguments) {
        if (typeof DASHBOARD.hook[name] === "object") {
            DASHBOARD.hook[name].sort(function(a, b) {
                return a.stack - b.stack;
            });
            for (var i = 0, len = DASHBOARD.hook[name].length; i < len; ++i) {
                DASHBOARD.hook[name][i].fn(arguments);
            }
        } else {
            DASHBOARD.hook[name] = [];
        }
    },
    eject: function(name, stack) {
        if (typeof DASHBOARD.hook[name] !== "undefined") {
            if (typeof stack !== "undefined") {
                for (var i = 0, len = DASHBOARD.hook[name].length; i < len; ++i) {
                    if (DASHBOARD.hook[name][i].stack === stack) {
                        delete DASHBOARD.hook[name][i];
                    }
                }
            } else {
                delete DASHBOARD.hook[name];
            }
        } else {
            DASHBOARD.hook = [];
        }
    },
    exist: function(name, fallback) {
        fallback = fallback || false;
        if (typeof name === "undefined") {
            return Object.keys(DASHBOARD.hook).length ? DASHBOARD.hook : fallback;
        }
        return typeof DASHBOARD.hook[name] !== "undefined" ? DASHBOARD.hook[name] : fallback;
    }
};