/**
 * DASHBOARD
 * ---------
 */

(function() {

    var D = function() {

        var _ = this;

        _.$ = window.Zepto || window.jQuery;

        // Hook(s)
        _.hook = [];

        // Task(s)
        _.task = {

            // Event
            event: {
                add: function(el, ev, fn) {
                    if (el.addEventListener) {
                        el.addEventListener(ev, fn, false);
                    } else if (el.attachEvent) {
                        el.attachEvent('on' + ev, fn);
                    } else {
                        el['on' + ev] = fn;
                    }
                },
                fire: function(el, ev) {
                    var e;
                    if (document.createEvent) {
                        e = new Event(ev);
                        el.dispatchEvent(e);
                    } else {
                        e = document.createEventObject();
                        el.fireEvent('on' + ev, e);
                    }
                }
            },

            // Extend
            extend: function(a, b) {
                a = a || {};
                for (var c in b) {
                    if (typeof b[c] === "object") {
                        a[c] = _.task.extend(a[c], b[c]);
                    } else {
                        a[c] = b[c];
                    }
                }
                return a;
            },

            // URL Query
            query: function(key, fallback) {
                var a = window.location.search.replace('?', "").split('&'),
                    b = {}, q;
                if (typeof fallback === "undefined") fallback = false;
                if (!a.length) return fallback;
                for (var i in a) {
                    q = a[i].split('=');
                    b[q[0]] = decodeURIComponent(q[1]);
                }
                if (!key) {
                    return Object.keys(b).length ? b : fallback;
                }
                return typeof b[key] !== "undefined" ? b[key] : fallback;
            },

            // Session
            session: {
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
                        if (typeof fallback === "undefined") fallback = false;
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
                        _.task.session.set(session, "", -1);
                    } else {
                        localStorage.removeItem(session);
                    }
                }
            },

            // Slug
            slug: function(input, join, exclude) {
                join = join || '-';
                exclude = exclude || 'a-zA-Z0-9' + join;
                var from, to;
                from = '¹,²,³,°,æ,ǽ,À,Á,Â,Ã,Å,Ǻ,Ă,Ǎ,Æ,Ǽ,à,á,â,ã,å,ǻ,ă,ǎ,ª,@,Ĉ,Ċ,ĉ,ċ,©,Ð,Đ,ð,đ,È,É,Ê,Ë,Ĕ,Ė,è,é,ê,ë,ĕ,ė,ƒ,Ĝ,Ġ,ĝ,ġ,Ĥ,Ħ,ĥ,ħ,Ì,Í,Î,Ï,Ĩ,Ĭ,Ǐ,Į,Ĳ,ì,í,î,ï,ĩ,ĭ,ǐ,į,ĳ,Ĵ,ĵ,Ĺ,Ľ,Ŀ,ĺ,ľ,ŀ,Ñ,ñ,ŉ,Ò,Ô,Õ,Ō,Ŏ,Ǒ,Ő,Ơ,Ø,Ǿ,Œ,ò,ô,õ,ō,ŏ,ǒ,ő,ơ,ø,ǿ,º,œ,Ŕ,Ŗ,ŕ,ŗ,Ŝ,Ș,ŝ,ș,ſ,Ţ,Ț,Ŧ,Þ,ţ,ț,ŧ,þ,Ù,Ú,Û,Ũ,Ŭ,Ű,Ų,Ư,Ǔ,Ǖ,Ǘ,Ǚ,Ǜ,ù,ú,û,ũ,ŭ,ű,ų,ư,ǔ,ǖ,ǘ,ǚ,ǜ,Ŵ,ŵ,Ý,Ÿ,Ŷ,ý,ÿ,ŷ,Ъ,Ь,А,Б,Ц,Ч,Д,Е,Ё,Э,Ф,Г,Х,И,Й,Я,Ю,К,Л,М,Н,О,П,Р,С,Ш,Щ,Т,У,В,Ы,З,Ж,ъ,ь,а,б,ц,ч,д,е,ё,э,ф,г,х,и,й,я,ю,к,л,м,н,о,п,р,с,ш,щ,т,у,в,ы,з,ж,Ä,Ö,Ü,ß,ä,ö,ü,Ç,Ğ,İ,Ş,ç,ğ,ı,ş,Ā,Ē,Ģ,Ī,Ķ,Ļ,Ņ,Ū,ā,ē,ģ,ī,ķ,ļ,ņ,ū,Ґ,І,Ї,Є,ґ,і,ї,є,Č,Ď,Ě,Ň,Ř,Š,Ť,Ů,Ž,č,ď,ě,ň,ř,š,ť,ů,ž,Ą,Ć,Ę,Ł,Ń,Ó,Ś,Ź,Ż,ą,ć,ę,ł,ń,ó,ś,ź,ż,Α,Β,Γ,Δ,Ε,Ζ,Η,Θ,Ι,Κ,Λ,Μ,Ν,Ξ,Ο,Π,Ρ,Σ,Τ,Υ,Φ,Χ,Ψ,Ω,Ϊ,Ϋ,ά,έ,ή,ί,ΰ,α,β,γ,δ,ε,ζ,η,θ,ι,κ,λ,μ,ν,ξ,ο,π,ρ,ς,σ,τ,υ,φ,χ,ψ,ω,ϊ,ϋ,ό,ύ,ώ,ϐ,ϑ,ϒ,أ,ب,ت,ث,ج,ح,خ,د,ذ,ر,ز,س,ش,ص,ض,ط,ظ,ع,غ,ف,ق,ك,ل,م,ن,ه,و,ي,ạ,ả,ầ,ấ,ậ,ẩ,ẫ,ằ,ắ,ặ,ẳ,ẵ,ẹ,ẻ,ẽ,ề,ế,ệ,ể,ễ,ị,ỉ,ọ,ỏ,ồ,ố,ộ,ổ,ỗ,ờ,ớ,ợ,ở,ỡ,ụ,ủ,ừ,ứ,ự,ử,ữ,ỳ,ỵ,ỷ,ỹ,Ạ,Ả,Ầ,Ấ,Ậ,Ẩ,Ẫ,Ằ,Ắ,Ặ,Ẳ,Ẵ,Ẹ,Ẻ,Ẽ,Ề,Ế,Ệ,Ể,Ễ,Ị,Ỉ,Ọ,Ỏ,Ồ,Ố,Ộ,Ổ,Ỗ,Ờ,Ớ,Ợ,Ở,Ỡ,Ụ,Ủ,Ừ,Ứ,Ự,Ử,Ữ,Ỳ,Ỵ,Ỷ,Ỹ'.split(',');
                to = '1,2,3,0,ae,ae,A,A,A,A,A,A,A,A,AE,AE,a,a,a,a,a,a,a,a,a,at,C,C,c,c,c,Dj,D,dj,d,E,E,E,E,E,E,e,e,e,e,e,e,f,G,G,g,g,H,H,h,h,I,I,I,I,I,I,I,I,IJ,i,i,i,i,i,i,i,i,ij,J,j,L,L,L,l,l,l,N,n,n,O,O,O,O,O,O,O,O,O,O,OE,o,o,o,o,o,o,o,o,o,o,o,oe,R,R,r,r,S,S,s,s,s,T,T,T,TH,t,t,t,th,U,U,U,U,U,U,U,U,U,U,U,U,U,u,u,u,u,u,u,u,u,u,u,u,u,u,W,w,Y,Y,Y,y,y,y,,,A,B,C,Ch,D,E,E,E,F,G,H,I,J,Ja,Ju,K,L,M,N,O,P,R,S,Sh,Shch,T,U,V,Y,Z,Zh,,,a,b,c,ch,d,e,e,e,f,g,h,i,j,ja,ju,k,l,m,n,o,p,r,s,sh,shch,t,u,v,y,z,zh,AE,OE,UE,ss,ae,oe,ue,C,G,I,S,c,g,i,s,A,E,G,I,K,L,N,U,a,e,g,i,k,l,n,u,G,I,Ji,Ye,g,i,ji,ye,C,D,E,N,R,S,T,U,Z,c,d,e,n,r,s,t,u,z,A,C,E,L,N,O,S,Z,Z,a,c,e,l,n,o,s,z,z,A,B,G,D,E,Z,E,Th,I,K,L,M,N,X,O,P,R,S,T,Y,Ph,Ch,Ps,O,I,Y,a,e,e,i,Y,a,b,g,d,e,z,e,th,i,k,l,m,n,x,o,p,r,s,s,t,y,ph,ch,ps,o,i,y,o,y,o,b,th,Y,a,b,t,th,g,h,kh,d,th,r,z,s,sh,s,d,t,th,aa,gh,f,k,k,l,m,n,h,o,y,a,a,a,a,a,a,a,a,a,a,a,a,e,e,e,e,e,e,e,e,i,i,o,o,o,o,o,o,o,o,o,o,o,o,u,u,u,u,u,u,u,y,y,y,y,A,A,A,A,A,A,A,A,A,A,A,A,E,E,E,E,E,E,E,E,I,I,O,O,O,O,O,O,O,O,O,O,O,O,U,U,U,U,U,U,U,Y,Y,Y,Y'.split(',');
                for (var i = 0, len = from.length; i < len; ++i) {
                    input = input.replace(new RegExp(from[i], 'g'), to[i]);
                }
                input = input
                    .replace(/<.*?>|&(?:[a-z0-9]+|#[0-9]+|#x[a-f0-9]+);/gi, join) // remove HTML tag & HTML entity
                    .replace(new RegExp('[^' + exclude + ']', 'g'), join)
                    .replace(new RegExp(join + '+', 'g'), join)
                    .replace(new RegExp('^' + join + '|' + join + '$', 'g'), "");
                return input;
            },

            // File
            file: {
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
            }
        };

        // Hook Add
        _.add = function(name, fn, stack) {
            stack = stack || 10;
            if (typeof _.hook[name] === "undefined") {
                _.hook[name] = [];
            }
            _.hook[name].push({
                'fn': fn,
                'stack': stack
            });
        };

        // Hook Fire
        _.fire = function(name, arguments) {
            if (typeof _.hook[name] === "object") {
                _.hook[name].sort(function(a, b) {
                    return a.stack - b.stack;
                });
                for (var i = 0, len = _.hook[name].length; i < len; ++i) {
                    _.hook[name][i].fn(arguments);
                }
            } else {
                _.hook[name] = [];
            }
        };

        // Hook Eject
        _.eject = function(name, stack) {
            if (typeof _.hook[name] !== "undefined") {
                if (typeof stack !== "undefined") {
                    for (var i = 0, len = _.hook[name].length; i < len; ++i) {
                        if (_.hook[name][i].stack === stack) {
                            delete _.hook[name][i];
                        }
                    }
                } else {
                    delete _.hook[name];
                }
            } else {
                _.hook = [];
            }
        };

        // Hook Exist
        _.exist = function(name, fallback) {
            fallback = fallback || false;
            if (typeof name === "undefined") {
                return Object.keys(_.hook).length ? _.hook : fallback;
            }
            return typeof _.hook[name] !== "undefined" ? _.hook[name] : fallback;
        };

    };

    window.DASHBOARD = new D();

})();