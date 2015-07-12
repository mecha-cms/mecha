// extend variable(s)
DASHBOARD.task.extend = function(a, b) {
    a = a || {};
    for (var c in b) {
        if (typeof b[c] === "object") {
            a[c] = DASHBOARD.task.extend(a[c], b[c]);
        } else {
            a[c] = b[c];
        }
    }
    return a;
};