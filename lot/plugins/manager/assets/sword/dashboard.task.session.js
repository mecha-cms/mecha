// cookie & storage
DASHBOARD.task.session = {
    set: function(session, value, expire) {
        if (session.indexOf('cookie:') === 0) {
            session = session.replace(/[\s=;]/g, '_');
            value = encodeURIComponent(value);
            expire = expire || 1;
            var str = "";
            if (expire) {
                var date = new Date();
                date.setTime(date.getTime() + (expire * 24 * 60 * 60 * 1000));
                str += '; expires=' + date.toGMTString();
            }
            document.cookie = session + '=' + value + str + '; path=/';
        } else {
            localStorage.setItem(session, JSON.stringify(value));
        }
    },
    get: function(session, fallback) {
        if (session === 'cookies' || session.indexOf('cookie:') === 0) {
            fallback = fallback || false;
            var output = {},
                cookies = document.cookie.split(/;\s*/);
            for (var i = 0, len = cookies.length; i < len; ++i) {
                var parts = cookies[i].split('=');
                output[parts[0]] = decodeURIComponent(parts[1]);
            }
            if (session !== 'cookies') {
                return typeof output[session] !== "undefined" ? output[session] : fallback;
            }
            return Object.keys(output).length ? output : fallback;
        } else {
            return JSON.parse(localStorage.getItem(session)) || fallback;
        }
    },
    kill: function(session) {
        if (session.indexOf('cookie:') === 0) {
            DASHBOARD.task.session.set(session, "", -1);
        } else {
            localStorage.removeItem(session);
        }
    }
};