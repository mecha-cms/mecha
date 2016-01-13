// file
DASHBOARD.task.file = {
    B: function(path, step, s) {
        step = step || 1;
        s = typeof DS !== "undefined" ? DS : '/';
        return path.split(s === '/' || s === '\\' ? new RegExp('[\\\\\\/]') : s).slice(-step).join(s);
    },
    D: function(path, step, s) {
        step = step || 1;
        s = typeof DS !== "undefined" ? DS : '/';
        return path.split(s === '/' || s === '\\' ? new RegExp('[\\\\\\/]') : s).slice(0, -step).join(s);
    },
    N: function(path, extension) {
        path = path.split(new RegExp('[\\\\\\/]')).pop();
        return !extension ? path.replace(/\.(\w+)$/, "") : path;
    },
    E: function(path, fallback) {
        fallback = fallback || "";
        path = path.replace(/.*?(?:\.(\w+))?$/, '$1').toLowerCase();
        return path !== "" ? path : fallback;
    }
};