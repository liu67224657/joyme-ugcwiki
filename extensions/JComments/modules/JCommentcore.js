// 定义错误信息
window.commenterror = {
	'0'		:'系统错误',
	'-1000'	:'系统错误',
	'-1001'	:'缺少必传参数',
	'-10102':'登录过期，请重新登陆',
	'-40000':'缺少参数unikey',
	'-40001':'缺少参数domain',
	'-40002':'缺少参数jsonparam',
	'-40003':'参数body格式错误',
	'-40004':'缺少参数score',
	'-40005':'缺少参数body',
	'-40006':'参数oid错误',
	'-40007':'参数pid错误',
	'-40008':'评论对象不存在',
	'-40009':'主楼评论不存在或已删除',
	'-40010':'楼中楼上级回复不存在或已删除',
	'-40011':'缺少参数rid',
	'-40012':'参数rid错误',
	'-40013':'参数错误',
	'-40014':'一天只能评分20次',
	'-40015':'oid参数错误',
	'-40016':'已经赞过了',
	'-40017':'评论中含有敏感词',
	'-40018':'没有该条评论或评论已删除',
	'-40019':'用户被禁言',
	'-40020':'一分钟内不能发表相同内容',
	'-40022':'两次评论间隔不能少于15秒，请稍后再试'
};

/*Joyme PHP Comment Core Js*/
//JQ 扩展
(function ($) {
	$.fn.extend({
		insertAtCaret: function (myValue) {
			var $t = $(this)[0];
			if (document.selection) {
				this.focus();
				sel = document.selection.createRange();
				sel.text = myValue;
				this.focus();
			} else{
				if ($t.selectionStart || $t.selectionStart == '0') {
					var startPos = $t.selectionStart;
					var endPos = $t.selectionEnd;
					var scrollTop = $t.scrollTop;
					$t.value = $t.value.substring(0, startPos) + myValue + $t.value.substring(endPos, $t.value.length);
					this.focus();
					$t.selectionStart = startPos + myValue.length;
					$t.selectionEnd = startPos + myValue.length;
					$t.scrollTop = scrollTop;
				} else {
					this.value += myValue;
					this.focus();
				}
			}
		}
	});
})(jQuery);
/***** 定义对象 *****/ 
if(java_page_id && java_page_id>0){
	var joymecomment = {
			wikikey : window.wgWikiname,
			joymeapi: 'http://api.joyme.' + window.wgWikiCom,
			auth: 'http://passport.joyme.' + window.wgWikiCom,
			total: 1,
			domain: 1,
			psize : 10,
			flag:'hot',
			range:null
		};
}else{
	var joymecomment = {
			wikikey : window.wgWikiname,
			joymeapi: 'http://api.joyme.' + window.wgWikiCom,
			auth: 'http://passport.joyme.' + window.wgWikiCom,
			total: 1,
			domain: 6,
			psize : 10,
			flag:'hot',
			range:null
		};
}


/***** 初始化数据 *****/
joymecomment.init = function(){console.log('JS:', 'JT');
	JT.islogin = $("#joymelogin").val()=='true' ? true : false;
	JT.uid = JT.getCookie('jmuc_u');
	JT.JMdzids = JT.getData('JMdzids_'+JT.uid) ? JSON.parse(JT.getData('JMdzids_'+JT.uid)) : {'ids':[]};
	JT.pnum = 1; // 主楼当前页码
	JT.spnum = 1; // 子楼当前页码
	JT.imgnum = 5;// 可以上传的图片数
	JT.picdomain = window.wgWikiCom == 'alpha' ? 'http://joymetest.qiniudn.com/' : 'http://joymepic.joyme.com/';
	// UGC WIKI
	JT.uri = document.location.href;
	JT.title = typeof(mw) != 'undefined' ? mw.config.get('wgTitle') : window.title;
	if(java_page_id && java_page_id>0){
		JT.unikey = JT.wikikey + '|' + java_page_id+'.shtml';
	}else{
		JT.unikey = JT.wikikey + '|' + JT.title;
	}
	
	JT.jsonparam = {
		title: JT.title,
        pic: "",
        description: "",
        uri: JT.uri};
}

/***** 获取主楼数据 *****/
joymecomment.getCommentData = function(){
	var data = {unikey: JT.unikey, domain: JT.domain, jsonparam:JSON.stringify(JT.jsonparam), flag:JT.flag, pnum:JT.pnum, psize:JT.psize};
	if(!isNaN(JC.plid)){
		data.replyid = JC.plid;
	}
	$.ajax({
		url: JT.joymeapi + "/jsoncomment/reply/query",
		type: "post",
		async: false,
		data: data,
		dataType: "jsonp",
		jsonpCallback: "getCommentData",
		success: function (req) {
			var res = req[0];
			if(res.rs != 1){
				JC.errDialog(commenterror[res.rs], false);
			}else{
				if( JC.isWap == 1 && JT.pnum>1){
					JC.wapComments(res.result, JT.pnum);
				}else{
					JC.comments(res.result, JT.pnum);
				}
			}
		},
		error: function () {}
	});
}

/***** 获取子楼数据 *****/
joymecomment.getSubCommentData = function(oid){
	$.ajax({
		url: JT.joymeapi + "/jsoncomment/reply/sublist",
		type: "post",
		async: false,
		data: {unikey: JT.unikey, domain: JT.domain, oid:oid, pnum:JT.spnum, psize:JT.psize, ordertype:'desc'},
		dataType: "jsonp",
		jsonpCallback: "getSubCommentData",
		success: function (req) {
			var res = req[0];
			if(res.rs != 1){
				JC.errDialog(commenterror[res.rs], false);
			}else{
				if( JC.isWap == 1 && JT.spnum>1){
					JC.wapSubComments(res.result, oid);
				}else{
					JC.subComments(res.result, oid);
				}
			}
		},
		error: function () {}
	});
}

/***** 提交评论 *****/
joymecomment.postComment = function(body, oid, pid){
	$.ajax({
        url: JT.joymeapi + "/jsoncomment/reply/post",
        type: "post",
        async: false,
        data: {unikey: JT.unikey, domain: JT.domain, body: JSON.stringify(body), oid: oid, pid: pid},
        dataType: "jsonp",
        jsonpCallback: "postComment",
        success: function(req) {
			var res = req[0];
			if(res.rs != 1){
				JC.submitted = 0;
				JC.errDialog(commenterror[res.rs]);
			}else{
				var data = {'reply':res.result};
				JC.addComment(data, oid);
				JC.submitted = 0;
			}
		},
        error: function() {}
    });
}

/***** 删除评论 *****/
joymecomment.delComment = function(data,isReply){
	var rid = typeof(data) == 'object' ? data.rid : data;
	$.ajax({
        url: JT.joymeapi + "/jsoncomment/reply/remove",
        type: "post",
        async: false,
        data: {unikey: JT.unikey, domain: JT.domain, rid: rid},
        dataType: "jsonp",
        jsonpCallback: "delComment",
        success: function(req) {
			var res = req[0];
			if(res.rs != 1){
				JC.errDialog(commenterror[res.rs], false);
			}else{
				JC.delComment(data);
				if(isReply == 'reply'){
					//更新热度排行榜
					JC.data.echotype = 4;
					JC.data.type = 1;
					JC.data.tuid = 1;
					JC.data.desc = 'desc';
					JC.data.cid = 1;
					JT.userCommentMsgData(JC.data);
				}
			}
		},
        error: function() {}
    });
}

/***** 评论点赞 *****/
joymecomment.agreeComment = function(rid){
	$.ajax({
        url: JT.joymeapi + "/jsoncomment/reply/agree",
        type: "post",
        async: false,
        data: {unikey: JT.unikey, domain: JT.domain, rid: rid},
        dataType: "jsonp",
        jsonpCallback: "agreeComment",
        success: function(req) {
            var res = req[0];
			if(res.rs != 1){
				JC.submitted = 0;
				JC.errDialog(commenterror[res.rs]);
			}else{
				JC.zanComment(rid);
				JC.submitted = 0;
			}
        },
        error: function() {}
    });
}

// 用户数据上报
joymecomment.getUserInfo = function(data){
	data.action = 'jcommentsuserinfo';
	data.format = 'json';
	$.ajax({
        url: window.wgServer + '/'+ window.wgWikiname + "/api.php",
        type: "get",
        async: false,
        data: data,
        dataType: "jsonp",
        jsonpCallback: "getuserinfo",
        success: function(req) {
            var res = JSON.parse(req.jcommentsuserinfo.res);
			if(res.rs != 0){
				JC.errDialog('getuserinfo error');
			}else{
				JC.userinfo = res.userinfo;
				if(data.type == 'name'){
					JC.upNoticeAT( res.userinfo );
				}
			}
        },
        error: function() {}
    });
}

// 用户关注信息
joymecomment.getUserFollow = function(uid,username){

	jQuery.post(
		mw.util.wikiScript(), {
			action: 'ajax',
			rs: 'wfUserUserIsFollow',
			rsargs: [uid]
		},
		function( data ) {
			var res = jQuery.parseJSON(data);
			if (res.rs){
				if(res.data==true){
					$('.userfollowstatus').html('取消关注');
					$('.userfollowstatus').addClass("followed");
				}else {
					$('.userfollowstatus').html('关注');
					$('.userfollowstatus').addClass("unfollow");
				}
				$('.userfollowstatus').data('uid',username);
			}else{
				mw.ugcwikiutil.autoCloseDialog(res.message);
			}
		}
	);
};

// 关注用户
joymecomment.userUserFollow = function(uid,fid){
	$.ajax({
        url: 'http://api.joyme.'+env+"/api/usercenter/relation/follow",
        type: "post",
        async: false,
        data: {destprofileid: fid},
        dataType: "jsonp",
        jsonpCallback: "callback",
        success: function (data) {
            if (data != "") {
                var sendResult = data[0];
                var rs = sendResult.rs;
                if (rs == "1") {
                    if (sendResult.result == 'e') {
                        $('.userfollowstatus').html('互相关注');
                    } else if (sendResult.result == 't') {
                        $('.userfollowstatus').html('已关注');
                    }
                    $('.userfollowstatus').removeClass("unfollow");
                    mw.ugcwikiutil.autoCloseDialog("关注成功");
                } else if (rs == "-1001") {
                    mw.ugcwikiutil.msgDialog('参数异常');
                } else if (rs == "-10128") {
                    mw.ugcwikiutil.msgDialog('对方不允许关注');
                } else if (rs == "-1000") {
                    mw.ugcwikiutil.msgDialog('系统错误');
                }
            }else{
            	mw.ugcwikiutil.msgDialog('系统错误');
            }
        }, error: function () {
        	mw.ugcwikiutil.msgDialog('系统错误');
        }
    });
};
// 取消关注
joymecomment.userUserUnFollow = function(uid,fid){
	$.ajax({
        url: UserInfo.unfollowapiurl,
        type: "post",
        async: false,
        data: {destprofileid: fid},
        dataType: "jsonp",
        jsonpCallback: "callback",
        success: function (data) {
            if (data != "") {
                var sendResult = data[0];
                var rs = sendResult.rs;
                if (rs == "1") {
                    $('.userfollowstatus').html('关注');
                    mw.ugcwikiutil.autoCloseDialog("取消关注成功");
                    $('.userfollowstatus').removeClass("followed");
                } else if (rs == "-1001") {
                	mw.ugcwikiutil.msgDialog('参数错误');
                } else if (rs == "-1000") {
                	mw.ugcwikiutil.msgDialog('系统错误');
                }
            }
        }, error: function () {
        	mw.ugcwikiutil.msgDialog('系统错误');
        }
    });
};

// 评论上报
joymecomment.userCommentMsgData = function(data){
	data.title = mw.config.get('wgTitle');
	data.action = 'jcomments';
	data.format = 'json';
	data.uid = mw.config.get('wgUserId');
	data.uname = mw.config.get('wgUserName');
	data.pageid = mw.config.get('wgArticleId');

	$.ajax({
        url: window.wgServer + '/'+ window.wgWikiname + "/api.php",
        type: "get",
        async: false,
        data: data,
        dataType: "jsonp",
        jsonpCallback: "usercommentdatacount",
        success: function(req) {
            var res = req.jcomments;
			if(res.ok != 'ok'){
				JC.errDialog('usercommentdatacount error');
			}else{
				// JC.zanComment(rid);
				console.log('AT-req:', req);
			}
        },
        error: function() {}
    });
}

// 光标位置插入
joymecomment.pasteHtmlAtCaret = function(html, selectPastedContent) {
    var sel, range;
    if (window.getSelection) {
        // IE9 and non-IE
        sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
            range = sel.getRangeAt(0);
			range = JT.range != null ? JT.range : sel.getRangeAt(0);
            range.deleteContents();

            // Range.createContextualFragment() would be useful here but is
            // only relatively recently standardized and is not supported in
            // some browsers (IE9, for one)
            var el = document.createElement("div");
            el.innerHTML = html;
            var frag = document.createDocumentFragment(), node, lastNode;
            while ( (node = el.firstChild) ) {
                lastNode = frag.appendChild(node);
            }
            var firstNode = frag.firstChild;
            range.insertNode(frag);
            
            // Preserve the selection
            if (lastNode) {
                range = range.cloneRange();
                range.setStartAfter(lastNode);
                if (selectPastedContent) {
                    range.setStartBefore(firstNode);
                } else {
                    range.collapse(true);
                }
                sel.removeAllRanges();
                sel.addRange(range);
            }
        }
    } else if ( (sel = document.selection) && sel.type != "Control") {
        // IE < 9
        var originalRange = sel.createRange();
        originalRange.collapse(true);
        sel.createRange().pasteHTML(html);
        if (selectPastedContent) {
            range = sel.createRange();
            range.setEndPoint("StartToStart", originalRange);
            range.select();
        }
    }
}

joymecomment.setEndOfContenteditable = function(contentEditableElement){
    var range,selection;
    if(document.createRange)//Firefox, Chrome, Opera, Safari, IE 9+
    {
        range = document.createRange();//Create a range (a range is a like the selection but invisible)
        range.selectNodeContents(contentEditableElement);//Select the entire contents of the element with the range
        range.collapse(false);//collapse the range to the end point. false means collapse to end rather than the start
        selection = window.getSelection();//get the selection object (allows you to change selection)
        selection.removeAllRanges();//remove any selections already made
        selection.addRange(range);//make the range you have just created the visible selection
    }
    else if(document.selection)//IE 8 and lower
    { 
        range = document.body.createTextRange();//Create a range (a range is a like the selection but invisible)
        range.moveToElementText(contentEditableElement);//Select the entire contents of the element with the range
        range.collapse(false);//collapse the range to the end point. false means collapse to end rather than the start
        range.select();//Select the range (make it the visible selection
    }
}

/***** html5 本地存储 *****/
joymecomment.setData = function(key, val) {
    if (!window.localStorage || !key || !val) return false;
    window.localStorage.setItem(key, val);
}

joymecomment.getData = function(key) {
    if (!window.localStorage || !key) return false;
	return window.localStorage.getItem(key);
}

/********提取数字********/
joymecomment.getNum = function(str){
	var data = str.match(/(\d+)/g);
	return data != null ? data[0] : 0;
}

/***** 检查数据 *****/
joymecomment.checkText = function(content){
	content = $.trim(content.replace(/&nbsp;/g, ''));
	content = content.replace(/<br\/>/g, '');
	var preg = new RegExp(/^\[.*\]$/);
	var onlyemoji = preg.test(content);
	content = content.replace(/(\[.*?\])/g, '');
	var imgPreg = new RegExp(/<img.*?\>/gim);
	var hasImg = false;
	var s = content.match(preg);
	if(imgPreg.test(content)){
		content = content.replace(imgPreg, '');
		hasImg = true;
	}
	
	contentLen = content.length;
	if(onlyemoji){
		return true;
	}else if(!hasImg && (contentLen == 0 || content == '<br/>')){
		JC.errDialog('评论内容不能为空', false);
		return false;
	}else if(contentLen > 300){
		JC.errDialog('评论内容不能超过300字', false);
		return false;
	}
	return true;
}

/***** 评论上报 *****/
/***** 评论分页 *****/
joymecomment.pageBreak = function(data, oid ,comentSum){
	var html = '';
	if(data==null || data.maxPage<2){
		return html;
	}

	var pagen = {'oid':oid};
	
	var start = data.curPage - 4;
	if(start < 1){
		start = 1;
	}
	
	var end = data.curPage + 5;
	if(start == 1){
		end = 10;
	}
	if(end > data.maxPage){
		end = data.maxPage;
	}
	
	var arr = [];
	for(var i = start; i <= end; i++){
		if(i == data.curPage)
			arr.push('<a href="javascript:;" class="on">'+i+'</a>');
		else
			arr.push('<a href="javascript:;" class="plPage" data-pno="'+ i +'" data-poid="'+ oid +'">'+i+'</a>');
	}
	
	html = arr.join('');
	if(data.maxPage == 2 && !JC.isWap){
		return '<div class="paging1">'+html+'</div>';
	}
	
	// var prev = '<a href="javascript:void(0);">上一页</a>';
	// var next = '<a href="javascript:void(0);">下一页</a>';
	var prev = '';
	var next = '';
	if(data.curPage > 1){
		prev = '<a href="javascript:;" class="plPage" data-pno="'+ (data.curPage-1) +'" data-poid="'+ oid +'">上一页</a>';
	}
	if(data.curPage < data.maxPage){
		next = '<a href="javascript:;" class="plPage" data-pno="'+ (data.curPage+1) +'" data-poid="'+ oid +'">下一页</a>';
	}
	var first = '';
	if(!data.firstPage){
		first = '<a href="javascript:;" class="last plPage" data-pno="1" data-poid="'+ oid +'">首页</a>';
	}
	var last = '';
	if(!data.lastPage){
		last = '<a href="javascript:;" class="last plPage" data-pno="'+ data.maxPage +'" data-poid="'+ oid +'">末页</a>';
	}
	//html = '<div class="paging1">'+first+prev+html+next+last+'<a href="javascript:" class="count-num">共<b>'+ data.maxPage +'</b>页<b>'+ comentSum +'</b>条</a></div>';
	html = '<div class="paging1">'+first+prev+html+next+last+'<a href="javascript:" class="count-num">共<b>'+ data.maxPage +'</b>页</a></div>';
	
	html += '<div class="load-more" data-pno="2"><a href="javascript:;">加载更多</a></div>';
	
	return html;
}

joymecomment.subPageBreak = function(data, oid){
	var html = '';
	if(data==null || data.maxPage<2){
		return html;
	}
	
	var pagen = {'oid':oid};
	
	var start = data.curPage - 4;
	if(start < 1){
		start = 1;
	}
	
	var end = data.curPage + 5;
	if(start == 1){
		end = 10;
	}
	if(end > data.maxPage){
		end = data.maxPage;
	}
	
	var arr = [];
	for(var i = start; i <= end; i++){
		if(i == data.curPage)
			arr.push('<a href="javascript:;" class="on">'+i+'</a>');
		else
			arr.push('<a href="javascript:;" class="plPage" data-pno="'+ i +'" data-poid="'+ oid +'">'+i+'</a>');
	}
	
	html = arr.join('');
	if(data.maxPage == 2){
		return '<div class="paging-fy">'+html+'</div>';
	}
	
	var prev = '<a href="javascript:void(0);" class="pre">上一页</a>';
	var next = '<a href="javascript:void(0);" class="pre">下一页</a>';
	if(data.curPage > 1){
		prev = '<a href="javascript:;" class="pre plPage" data-pno="'+ (data.curPage-1) +'" data-poid="'+ oid +'">上一页</a>';
	}
	if(data.curPage < data.maxPage){
		next = '<a href="javascript:;" class="pre plPage" data-pno="'+ (data.curPage+1) +'" data-poid="'+ oid +'">下一页</a>';
	}
	html = '<div class="paging-fy">'+prev+html+next+'</div>';
	
	html += '<div class="load-more" data-pno="2"><a href="javascript:;">加载更多</a></div>';
	
	return html;
}

/***** 粘贴内容过滤*****/
joymecomment.parseFilter = function(str){
	str = str.replace(/<\/?(div|p|ul|li|br)[^>]*>|\n/ig, '<br/>').replace(/(<br\/>)*/gm, '$1').replace(/\r/gm, '');

	str = str.replace(/\r\n|\n|\r/ig, "");
    //remove html body form
    str = str.replace(/<\/?(html|body|form)(?=[\s\/>])[^>]*>/ig, "");
    //remove doctype
    str = str.replace(/<(!DOCTYPE)(\n|.)*?>/ig, "");
    //remove xml tags
    str = str.replace(/<(\/?(\?xml(:\w )?|xml|\w :\w )(?=[\s\/>]))[^>]*>/gi,"");
    //remove head
    str = str.replace(/<head[^>]*>(\n|.)*?<\/head>/ig, "");
    //remove <xxx /> 
    str = str.replace(/<(script|style|link|title|meta|textarea|option|select|iframe|hr)(\n|.)*?\/>/ig, "");
    //remove empty span
    str = str.replace(/<span[^>]*?><\/span>/ig, "");
    //remove <xxx>...</xxx>
    str = str.replace(/<(head|script|style|textarea|button|select|option|iframe)[^>]*>(\n|.)*?<\/\1>/ig, "");
    //remove table and <a> tag, <img> tag,<input> tag (this can help filter unclosed tag)
    str = str.replace(/<\/?(a|table|tr|td|tbody|thead|th|input|iframe|div|span|p)[^>]*>/ig, "");
    //remove bad attributes
    do {
        len = str.length;
        str = str.replace(/(<[a-z][^>]*\s)(?:id|name|language|type|class|on\w |\w :\w )=(?:"[^"]*"|\w )\s?/gi, "$1");
    } while (len != str.length);
	
	str = str.replace(/<\/?(div|p|ul|li|br)[^>]*>|\n/ig, '<br/>').replace(/(<br\/>)*/gm, '$1').replace(/\r/gm, '');
    
    return str;
}

/***** 错误提示 *****/
joymecomment.showError = function(data){
	if(typeof(commenterror) != 'undefined' && commenterror[data] != undefined){
		data = commenterror[data];
	}
}

/***** 获取cookie *****/
joymecomment.getCookie = function(objName) {
    var arrStr = document.cookie.split("; ");
    for (var i = 0; i < arrStr.length; i++) {
        var temp = arrStr[i].split("=");
        if (temp[0] == objName && temp[1] != '\'\'' && temp[1] != "\"\"") {
            return unescape(temp[1]);
        }
    }
    return null;
}

/**** 删除图片 ****/
joymecomment.delImage = function(url) {
	if(url == undefined || url == ''){
		return false;
	}
	var formhash = $('#formhash').val();
	$.ajax({
        url: window.wgPhpServer+'/public/qiniujs.php',
        type: "get",
        async: false,
        data: {'do': 'del', 'file': url, 'formhash':formhash},
        dataType: "jsonp",
        jsonpCallback: "delimg",
        success: function(req) {
            
        },
        error: function() {}
    });
}

// 将特定标签替换为 <br/>
joymecomment.replaceBr = function (str){
	return str.replace(/<\/?(div|p|ul|li|br)[^>]*>|\n/ig, '<br/>').replace(/(<br\/>)*/gm, '$1').replace(/\r/gm, '').trim('<br\/>');
}

String.prototype.trim=function(s) { 
	var preg = new RegExp('(^('+s+')*)|(('+s+')*$)', 'g');
	return this.replace(preg, "");
}

// JQ 粘贴事件
$.fn.pasteEvents = function( delay ) {
    if (delay == undefined) delay = 20;
    return $(this).each(function() {
        var $el = $(this);
        $el.on("paste", function() {
            $el.trigger("prepaste");
            setTimeout(function() { $el.trigger("postpaste"); }, delay);
        });
    });
};

/***** 调用测试 *****/
window.JT = joymecomment;
// 设置
JT.init();