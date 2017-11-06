// 讨论区帖子
var phpapi='http://'+window.wgWikiname+'.joyme.'+window.wgWikiCom;
document.write(unescape("%3Cscript src='"+phpapi+"/extensions/jsscripts/commenterror.js' type='text/javascript'%3E%3C/script%3E"));

$(function(){
	// window.joymeuser.uid = getCookie('jmuc_u');
	window.commenterror.loginHtml = '请先保存内容<a href="'+window.joymeapi.auth+'/auth/loginpage">登录</a>之后再行回复';
	window.commenterror.textEmpty = '内容不能为空';
	window.commenterror.textToLong = '内容不能超过300字';
	
	window.JpageId = $('#pageId').val();
	window.Jtoken = $('#mytoken').val();
	
	var namespace = $('#namespace').val();
	window.domain = 6;
	var title = $.trim($('#title').html());
	title = namespace==1000 ? '讨论区:'+title : title;
	var uri = document.location.href;
	window.uniKey = window.wgWikiname+'|'+title;
	// console.log('window.uniKey:', window.uniKey);return false;
	var body = {
            "text": '',
            "pic": ""
        }
	window.jsonParam = {
        "title": title,
        "pic": "",
        "description": "",
        "uri": uri
    };
	
	// 获取评论数据
	window.pnum = 1;
	window.subpnum = 1;
	tlqComment.getCommentList();
	
	// 发表评论
	$("#comment_submit").click(function(){
		var text = $('#textarea_body_0').html();
		var checkRes = tlqComment.checkFn(text);
		if(!checkRes){
			return false;
		}
		// 设置按钮不可点击
		var butttonDisable = $(this).attr('data-disable');
		if(!butttonDisable){
			$(this).attr('data-disable', true);
		}else{
			return false;
		}
		// post data
		body.text = $.trim(text.replace(/&nbsp;/g, ' '));
		tlqComment.postComment(body, 0, 0);
	});
	
	// 点赞后加样式
	var isAgree = localSorageData('get', 'article_'+window.joymeuser.uid+'_'+window.JpageId);
	if(isAgree){
		$('#articleAgree a').addClass('cur');
	}
	
	// 绑定文章点赞
	$('#articleAgree a').click(function(){
		var pageId = window.JpageId;
		var token = window.Jtoken;
		var isAgree = localSorageData('get', 'article_'+window.joymeuser.uid+'_'+pageId);
		if(!window.joymeIsLogin){
			alert('请先登录');
			return false;
		}else if(isAgree){
			alert('不能重复点赞');
			return false;
		}
		$.ajax({
			url: window.joymeapi.joymewiki+'?c=wikiPosts&a=setUpPriseNum',
			type: "post",
			async: false,
			data: {wikikey:window.wgWikiname, page_id:pageId, token:token},
			dataType: "jsonp",
			jsonpCallback: "articleAgreeCallback",
			success: function (req) {
				localSorageData('set', 'article_'+window.joymeuser.uid+'_'+pageId, pageId);
				var resMsg = req[0];
				if(resMsg.rs == 0){
					var agreeNum = parseInt($('#articleAgree a').html());
					$('#articleAgree a').html(++agreeNum);
					$('#articleAgree a').addClass('cur');
				}else{
					setErrMsg(resMsg.msg);
				}
			},
			error: function () {
				setErrMsg('articleAgree程序错误');
			}
		});
	});
	
	// 点击空白隐藏表情
	$(document).click(function(){
		if($('#biaoqing').css("display") != 'none'){
			$('#biaoqing').hide();
		}
	});
	
	// 点击表情框阻止隐藏
	$('#biaoqing').click(function(e){
		e.stopPropagation();
		$('#biaoqing').show();
	});
	
	// 发表图片
	$('#commentImg').change(function(){
		$('#edittoken').val(mw.user.tokens.get('editToken'));
		var file = $('#commentImg').val();
		if(file == ''){
			return false;
		}
		var text = '<div>'+$('#textarea_body_0').html()+'</div>';
		var imgNum = $(text).find('img').length;
		if(imgNum>=1){
			alert('评论只许上传一张图片');
			return false;
		}
		$('#imgForm').submit();
	});
	
	// 取消置顶
	$('#quxiaozhiding').click(function(){
		if(!confirm("确认要取消置顶？")) {
			return false;
		}
		var pageId = window.JpageId;
		var token = window.Jtoken;
		$.ajax({
			url: window.joymeapi.joymewiki+'?c=wikiPosts&a=UpdateIsTop',
			type: "post",
			async: false,
			data: {type:2, page_id:pageId, token:token, wikikey:window.wgWikiname},
			dataType: "jsonp",
			jsonpCallback: "quxiaozhidingfn",
			success: function (req) {
				var msg = req[0];
				if(msg.rs == 0){
					$('#quxiaozhiding').remove();
					alert('取消置顶成功');
				}
			},
			error: function () {
				setErrMsg('articleAgree程序错误');
			}
		});
	});
	
	// 取消加精
	$('#quxiaojiajing').click(function(){
		if(!confirm("确认要取消加精？")) {
			return false;
		}
		var pageId = window.JpageId;
		var token = window.Jtoken;
		$.ajax({
			url: window.joymeapi.joymewiki+'?c=wikiPosts&a=UpdateIsEssence',
			type: "post",
			async: false,
			data: {type:2, page_id:pageId, token:token, wikikey:window.wgWikiname},
			dataType: "jsonp",
			jsonpCallback: "quxiaojiajingfn",
			success: function (req) {
				var msg = req[0];
				if(msg.rs == 0){
					$('#quxiaojiajing').remove();
					alert('取消加精成功');
				}
			},
			error: function () {
				setErrMsg('quxiaojiajing程序错误');
			}
		});
	});
});

var tlqComment = {
	
	// 获取评论数据
	'getCommentList' : function(){
		var authorId = $('#uIcon').attr('data-uid');
		$.ajax({
			url: window.joymeapi.api+"jsoncomment/reply/query",
			type: "post",
			async: false,
			data: {unikey:window.uniKey, domain:window.domain, jsonparam:JSON.stringify(window.jsonParam), pnum:window.pnum, psize:10, ordertype:'asc', uid:authorId},
			dataType: "jsonp",
			jsonpCallback: "getList",
			success: function (req) {
				var resMsg = req[0];
				if(resMsg.rs == 1){
					var result = resMsg.result;
					if (result == null || result.mainreplys == null || result.mainreplys.rows.length == 0) {
						// setErrMsg('暂时没有回帖');
					}else{
						var html = '';
						for (var i = 0; i < result.mainreplys.rows.length; i++) {
							html += tlqComment.getCommentHtml(result.mainreplys.rows[i]);
						}
						var pageListHtml = tlqComment.getPageHtml(result.mainreplys.page, 0);
						$("#mainPageList").html(pageListHtml);
						$(".page_0").click(tlqComment.mainPage);
						$('#comment_list_area').html('');
						if(window.pnum != 1){
							$('.post-cont').hide();
						}else{
							$('.post-cont').show();
						}
						$("#comment_list_area").append(html);
						$.each(result.mainreplys.rows, function(i){
							$('.page_'+result.mainreplys.rows[i].reply.reply.rid).click(tlqComment.subPage);
						});
					}
					tlqComment.pageClick();
					$('#uIcon').attr('src', result.user.icon);
					$('#author').html(result.user.name);
				}else{
					setErrMsg('获取列表失败');
				}
			},
			error: function (XMLHttpRequest, textStatus, errorThrown ) {
				// console.log('XMLHttpRequest', XMLHttpRequest); 
				// console.log('textStatus', textStatus); 
				// console.log('errorThrown', errorThrown);
				setErrMsg('getCommentList程序错误');
			}
		});
	},
	
	// 拼接一个楼层html
	'getCommentHtml' : function(data){
		var dataField = {"rid":data.reply.reply.rid, "oid":0};
		var delHtml = '';
		if(window.joymeuser.uid == data.reply.user.uid){
			delHtml = '<a href="javascript:void(0);" class="post-reply-delet" data-field=\''+JSON.stringify(dataField)+'\'>删除</a>';
		}
		var isAgree = localSorageData('get', window.wgWikiname+'_'+data.reply.reply.rid);
		var agreeCss = '';
		if(isAgree){
			agreeCss = ' post-reply-like_cur';
		}
		var content = data.reply.reply.body.text.replace(/&lt;div&gt;/g,'<br>').replace(/&lt;\/div&gt;/g,'<br>').replace(/&lt;p&gt;/g,'<br>').replace(/&lt;\/p&gt;/g,'<br>').replace(/<br><br>/g,'<br>');

		var html = '<div class="post-cont-author" id="tId_'+data.reply.reply.rid+'"><cite><img src="'+data.reply.user.icon+'"></cite><div class="post-cont-author-tit">'+data.reply.user.name+'<em>'+(++data.reply.reply.floor_num)+'楼</em></div><div class="post-cont-main"><p>'+content+'</p></div><div class="post-reply-detail">'+ delHtml +'<a href="javascript:void(0);" class="post-reply-like'+agreeCss+'" data-rid="'+data.reply.reply.rid+'">（'+data.reply.reply.agree_sum+'）</a><span>'+data.reply.reply.post_date+'</span></div>';
		
		// 子评论
		var reCommentHtml = '';
		var rePageListHtml = '';
		var isShow = '';
		if(data.subreplys != null){
			for (var i = 0; i < data.subreplys.rows.length; i++) {
				if(i>2){
					isShow = ' ;display:none; ';
				}
				reCommentHtml += tlqComment.getReCommentHtml(data.subreplys.rows[i], isShow);
			}
			var pageHtml = tlqComment.getPageHtml(data.subreplys.page, data.reply.reply.rid);
			if(pageHtml){
				rePageListHtml = '<div class="pager" id="rePageDiv_'+data.reply.reply.rid+'" style="'+isShow+'">'+pageHtml+'</div>';
			}
			
			
			var isMore = '';
			if(data.reply.reply.sub_reply_sum - 3 > 0){
				isMore = '<div class="review-but"><span data-rid="'+data.reply.reply.rid+'"><a href="javascript:void(0);">查看更多</a>, 剩余'+(data.reply.reply.sub_reply_sum - 3)+'条</span></div>';
			}
			// 有回复展示回复框
			html += '<div class="post-reply-detail-cont"><a class="post-reply-open" data-subrnum="'+data.reply.reply.sub_reply_sum+'" data-rid="'+data.reply.reply.rid+'">收起回复</a><div class="post-comment-box"><div id="reComments_'+data.reply.reply.rid+'">'+reCommentHtml+'</div>'+rePageListHtml+'<div class="post-reply-review">'+isMore+'<div class="review-input" id="post_recomment_area_'+data.reply.reply.rid+'" style="'+isShow+'"><div id="textarea_body_'+data.reply.reply.rid+'" contenteditable="true"></div></div><div class="review-icon" style="'+isShow+'"><a href="javascript:void(0);" class="review-icon-phiz" data-oid="'+data.reply.reply.rid+'">表情</a><span id="errMsg'+data.reply.reply.rid+'"></span><button class="cancel recomment" data-rid="'+data.reply.reply.rid+'">评论</button></div></div></div></div></div>';
		}else{
			// 无回复隐藏回复框
			html += '<div class="post-reply-detail-cont"><a class="post-reply-open" data-subrnum="'+data.reply.reply.sub_reply_sum+'" data-rid="'+data.reply.reply.rid+'">回复</a><div class="post-comment-box" style="display:none;"><div id="reComments_'+data.reply.reply.rid+'">'+reCommentHtml+'</div>'+rePageListHtml+'<div class="post-reply-review"><div class="review-input" id="post_recomment_area_'+data.reply.reply.rid+'" style="'+isShow+'"><div id="textarea_body_'+data.reply.reply.rid+'" contenteditable="true"></div></div><div class="review-icon" style="'+isShow+'"><a href="javascript:void(0);" class="review-icon-phiz" data-oid="'+data.reply.reply.rid+'">表情</a><span id="errMsg'+data.reply.reply.rid+'"></span><button class="cancel recomment" data-rid="'+data.reply.reply.rid+'">评论</button></div></div></div></div></div>';
		}



		return html;
	},
	
	// 回复list
	'getReCommentHtml' : function(data, isShow){
		isShow = isShow || '';
		var dataField = {"pid":data.reply.rid, "oid":data.reply.oid, "author":data.user.name};
		var delHtml = '';
		var subDataField = {"rid":data.reply.rid, "oid":data.reply.oid};
		if(window.joymeuser.uid == data.user.uid){
			delHtml = '<a class="post-reply-delet" href="javascript:void(0);" data-field=\''+JSON.stringify(subDataField)+'\'>删除</a>';
		}
		var isAgree = localSorageData('get', window.wgWikiname+'_'+data.reply.rid);
		var agreeCss = '';
		if(isAgree){
			agreeCss = ' post-reply-like_cur';
		}
		var puserHtml = '';
		if (data.puser != null && data.puser.name != null) {
			puserHtml = '@' + data.puser.name + ":";
		}
		var newcontent = '<br>'+puserHtml+data.reply.body.text.replace(/&lt;div&gt;/g,'<br>').replace(/&lt;\/div&gt;/g,'<br>').replace(/&lt;p&gt;/g,'<br>').replace(/&lt;\/p&gt;/g,'<br>').replace(/<br><br>/g,'<br>');

		var html = '<div class="post-reply-d-tit" id="tId_'+data.reply.rid+'" style="'+ isShow +'"><cite><img src="'+data.user.icon+'"></cite><div class="post-reply-d-cont" id="reText_'+data.reply.oid+'">'+data.user.name+' : '+newcontent+'</div><div class="post-reply-detail post-reply-detail1"><a class="post-reply-reply" href="#textarea_body_'+data.reply.oid+'" data-field=\''+JSON.stringify(dataField)+'\'>回复</a>'+ delHtml +'<a class="post-reply-like'+agreeCss+'" href="javascript:void(0);" data-rid="'+data.reply.rid+'">（'+data.reply.agree_sum+'）</a><span>'+data.reply.post_date+'</span></div></div>';
		return html;
	},
	
	// 楼中楼回复按钮绑定事件
	'rCommentButton' : function(){
		var dataField = $(this).attr('data-field');
		var arrField = eval('('+dataField+')');
		tlqComment.rMore(arrField.oid);
		$("#textarea_body_"+arrField.oid).attr('data-field', dataField);
		$("#textarea_body_"+arrField.oid).html('@'+arrField.author+':');
		$("#textarea_body_"+arrField.oid).keyup(function(){
			var strLen = $(this).html().length;
			if(strLen==0){
				arrField.pid = arrField.oid;
				$(this).attr('data-field', JSON.stringify(arrField));
			}
		});
		// moveEnd($("#textarea_body_"+arrField.oid));
	},
	
	// 楼中楼显示与隐藏
	'rCommentList' : function(){
		var buttonVal = $(this).html();
		if(buttonVal != '收起回复'){
			$(this).next('div').show();
			$(this).html('收起回复');
			//$('.post-reply-detail').css({'padding-right':'105px'});
		}else{
			$(this).next('div').hide();
			$(this).html('回复('+$(this).attr('data-subrnum')+')');
			//$('.post-reply-detail').css({'padding-right':'80px;'});
		}
		//$('.post-comment-box .post-reply-detail').css({'padding-right':'10px;'});
	},
	
	// 楼中楼回复提交按钮
	'rComment' : function(){
		// 设置按钮不可点击
		var butttonDisable = $(this).attr('data-disable');
		if(!butttonDisable){
			$(this).attr('data-disable', true);
		}else{
			return false;
		}
		var zRid = $(this).attr('data-rid');
		var text = $('#textarea_body_'+$(this).attr('data-rid')).html();
		var rid = $(this).attr('data-rid');
		var postData = eval("("+$('#textarea_body_'+rid).attr('data-field')+")");
		if(postData == undefined){
			postData={'pid':zRid, 'oid':zRid};
		}
		text = text.replace('@'+postData.author+':', '');
		var checkRes = tlqComment.checkFn(text);
		if(!checkRes){
			return false;
		}
		postData.text = $.trim(text.replace(/&nbsp;/g, ' '));
		tlqComment.rPostComment(postData);
	},
	
	// 获取楼中楼列表数据
	'getReCommentData' : function(oid){
		$.ajax({
			url: window.joymeapi.api+"jsoncomment/reply/sublist",
			type: "post",
			async: false,
			data: {unikey:window.uniKey, domain:window.domain, oid:oid, pnum:window.subpnum, psize:10, ordertype:'asc'},
			dataType: "jsonp",
			jsonpCallback: "sublistcallback",
			success: function (req) {
				var resMsg = req[0];
				if(resMsg.rs == 1){
					if(resMsg.result != null){
						var reCommentHtml = '';
						for (var i = 0; i < resMsg.result.rows.length; i++) {
							reCommentHtml += tlqComment.getReCommentHtml(resMsg.result.rows[i]);
						}
						$('#reComments_'+oid).html(reCommentHtml);
					}
					rePageListHtml = tlqComment.getPageHtml(resMsg.result.page, oid);
					$('#rePageDiv_'+oid).html(rePageListHtml);
					$('.page_'+oid).click(tlqComment.subPage);
					tlqComment.pageClick();
				}else{
					setErrMsg('获取列表失败');
				}
			},
			error: function () {
				setErrMsg('getReCommentData程序错误');
			}
		});
	},
	
	// 楼中楼回复
	'rPostComment' : function(postData){
		var body = {
			'text': postData.text,
            'pic': ""
		};
		$.ajax({
			url: window.joymeapi.api+"jsoncomment/reply/post",
			type: "post",
			async: false,
			data: {unikey:window.uniKey, domain:window.domain, body:JSON.stringify(body), oid:postData.oid, pid:postData.pid},
			dataType: "jsonp",
			jsonpCallback: "postcallback",
			success: function (req) {
				var resMsg = req[0];
				if(resMsg.rs == 1){
					var html = tlqComment.getReCommentHtml(resMsg.result);
					$('#reComments_'+resMsg.result.reply.oid).append(html);
					$('#textarea_body_'+resMsg.result.reply.oid).html('');
					tlqComment.pageClick();
					tlqComment.setCommentNum(resMsg.result.user.name, resMsg.result.reply.post_time, 'add');
					// 总回复数加1
					var subrnum = $('.post-reply-open[data-rid='+resMsg.result.reply.oid+']').attr('data-subrnum');
					$('.post-reply-open[data-rid='+resMsg.result.reply.oid+']').attr('data-subrnum', ++subrnum);
					$('#errMsg').html('');
				}else{
					var denyWord = resMsg.result == null ? '' : ':'+resMsg.result;
					setErrMsg(commenterror[resMsg.rs]+denyWord);
				}
				$('.recomment').removeAttr('data-disable');
			},
			error: function () {
				setErrMsg('rPostComment程序错误');
				$('.recomment').removeAttr('data-disable');
			}
		});
	},
	
	// 主回复
	'postComment' : function( body, oid, pid){

		$.ajax({
			url: window.joymeapi.api+"jsoncomment/reply/post",
			type: "post",
			async: false,
			data: {unikey:window.uniKey, domain:window.domain, body:JSON.stringify(body), oid:oid, pid:pid},
			dataType: "jsonp",
			jsonpCallback: "postcallback",
			success: function (req) {
				var resMsg = req[0];
				if(resMsg.rs == 1){
					var tmp = new Object();
					tmp.reply = resMsg.result
					var html = tlqComment.getCommentHtml(tmp);
					$('#comment_list_area').append(html);
					$('#textarea_body_0').html('');
					$('#errMsg').html('');
					tlqComment.pageClick();
					tlqComment.setCommentNum(resMsg.result.user.name, resMsg.result.reply.post_time, 'add');
				}else{
					var denyWord = resMsg.result == null ? '' : ':'+resMsg.result;
					setErrMsg(commenterror[resMsg.rs]+denyWord);
				}
				$('#comment_submit').removeAttr('data-disable');
			},
			error: function () {
				setErrMsg('postComment程序错误');
				$('#comment_submit').removeAttr('data-disable');
			}
		});
	},
	
	// 楼中楼查看更多
	'rMore' : function(rid){
		rid = !isNaN(rid) ? rid : $(this).attr('data-rid');
		$('span[data-rid='+rid+']').parent().remove();
		$('#reComments_'+rid+' > div').show();
		$('#post_recomment_area_'+rid).show();
		$('#post_recomment_area_'+rid).next('div').show();
		$('#rePageDiv_'+rid).show();
	},
	
	// 点赞
	'agreeComment' : function(){
		if(!window.joymeIsLogin){
			setErrMsg(commenterror.loginHtml);
			return false;
		}
		var rid = $(this).attr('data-rid');
		$.ajax({
			url: window.joymeapi.api+"jsoncomment/reply/agree",
			type: "post",
			async: false,
			data: {unikey:window.uniKey, domain:window.domain, rid:rid},
			dataType: "jsonp",
			jsonpCallback: "agreecallback",
			success: function (req) {
				var resMsg = req[0];
				if(resMsg.rs == 1){
					localSorageData('set', window.wgWikiname+'_'+rid, rid);
					var n = $('a[data-rid="'+rid+'"]').html();
					n = n.match(/[\d]+/g);
					$('.post-reply-detail a[data-rid="'+rid+'"]').html('( '+(++n)+' )');
					$('.post-reply-detail a[data-rid="'+rid+'"]').addClass('post-reply-like_cur');
				}else{
					setErrMsg(commenterror[resMsg.rs]);
				}
			},
			error: function () {
				// setErrMsg('agreeComment程序错误');
			}
		});
	},
	
	// 删除
	'delComment' : function(){
		var dataField = eval('('+$(this).attr('data-field')+')');
		if (!confirm('确定要删除吗？')) {
			return false;
		}
		var num = 1;
		if(dataField.oid == 0){
			num += parseInt($('.post-reply-open[data-rid='+dataField.rid+']').attr('data-subrnum'));
		}

		$.ajax({
			url: window.joymeapi.api+"jsoncomment/reply/remove",
			type: "post",
			async: false,
			data: {unikey:window.uniKey, domain:window.domain, rid:dataField.rid},
			dataType: "jsonp",
			jsonpCallback: "removecallback",
			success: function (req) {
				var resMsg = req[0];
				if(resMsg.rs == 1){
					$('#tId_'+dataField.rid).remove();
					//总回复数减1
					var subrnum = $('.post-reply-open[data-rid='+dataField.oid+']').attr('data-subrnum');
					$('.post-reply-open[data-rid='+dataField.oid+']').attr('data-subrnum', --subrnum);
					// 调用评论数上报
					var latPostUser = resMsg.result ? resMsg.result.user.name : '';
					var latPostTime = resMsg.result ? resMsg.result.reply.post_time : '';
					tlqComment.setCommentNum(latPostUser, latPostTime, 'del', num);
				}else{
					setErrMsg(commenterror[resMsg.rs]);
				}
			},
			error: function () {
				setErrMsg('delComment程序错误');
			}
		});
	},
	
	// 表情
	'emoji' : function(e){
		$('#biaoqing').find('td').unbind('click');
		$('#biaoqing').show();
		e.stopPropagation();
		var oid = $(this).attr('data-oid');
		var top = $(this).offset().top-68;
		var left = $(this).offset().left-170;
		$('#biaoqing').css({'position':'absolute', 'top':top, 'left':left, 'z-index':999});
		$('#biaoqing').find('td').bind('click', function(){
			var html = $('#textarea_body_'+oid).html()+'['+$(this).attr('data-code')+']';
			$('#textarea_body_'+oid).html(html);
		});
	},
	
	// 绑定页面所有click事件
	'pageClick' : function(){
		$('.post-reply-open, .post-reply-reply, .post-reply-like, .post-reply-delet, .review-icon-phiz').unbind( "click" );
		$('div [id ^= "textarea_body_"]').unbind('paste');
		$('.post-reply-open').bind('click', tlqComment.rCommentList); // 楼中楼隐藏展示
		$('.post-reply-reply').bind('click', tlqComment.rCommentButton); // 楼中楼回复按钮绑定事件
		$('.post-reply-like').bind('click', tlqComment.agreeComment); // 绑定点赞事件
		$('.post-reply-delet').bind('click', tlqComment.delComment); // 绑定删除事件
		$('.review-icon-phiz').bind('click', tlqComment.emoji); // 绑定表情事件
		$('.review-but span').bind('click', tlqComment.rMore);  // 楼中楼查看更多
		$('.recomment').bind('click', tlqComment.rComment); // 楼中楼回复
		$('div [id ^= "textarea_body_"]').bind('paste', pasteHandlers); // 粘贴过滤html标签
		
	},
	
	// 主页翻页
	'mainPage' : function(){
		window.pnum = $(this).attr('data-pagen');
		tlqComment.getCommentList();
	},
	
	// 楼中楼翻页
	'subPage' : function(){
		window.subpnum = $(this).attr('data-pagen');
		var tmpArr = $(this).attr('class').split('_');
		var oid = tmpArr[1];
		tlqComment.getReCommentData(oid);
	},
	
	// 评论数上报统计
	'setCommentNum' : function(user, time, type, num){
		var pageId = window.JpageId;
		var token = window.Jtoken;
		num = num == undefined ? 1 : num;
		var typeNum = type == 'add' ? 1 : 2;// 1增加 2删除
		$.ajax({
			url: window.joymeapi.joymewiki+'?c=wikiPosts&a=setUpCommentsNum',
			type: "post",
			async: false,
			data:{wikikey:window.wgWikiname, page_id:pageId, token:token, last_comment_user:user, last_comment_time:parseInt(time/1000), type:typeNum, commentnum:num},
			dataType: "jsonp",
			jsonpCallback: "setCommentNumCallback",
			success: function (req) {
				// console.log('req', req);
			},
			error: function () {
				setErrMsg('setCommentNum程序错误');
			}
		});
	},
	
	// 判断登录，检查数据
	'checkFn' : function(text){
		// check login
		if(!window.joymeIsLogin){
			setErrMsg(commenterror.loginHtml);
			return false;
		}
		// check text
		var text = $.trim(text.replace(/&nbsp;/g, ''));
		var textLen = text.length;
		if(textLen == 0){
			setErrMsg(commenterror.textEmpty);
			$('.recomment').removeAttr('data-disable');
			return false;
		}else if(textLen > 300){
			setErrMsg(commenterror.textToLong);
			$('.recomment').removeAttr('data-disable');
			return false;
		}
		return true;
	},
	
	// 拼接分页html
	'getPageHtml' : function(data, oid){
		var html = '';
		if(data==null || data.maxPage==1){
			return html;
		}
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
			var sty = '', pagen = '';
			if(data.curPage == i){
				sty = 'class="cur" ';
			}else{
				pagen = ' data-pagen="'+i+'" ';
				sty = 'class="page_'+oid+'" ';
			}
			var url = oid==0 ? '#wrapper' : '#tId_'+oid;
			arr.push('<a ' + sty + pagen +' href="'+url+'">'+i+'</a>');//#wrapper
		}
		
		html = arr.join('');
		if(data.maxPage == 2){
			return html;
		}
		
		var prev = '<span>&lt;前页</span>';
		var next = '<span>后页&gt;</span>';
		if(data.curPage > 1){
			prev = '<a  class="page_'+ oid +'" href="javascript:void(0);" data-pagen="'+(data.curPage-1)+'">&lt;前页</a>';
		}
		if(data.curPage < data.maxPage){
			next = '<a class="page_'+ oid +'" href="javascript:void(0);" data-pagen="'+(data.curPage+1)+'">后页&gt;</a>';
		}
		
		return prev+html+next;
	}
}

//监控粘贴(ctrl+v),如果是粘贴过来的东东，则替换多余的html代码，只保留<br>

// 重写setTimeout 使其可以传参
var _st = window.setTimeout; 
window.setTimeout = function(fRef, mDelay) { 
    if(typeof fRef == 'function'){ 
        var argu = Array.prototype.slice.call(arguments,2); 
        var f = (function(){ fRef.apply(null, argu); }); 
        return _st(f, mDelay);
    } 
    return _st(fRef,mDelay);
}
// 粘贴绑定事件
function pasteHandlers(){
	var id = $(this).attr('id');
	window.setTimeout(delHtmlTag, 100, id);
}
// 过滤html标签
function delHtmlTag(id){
	var content = $('#'+id).html();
	valiHTML=["br"];
	content = content.replace(/<[\/\s]*(?:(?!div|br)[^>]*)>/g,'');
	content = content.replace(/<[\/\s]*(?:(?!div|br)[^>]*)>/g, '');
	content = content.replace(/<\s*div[^>]*>/g, '<div>');
	content = content.replace(/<[\/\s]*div[^>]*>/g, '</div>');

	content=content.replace(/_moz_dirty=""/gi, "").replace(/\[/g, "[[-").replace(/\]/g, "-]]").replace(/<\/ ?tr[^>]*>/gi, "[br]").replace(/<\/ ?td[^>]*>/gi, "&nbsp;&nbsp;").replace(/<(ul|dl|ol)[^>]*>/gi, "[br]").replace(/<(li|dd)[^>]*>/gi, "[br]").replace(/<p [^>]*>/gi, "[br]").replace(new RegExp("<(/?(?:" + valiHTML.join("|") + ")[^>]*)>", "gi"), "[$1]").replace(new RegExp('<span([^>]*class="?at"?[^>]*)>', "gi"), "[span$1]").replace(/<[^>]*>/g, "").replace(/\[\[\-/g, "[").replace(/\-\]\]/g, "]").replace(new RegExp("\\[(/?(?:" + valiHTML.join("|") + "|img|span)[^\\]]*)\\]", "gi"), "<$1>");

	if(!/firefox/.test(navigator.userAgent.toLowerCase())){
		content=content.replace(/\r?\n/gi, "<br>");
	}
	content = content.replace(/((<br>)+)/g,"");

	$('#'+id).html(content);
}

// 光标移动
function  moveEnd(obj){
	obj.focus();
	var len = obj.html().length;
	if(document.selection) {
		var sel = obj.createTextRange();
		sel.moveStart( ' character ' ,len);
		sel.collapse();
		sel.select();
	}else if( typeof obj.selectionStart == 'number' && typeof obj.selectionEnd == 'number' ) {
		obj.selectionStart = obj.selectionEnd = len;
	}
}

// html5 本地存储
function localSorageData(op, key, val) {
    if (!window.localStorage || !op || !key) {
        return false;
    } else if (op == 'set' && val == '') {
        return false;
    }
    if (op == 'set') {
        window.localStorage.setItem(key, val);
    } else if (op == 'get') {
        return window.localStorage.getItem(key);
    } else {
        return false;
    }
}

// 设置错误信息
function setErrMsg(msg) {
    $("#errMsg").html(msg);
}

// 表情模板
function emojiData(jsonData) {
    if (jsonData.rs != 1) {
        window.emoji = '';
        return false;
    } else {
        window.emoji = jsonData
    }
    var s = '<div>';
    for (var i in jsonData.result) {
        s += '<span class="emojiTab">' + i + '</span>';
    }
    for (var i in jsonData.result) {
        var tmp = jsonData.result[i];
        s += '<div id="' + i + '" style="display:none;"><table><tr>';
        for (var j in tmp) {
            s += '<td data-code="' + tmp[j]['code'] + '"><img src="' + tmp[j]['pic'] + '" title="' + tmp[j]['code'] + '"/></td>';
            if (j != 0 && j % 10 == 0) {
                s += '</tr><tr>';
            }

        }
        s += '</tr></table></div>';

    }
    s += '</div>';
    $('#biaoqing').html(s);
    var firstSmail = $('#biaoqing div span :first').html();
    $('#biaoqing div div:first').show();
    $('#biaoqing div span:first').addClass('cur');
    $('.emojiTab').click(function() {
        var tab = $(this).html();
        $('#biaoqing div div').hide();
        $('#biaoqing div span').removeClass('cur');
        $(this).addClass('cur');
        $('#' + tab).show();
    });
}


// 发表图片
$('#commentImg2').change(function(){
    var content = $("#commentImg2").val();
    if(content){
        $('#edittoken').val(mw.user.tokens.get('editToken'));
        $('#imgForm2').submit();//function(){return false;}
        $('#tips2').show();
        $('#addFriendshipLinks').hide();
    }
});

// 发图回调函数
function upImgCallback(data){
    //判断div是否隐藏
    var o =document.getElementById("imgdiv1").style.display;
	var imgData = eval('('+data+')');
	if( imgData.result && imgData.result.rs == 0 ){
        if(o=="block"){
            $('#errMsg2').html(imgData.result.msg);
            $('#errMsg2').show();
        }else{
            $('#errMsg').html(imgData.result.msg);
        }
	}else{
        if(o=="block"){
            $("#imageval").val(imgData.http_url);
            $('#addFriendshipLinks').show();
            $('#errMsg2').hide();
        }else{
            var html = $('#textarea_body_0').html()+'<img src="'+imgData.http_url+'">';
            $('#textarea_body_0').html(html);
        }
	}
    $('#tips2').hide();
}

/************************合并模板里的js*****************************/
function switchTab(n) {
    for (var i = 1; i <= 2; i++) {
        document.getElementById("tab_" + i).className = "";
        document.getElementById("tab_con_" + i).style.display = "none";
    }
    document.getElementById("tab_" + n).className = "cur";
    document.getElementById("tab_con_" + n).style.display = "block";
}

function revmoeinfo(id){

	var url = $("#url").val();
	var sendurl = url+"?del=5";
	if(confirm('确实要删除吗?')){
		$.ajax({
			url:sendurl,
			type:"GET",
			async: false,
			data:{"id":id},
			dataType: "jsonp",
			success:function(msg){
				if(msg==1){
					alert("设置成功！");
					location.reload()
				}else{
					alert("设置失败！");
				}
			}
		})
	}
};
;



var add={
    int:function(){
        add.show();
        add.addwiki();
    },
    show:function(){
        var settingBtn=$('.setting-btn');
        var floatWin=$('.float-win');
        var btnclose=$('.btn-close');
        settingBtn.on('click',function(){
            var checkon = new Array();
            var checkonid = new Array();

            $("span[id='linkF']").each(function() {
                checkon.push($(this).text());
            });

            $("span[id='linkF']").each(function() {
                checkonid.push($(this).next().val());
            });
            var str = '<span>';
            for(var i= 0;i<checkon.length;i++){

                str+= checkon[i]+"</span><cite onclick='revmoeinfo("+checkonid[i]+")'></cite></br>";
            }
            $(".add-list").html(str);
            floatWin.show();
        });
        btnclose.on('click',function(){
            $('#tips1').hide();
            $('#tips2').hide();
            floatWin.hide();
        });
    },

	addwiki:function(){
		var addbtn=$('.add-btn');
		var fwstatus=$('.fw-status');
		addbtn.on('click',function(){
			var val=$.trim($('.inp-text').val());
			var wikiname = $("#wikiname").val();

			if(val=='' || wikiname ==''){
				$('#tips1').show();
			}else{
				var text_wiki = $("#wikitext").val();
				var text_name = $("#wikiname").val();
				var url = $("#url").val();
				var floatWin=$('.float-win');

				var checkon = new Array();
				$("span[id='linkF']").each(function() {
					checkon.push($(this).text());
				});

				if(checkon.length>=6){
					alert("最多只能设置六个链接");
					return false;
				}

				if(text_name.length >=13){
					alert("wiki名称最多只限12个字");
					return false;
				}

				var imagePath = $("#imageval").val();

				sendurl = url+"?del=4";;
				$.ajax({
					url:sendurl,
					type:"GET",
					async: false,
					data:{"text_wiki":text_wiki,"text_name":text_name,"filename":imagePath},
					dataType: "jsonp",
					beforeSend: function(){
						var tishi = "<img src='"+url+"'+/resources/src/mediawiki.posts/images/loading.gif'>";
						$('#tips2').show();
						$('#tips1').hide();
					},
					success:function(msg){
						$('#tips2').hide();
						if(msg==1){
							alert("设置成功！");
							floatWin.hide();
							location.reload()
						}else{
							alert("设置失败！");
						}
					}
				})
			};
		});
	}
};
add.int();
