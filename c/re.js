var m = window.location.href,s = m.split('#'),rh = m,nowpage = 0,pagetype = 'normal',cpage = 1,nextkey,state = true,x = new Array();
document.getElementById('l').style.display = 'none'; /*预加载页头页尾*/
getp('header', 'h'); /*页头*/
getp('footer', 'f'); /*页尾*/
if (s[1] !== null && s[1] !== undefined && s[1] !== '') {
	state = false;
	if (s[1].indexOf('!') !== -1) { /*如果是文章或者页面*/
		pagetype = 'popage';
	} else if (s[1].indexOf('?') !== -1) { /*如果是搜索*/
		pagetype = 'search';
	} else {
		pagetype = 'normal';
	}
	nowpage = 0;
	getp(s[1], 'c');
	nextkey = '';
	cpage = 1;
} else {
	window.location.href = m + '#m';
}
function purge(){localStorage.clear();}
function checkurl() {
	pagetype = 'normal';
	var i = window.location.href;
	if (i !== rh) {
		state = false;
		rh = i;
		var t = i.split('#');
		if (t[1].indexOf('!') !== -1) { /*如果是文章或者页面*/
			pagetype = 'popage';
		} else if (t[1].indexOf('?') !== -1) { /*如果是搜索*/
			pagetype = 'search';
		} else {
			pagetype = 'normal';
		}
		nowpage = 0;
		cpage = 1;
		getp(t[1], 'c');
	}
}
onhashchange = function() {
	checkurl();
};
setInterval(checkurl, 1000);

function getp(page, e) {
	var opg = page;
	if (x[page] !== undefined && x[page] !== null) {
		var apage = page.split('/');
		if (apage[0] == 'm') {
			if (apage[1] == null || apage[1] == '') {
				nowpage = 1;
			} else {
				nowpage = parseInt(apage[1]) + 1;
			}
			cpage += 1;
		}
		var c = document.getElementById(e);
		c.style.opacity = 0;
		$('#' + e).html(x[page]);
		$('#' + e).animate({
			opacity: '1'
		});
	} else { /*预载入页码*/
		var cswitch = false;
		if (pagetype == 'normal') {
			var apage = page.split('/');
			if (apage[1] !== null && apage[1] !== undefined && apage[1] !== '' && apage[0] == 'm') {
				nowpage = Number(apage[1]);
				page = 'm';
			}
		} else if (pagetype == 'popage') {
			var apage = page.split('!');
			if (apage[1] !== null && apage[1] !== undefined && apage[1] !== '') {
				page = apage[1];
				var cswitch = true;
			}
		}
		document.getElementById('l').style.display = 'block';
		var c = document.getElementById(e);
		c.style.opacity = 0;
		var timestamp, cache;
		if (typeof localStorage['blog' + opg] == 'undefined') {
			localStorage['blog' + opg] = '';
		} else {
			timestamp = localStorage['blog' + opg].split('||CACHE||')[1];
			cache = localStorage['blog' + opg].split('||CACHE||')[0];
		}
		$.ajax({
			type: "post",
			url: './c/g.php?type=getpage',
			data: {
				p: page,
				load: nowpage,
				mode: pagetype,
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
								nowpage = 1;
							} else {
								nowpage = parseInt(apage[1]) + 1;
							}
							cpage += 1;
						}
						$('#' + e).html(cache);
						x[opg] = cache;
					} else {
						x[opg] = data.r; /*存入已加载区*/
						if(data.l!=='yes'){
						localStorage['blog' + opg] = data.r + '||CACHE||' + data.ca;
						}
						$('#' + e).html(data.r);
						if (data.r.match(/^[ ]+$/)) {
							$('#' + e).html('<center><h2 style=\'color:#AAA;\'>QAQ 404</h2></center>');
						}
						if (page.indexOf('m') !== -1) {
							allnum = data.allp;
							if ((Number(allnum) - 1) <= nowpage) { /*数组count比实际数量多1*/
								setTimeout(function() {
									$('#loadmore').remove();
								}, 10);
							}
							cpage += 1;
							nowpage += 1;
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

function getmore() { /*加载更多-函数*/
	cachepage = nowpage;
	if (cpage < 3) { /*自动换页*/
		$('#loadmore').remove();
		var e = 'c';
		var c = document.getElementById(e);
		c.style.opacity = 0;
		if (x['m' + nowpage] !== undefined && x['m' + nowpage] !== null) {
			//console.log('Cached Page >3');
			$('#' + e).html($('#' + e).html() + x['m' + nowpage]);
			$('#' + e).animate({
				opacity: '1'
			});
			nowpage += 1;
			cpage += 1;
		} else {
			document.getElementById('l').style.display = 'block';
			var timestamp;
			var cache;
			if (typeof localStorage['blogm' + cachepage] == 'undefined') {
				localStorage['blogm' + cachepage] = '';
			} else {
				timestamp = localStorage['blogm' + cachepage].split('||CACHE||')[1];
				cache = localStorage['blogm' + cachepage].split('||CACHE||')[0];
			}
			$.ajax({
				type: "post",
				url: './c/g.php?type=getmore',
				data: {
					load: nowpage,
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
							x['m' + cachepage] = cache;
							nowpage += 1;
							cpage += 1;
						} else {
							allnum = data.allp;
							if ((Number(allnum) - 1) <= nowpage) { /*数组count比实际数量多1*/
								data.r = data.r + '<script>setTimeout(function(){$(\'#loadmore\').remove();},10);</script>';
								console.log('No more.');
							} else {
								nowpage += 1;
							}
							cpage += 1;
							$('#' + e).html($('#' + e).html() + data.r);
							x['m' + cachepage] = data.r;
							if(data.l!=='yes'){
							localStorage['blogm' + cachepage] = data.r + '||CACHE||' + data.ca; /*加入本地缓存*/
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
		window.open('#m/' + nowpage, '_self');
	}
}
setTimeout(function() {
	console.clear();
	console.log('\n %c =3= OBottle  %c @SomeBottle 2018.8.24 \n\n','color:#484848;background:#ffffff;padding:5px 0;', 'color:#ffffff;background:#484848;padding:5px 0;');
}, 1000);