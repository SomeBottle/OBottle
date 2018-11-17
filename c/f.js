var timer = false;
var t, c, times = 0,
ct = 0,
lastrequest = 0;
function faq() {
    alert('主要内容在Edit页面的文本框里已经提示了~\n值得一说的是【日期/链接】这一栏\n填写日期的格式为20080101这样的格式，当你填写的是日期时，你发布的就是【文章】，当你填的是链接例如links或者不是日期的数字之类的时，就会生成一个页面.\n例如http://example.com/#links\n\n当你选择创建页面时可以不用管标签这一栏.');
    alert('最有灵性的要属底下的多功能键(O_o)?了.在单击或者多击的情况下实现不同功能.\n点击1次————发布/编辑\n点击2次————预览\n点击3次————上传图片\n点击4次————读取草稿\n点击5次————保存草稿\n点击6次————注销登录\n点击更多下取消.');
}
function edit() {
    if (timer == false) {
        times = 0;
        lastrequest = 0;
        timer = true;
        t = setInterval(function() {
            times += 0.5
        },
        500);
        c = setInterval(function() {
            if (times - lastrequest >= 1) {
                console.log('Chosen.');
                clearInterval(t);
                clearInterval(c);
                timer = false;
                act(ct);
                ct = 0;
            }
        },
        500);
    } else {
        lastrequest = times;
    }
    ct += 1;
    $.ht('----' + ct + '----', 'btn');
}
function act(step) {
    if (step == 1) {
        $.ht('编辑/发布', 'btn');
        submits();
    } else if (step == 2) {
        $.ht('预览', 'btn');
        previews();
    } else if (step == 3) {
        upload();
        $.ht('上传图片', 'btn');
    } else if (step == 5) {
        $.ht('保存草稿(本地)成功', 'btn');
        localStorage.edittitle = document.getElementById("t").value;
        localStorage.editcontent = document.getElementById("c").value;
        localStorage.editdal = document.getElementById("d").value;
        localStorage.edittag = document.getElementById("a").value;
    } else if (step == 4) {
        $.ht('读取草稿', 'btn');
        if (confirm('你真的要读取草稿吗？O_o\n这会覆盖你现在的内容.')) {
            document.getElementById("t").value = localStorage.edittitle;
            document.getElementById("c").value = localStorage.editcontent;
            document.getElementById("d").value = localStorage.editdal;
            document.getElementById("a").value = localStorage.edittag;
        };
    } else if (step == 6) {
        $.ht('登出', 'btn');
        if (confirm('你真的要登出嘛O_o')) {
            console.log('logout.');
            window.open('?t=out', '_self');
        };
    } else if (step >= 7) {
        $.ht('取消', 'btn');
    }
    setTimeout(function() {
        $.ht('(O_o)?', 'btn');
    },
    1000);
}
function upload() {
    document.getElementById("fileinfo").style.display = 'block';
}
document.getElementById('fileinfo').onchange = function() {
    var fd = new FormData(document.getElementById("fileinfo"));
    $.ht('正在上传', 'btn');
    fd.append("label", "WEBUPLOAD");
    $.aj("https://sm.ms/api/upload", fd, {
        success: function(msg) {
            if (msg != '') {
                msg = eval("(" + msg + ")");
            }
            $.ht('上传完毕', 'btn');
            mains = eval(msg.data);
            document.getElementById("fileinfo").style.display = 'none';
            document.getElementById("c").value = document.getElementById("c").value + '  \n![' + mains.filename + '](' + mains.url + ')';
        },
        failed: function(msg) {
            alert('上传失败');
        }
    },
    'multipart/form-data');
    return false;
}
function submits() {
    var t = document.getElementById("t").value;
    var c = document.getElementById("c").value;
    var d = document.getElementById("d").value;
    var a = document.getElementById("a").value;
    var zd = 'no';
    if (document.getElementById('zd').checked) {
        zd = 'yes';
    }
    if (confirm('你真的要发布/编辑嘛O_o')) {
        $.ht('正在发布', 'zt');
		c=encodeURIComponent(c);
        $.aj('./../c/t.php?type=submit', {
            title: t,
            content: c,
            dat: d,
            tag: a,
            ifzd: zd,
            editn: editnum
        },
        {
            success: function(msg) {
                var datat = '';
                if (msg != '') {
                    datat = eval("(" + msg + ")");
                }
                data = datat;
                if (data.result == 'ok') {
                    alert('编辑/发布成功~\n文章/页面ID是' + data.pid);
                    window.open('?e=' + data.pid, '_self');
                    $.ht('EDIT -v-', 'zt');
                } else {
                    alert('编辑/发布失败QAQ~\n' + data.msg);
                    $.ht('EDIT -v-', 'zt');
                }
            },
            failed: function(msg) {
                alert('失去了与服务器的连接OAO');
                if (confirm('需要保存草稿吗？')) {
                    localStorage.edittitle = document.getElementById("t").value;
                    localStorage.editcontent = document.getElementById("c").value;
                    localStorage.editdal = document.getElementById("d").value;
                    localStorage.edittag = document.getElementById("a").value;
                }
            }
        },
        '');
    }
}
function previews() {
    var a = document.getElementById('c').value;
    var wd;
    wd = window.open('', '_blank', '');
    var converter = new Markdown.Converter();
    var rhtml = converter.makeHtml(a);
    wd.document.write("<style>body{max-width:500px;}</style><body><p>-----------预览--------------</p>" + rhtml + '</body>');
    wd.document.close();
}