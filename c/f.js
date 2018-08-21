var timer = false;
var ct = 0;
var t, c, times = 0,
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
    $('#btn').html('----' + ct + '----');
}
function act(step) {
    if (step == 1) {
        $('#btn').html('编辑/发布');
        submits();
    } else if (step == 2) {
        $('#btn').html('预览');
        previews();
    } else if (step == 3) {
        upload();
        $('#btn').html('上传图片');
    } else if (step == 5) {
        $('#btn').html('保存草稿(本地)成功');
        localStorage.edittitle = document.getElementById("t").value;
        localStorage.editcontent = document.getElementById("c").value;
        localStorage.editdal = document.getElementById("d").value;
        localStorage.edittag = document.getElementById("a").value;
    } else if (step == 4) {
        $('#btn').html('读取草稿');
        if (confirm('你真的要读取草稿吗？O_o\n这会覆盖你现在的内容.')) {
            document.getElementById("t").value = localStorage.edittitle;
            document.getElementById("c").value = localStorage.editcontent;
            document.getElementById("d").value = localStorage.editdal;
            document.getElementById("a").value = localStorage.edittag;
        };
    } else if (step == 6) {
        $('#btn').html('登出');
        if (confirm('你真的要登出嘛O_o')) {
            console.log('logout.');
            window.open('?t=out', '_self');
        };
    } else if (step >= 7) {
        $('#btn').html('取消');
    }
    setTimeout(function() {
        $('#btn').html('(O_o)?');
    },
    1000);
}
function upload() {
    document.getElementById("fileinfo").style.display = 'block';
}
document.getElementById('fileinfo').onchange = function() {
    console.log("submit pic");
    var fd = new FormData(document.getElementById("fileinfo"));
    $('#btn').html('正在上传');
    fd.append("label", "WEBUPLOAD");
    $.ajax({
        url: "https://sm.ms/api/upload",
        type: "POST",
        data: fd,
        enctype: 'multipart/form-data',
        processData: false,
        // tell jQuery not to process the data
        contentType: false // tell jQuery not to set contentType
    }).done(function(data) {
        $('#btn').html('上传完毕');
        mains = eval(data.data);
        document.getElementById("fileinfo").style.display = 'none';
        document.getElementById("c").value = document.getElementById("c").value + '&nbsp;&nbsp;\n![' + mains.filename + '](' + mains.url + ')';
    });
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
        $('#zt').html('正在发布...');
        $.ajax({
            type: "post",
            url: './../c/t.php?type=submit',
            data: {
                title: t,
                content: c,
                dat: d,
                tag: a,
                ifzd: zd,
                editn: editnum
            },
            dataType: "text",
            success: function(msg) {
                var datat = '';
                if (msg != '') {
                    datat = eval("(" + msg + ")");
                }
                data = datat;
                if (data.result == 'ok') {
                    alert('编辑/发布成功~\n文章/页面ID是' + data.pid);
                    window.open('?e=' + data.pid, '_self');
                    $('#zt').html('EDIT -v-');
                } else {
                    alert('编辑/发布失败QAQ~\n' + data.msg);
                    $('#zt').html('EDIT -v-');
                }
            },
            error: function(msg) {
                alert('失去了与服务器的连接OAO');
                if (confirm('需要保存草稿吗？')) {
                    localStorage.edittitle = document.getElementById("t").value;
                    localStorage.editcontent = document.getElementById("c").value;
                    localStorage.editdal = document.getElementById("d").value;
                    localStorage.edittag = document.getElementById("a").value;
                }
            }
        });
    }
}
function previews() {
    var a = document.getElementById('c').value;
    var wd;
    wd = window.open('', '_blank', '');
    var converter = new Markdown.Converter();
    var rhtml = converter.makeHtml(a);
    wd.document.write("<style>body{max-width:500px;}</style><body><link rel='stylesheet' href='<?php echo $bhost; ?>/theme/style.css'><p>-----------预览--------------</p>" + rhtml + '</body>');
    wd.document.close();
}