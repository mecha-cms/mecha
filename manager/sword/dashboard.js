/**
 * DASHBOARD
 * ---------
 */

window.DASHBOARD = {
    hooks: [],
    register: function(name, callback, priority) {
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
    call: function(name, arguments) {
        if (typeof DASHBOARD.hooks[name] != "undefined") {
            for (var i = 0, len = DASHBOARD.hooks[name].length; i < len; ++i) {
                DASHBOARD.hooks[name][i](arguments);
            }
        }
    }
};