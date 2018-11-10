/*Scripts For Request - SomeBottle*/
var $ = new Object();
$.ls = new Array();
var SC = function(e) {
    return document.getElementById(e);
}
$.script=function (url) {
    var script = document.createElement("script");
    var exist = false;
    for (var up in $.ls) {
        if ($.ls[up] == url) {
            exist = true;
            break;
        }
    }
    if (!exist) {
        $.ls[$.ls.length] = url;
        script.type = "text/javascript";
        script.src = url;
        document.body.appendChild(script);
    }
}
$.op = function(v, e) {
    ht = SC(e);
    if (Number(v) == 1) {
        ht.style.transition = '0.5s ease';
        ht.style.opacity = '1';
    } else {
        ht.style.transition = 'none';
        ht.style.opacity = '0';
    }
}
$.rm = function(e) {
    SC(e).parentNode.removeChild(SC(e));
}
$.ht = function(h, e) {
    ht = SC(e);
    ht.innerHTML = h;
    os = ht.getElementsByTagName('script');
    var scr = '';
    for (var o = 0; o < os.length; o++) {
        scr = scr + os[o].innerHTML;
        if (os[o].src !== undefined && os[o].src !== null && os[o].src !== '') {
            $.script(os[o].src);
        }
    }
    var ns = document.createElement('script');
    ns.innerHTML = scr;
    ht.appendChild(ns);
    for (var o = 0; o < os.length; o++) {
        os[o].parentNode.removeChild(os[o]);
    }
}
$.aj = function(p, d, sf) {
    /*(path,data,success or fail)*/
    var xhr = new XMLHttpRequest();
    var hm = '';
    for (var ap in d) {
        hm = hm + ap + '=' + d[ap] + '&';
    }
    xhr.open('post', p, true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send(hm);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            sf.success(xhr.responseText);
        } else if (xhr.readyState == 4 && xhr.status !== 200) {
            sf.failed();
        }
    };
}