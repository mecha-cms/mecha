// parse url query
DASHBOARD.task.query = function(key, fallback) {
    var query = window.location.search.replace('?', "").split('&'),
        output = {}, q;
    fallback = fallback || false;
    if (!query.length) return fallback;
    for (var i in query) {
        q = query[i].split('=');
        output[q[0]] = decodeURIComponent(q[1]);
    }
    if (!key) {
        return Object.keys(output).length ? output : fallback;
    }
    return typeof output[key] !== "undefined" ? output[key] : fallback;
};