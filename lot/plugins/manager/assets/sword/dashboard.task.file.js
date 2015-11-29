// file
DASHBOARD.task.file = {
    B: function(path, s) {
        return path.split(s || new RegExp('[\\\\\\/]')).pop();
    },
    D: function(path, s) {
        return path.split(s || new RegExp('[\\\\\\/]')).slice(0, -1).join(DS);
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