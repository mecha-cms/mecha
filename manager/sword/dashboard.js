/**
 * DASHBOARD
 * ---------
 */

window.DASHBOARD = {
    hooks: [],
    add: function(name, callback, priority) {
        if (typeof DASHBOARD.hooks[name] == "undefined") {
            DASHBOARD.hooks[name] = [];
        }
        if (parseInt(priority, 10) === priority) { // should be a valid integer
            if (DASHBOARD.hooks[name].length > priority + 1) {
                DASHBOARD.hooks[name].splice(priority, 0, callback);
            } else {
                DASHBOARD.hooks[name].push(callback);
            }
        } else {
            DASHBOARD.hooks[name].push(callback);
        }
    },
    fire: function(name, arguments) {
        if (typeof DASHBOARD.hooks[name] != "undefined") {
            for (var i = 0, len = DASHBOARD.hooks[name].length; i < len; ++i) {
                DASHBOARD.hooks[name][i](arguments);
            }
        }
    },
    eject: function(name) {
        delete DASHBOARD.hooks[name];
    },
    exist: function(name) {
        if (typeof name == "undefined") {
            return DASHBOARD.hooks;
        }
        return typeof DASHBOARD.hooks[name] != "undefined";
    }
};