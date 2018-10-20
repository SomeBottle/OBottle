var m = window.location.href,
s = m.split('#'),
rh = m,
np = 0,
pt = 'normal',
cpage = 1,
nextkey,
state = true,
x = new Array();
document.getElementById('l').style.display = 'none';
/*预加载页头页尾*/
g('header', 'h');
/*页头*/
g('footer', 'f');
/*页尾*/
if (s[1] !== null && s[1] !== undefined && s[1] !== '') {
    state = false;
    if (s[1].indexOf('!') !== -1) {
        /*如果是文章或者页面*/
        pt = 'popage';
    } else if (s[1].indexOf('?') !== -1) {
        /*如果是搜索*/
        pt = 'search';
    } else {
        pt = 'normal';
    }
    np = 0;
    g(s[1], 'c');
    nextkey = '';
    cpage = 1;
} else {
    window.location.href = m + '#m';
}
function purge() {
    localStorage.removeItem('obottle');
}
function q(md, k, c, t, rt) {
    /*(mode,key,content,timestamp,readtime)*/
    /*初始化本地cache*/
    if (typeof localStorage.obottle == 'undefined') {
        localStorage.obottle = '{}';
    }
    var timestamp = 0,
    cache = '',
    caches = JSON.parse(localStorage.obottle),
    rs = new Array();
    if (typeof caches[k] !== 'undefined') {
        timestamp = caches[k].t;
        cache = caches[k].h;
    }
    if (md == 'w') {
        var caches = JSON.parse(localStorage.obottle);
        var cc = new Object();
        cc.h = c;
        cc.t = t;
        cc.rt = 0;
        /*使用缓存次数*/
        caches[k] = cc;
        try {
            localStorage.obottle = JSON.stringify(caches);
        } catch(e) {
            for (var d in caches) {
                if (Number(caches[d].rt) <= 8 || Number(t) - Number(caches[d].t) >= 172800) {
                    /*自动清理缓存空间*/
                    delete caches[d];
                }
            }
            localStorage.obottle = JSON.stringify(caches);
        }
    } else if (md == 'r') {
        rs['t'] = timestamp;
        rs['c'] = cache;
        return rs;
    } else if (md == 'e') {
        var caches = JSON.parse(localStorage.obottle);
        caches[k].rt = Number(caches[k].rt) + rt;
        localStorage.obottle = JSON.stringify(caches);
    }
}
function cu() {
    pt = 'normal';
    var i = window.location.href;
    if (i !== rh) {
        state = false;
        rh = i;
        var t = i.split('#');
        if (t[1].indexOf('!') !== -1) {
            /*如果是文章或者页面*/
            pt = 'popage';
        } else if (t[1].indexOf('?') !== -1) {
            /*如果是搜索*/
            pt = 'search';
        } else {
            pt = 'normal';
        }
        np = 0;
        cpage = 1;
        g(t[1], 'c');
    }
}
onhashchange = function() {
    cu();
};
setInterval(cu, 1000);
function g(page, e) {
    var opg = page;
    if (x[page] !== undefined && x[page] !== null) {
        var apage = page.split('/');
        if (apage[0] == 'm') {
            if (apage[1] == null || apage[1] == '') {
                np = 1;
            } else {
                np = parseInt(apage[1]) + 1;
            }
            cpage += 1;
        }
        var c = document.getElementById(e);
        c.style.opacity = 0;
        $('#' + e).html(x[page]);
        $('#' + e).animate({
            opacity: '1'
        });
    } else {
        /*预载入页码*/
        var cswitch = false;
        if (pt == 'normal') {
            var apage = page.split('/');
            if (apage[1] !== null && apage[1] !== undefined && apage[1] !== '' && apage[0] == 'm') {
                np = Number(apage[1]);
                page = 'm';
            }
        } else if (pt == 'popage') {
            var apage = page.split('!');
            if (apage[1] !== null && apage[1] !== undefined && apage[1] !== '') {
                page = apage[1];
                var cswitch = true;
            }
        }
        document.getElementById('l').style.display = 'block';
        var c = document.getElementById(e);
        c.style.opacity = 0;
        var cache = q('r', 'b' + opg, '', '', '')['c'];
        var timestamp = q('r', 'b' + opg, '', '', '')['t'];
        $.ajax({
            type: "post",
            url: './c/g.php?type=getpage',
            data: {
                p: page,
                load: np,
                mode: pt,
                ts: timestamp
            },
            dataType: "text",
            success: function(msg) {
                var datat = '';
                if (msg != '') {
                    datat = eval("(" + msg + ")");
                }
                data = datat;
                if (data.result == 'ok') {
                    if (data.cm == 'cache') {
                        var apage = opg.split('/');
                        if (apage[0] == 'm') {
                            if (apage[1] == null || apage[1] == '') {
                                np = 1;
                            } else {
                                np = parseInt(apage[1]) + 1;
                            }
                            cpage += 1;
                        }
                        $('#' + e).html(cache);
                        x[opg] = cache;
                        q('e', 'b' + opg, '', '', 1);
                    } else {
                        x[opg] = data.r;
                        /*存入已加载区*/
                        if (data.l !== 'yes') {
                            q('w', 'b' + opg, data.r, data.ca, '');
                        }
                        $('#' + e).html(data.r);
                        if (data.r.match(/^[ ]+$/)) {
                            $('#' + e).html('<center><h2 style=\'color:#AAA;\'>QAQ 404</h2></center>');
                        }
                        if (page.indexOf('m') !== -1) {
                            allnum = data.allp;
                            if ((Number(allnum) - 1) <= np) {
                                /*数组count比实际数量多1*/
                                setTimeout(function() {
                                    $('#loadmore').remove();
                                },
                                10);
                            }
                            cpage += 1;
                            np += 1;
                        }
                    }
                } else {
                    $('#' + e).html('<center><h2 style=\'color:#AAA;\'>' + data.msg + '</h2></center>');
                }
                $('#' + e).animate({
                    opacity: '1'
                });
                document.getElementById('l').style.display = 'none';
                state = true;
            },
            error: function(msg) {
                $('#' + e).html('<center><h2 style=\'color:#AAA;\'>失去连接~OAO</h2></center>');
                state = true;
            }
        });
    }
}
function getmore() {
    /*加载更多-函数*/
    var cp = np;
    if (cpage < 3) {
        /*自动换页*/
        $('#loadmore').remove();
        var e = 'c';
        var c = document.getElementById(e);
        c.style.opacity = 0;
        if (x['m' + np] !== undefined && x['m' + np] !== null) {
            $('#' + e).html($('#' + e).html() + x['m' + np]);
            $('#' + e).animate({
                opacity: '1'
            });
            np += 1;
            cpage += 1;
        } else {
            document.getElementById('l').style.display = 'block';
            var cache = q('r', 'b' + cp, '', '', '')['c'];
            var timestamp = q('r', 'b' + cp, '', '', '')['t'];
            $.ajax({
                type: "post",
                url: './c/g.php?type=getmore',
                data: {
                    load: np,
                    ts: timestamp
                },
                dataType: "text",
                success: function(msg) {
                    var datat = '';
                    if (msg != '') {
                        datat = eval("(" + msg + ")");
                    }
                    data = datat;
                    if (data.result == 'ok') {
                        if (data.cm == 'cache') {
                            $('#' + e).html($('#' + e).html() + cache);
                            x['m' + cp] = cache;
                            q('e', 'b' + cp, '', '', 1);
                            np += 1;
                            cpage += 1;
                        } else {
                            allnum = data.allp;
                            if ((Number(allnum) - 1) <= np) {
                                /*数组count比实际数量多1*/
                                data.r = data.r + '<script>setTimeout(function(){$(\'#loadmore\').remove();},10);</script>';
                                console.log('No more.');
                            } else {
                                np += 1;
                            }
                            cpage += 1;
                            $('#' + e).html($('#' + e).html() + data.r);
                            x['m' + cp] = data.r;
                            if (data.l !== 'yes') {
                                q('w', 'b' + cp, data.r, data.ca, '');
                            }
                        }
                    } else {
                        document.getElementById(e).innerHTML = document.getElementById(e).innerHTML + '<center><h2 style=\'color:#AAA;\'>' + data.msg + '</h2></center>';
                    }
                    $('#' + e).animate({
                        opacity: '1'
                    });
                    document.getElementById('l').style.display = 'none';
                    state = true;
                },
                error: function(msg) {
                    alert('加载失败');
                    state = true;
                }
            });
        }
    } else {
        cpage = 0;
        window.open('#m/' + np, '_self');
    }
}
setTimeout(function() {
    console.log('\n %c =3= OBottle  %c @SomeBottle 2018.10.20 \n\n', 'color:#484848;background:#ffffff;padding:5px 0;', 'color:#ffffff;background:#484848;padding:5px 0;');
},
1000);