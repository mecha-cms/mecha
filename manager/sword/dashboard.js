/**
 * DASHBOARD
 * ---------
 */

window.DASHBOARD = {
    hooks: [],
    add: function(name, fn, stack) {
        if (typeof DASHBOARD.hooks[name] === "undefined") {
            DASHBOARD.hooks[name] = [];
        }
        if (typeof stack === "undefined") {
            stack = 10;
        }
        DASHBOARD.hooks[name].push({
            'fn': fn,
            'stack': stack
        });
    },
    fire: function(name, arguments) {
        if (typeof DASHBOARD.hooks[name] === "object") {
            DASHBOARD.hooks[name].sort(function(a, b) {
                return a.stack - b.stack;
            });
            for (var i = 0, len = DASHBOARD.hooks[name].length; i < len; ++i) {
                DASHBOARD.hooks[name][i].fn(arguments);
            }
        } else {
            DASHBOARD.hooks[name] = [];
        }
    },
    eject: function(name, stack) {
        if (typeof DASHBOARD.hooks[name] !== "undefined") {
            if (typeof stack !== "undefined") {
                for (var i = 0, len = DASHBOARD.hooks[name].length; i < len; ++i) {
                    if (DASHBOARD.hooks[name][i].stack === stack) {
                        delete DASHBOARD.hooks[name][i];
                    }
                }
            } else {
                delete DASHBOARD.hooks[name];
            }
        } else {
            DASHBOARD.hooks = [];
        }
    },
    exist: function(name, fallback) {
        if (typeof fallback === "undefined") {
            fallback = false;
        }
        if (typeof name === "undefined") {
            return Object.keys(DASHBOARD.hooks).length > 0 ? DASHBOARD.hooks : fallback;
        }
        return typeof DASHBOARD.hooks[name] !== "undefined" ? DASHBOARD.hooks[name] : fallback;
    }
};