;//JComments.js

var JC = {
	isWap : 0,
	submitted : 0,
	isErr : 0,
	body : '',
	nowBoxID : '',
	imgsum : 0,
	plid : 0,
	isLoading : false,
	clickPage: false,
	key : window.wgWikiname,
	comment_sum : 0,
	mainBox : $('#JCbox'),
	jcMsgDialog : null,
	jcWinManager : null,
	comentSum : 0,
	data : {},
	uids : [],
	userinfo : {},
	uc_url : 'http://www.joyme.'+window.wgWikiCom+'/usercenter/page?pid=',
	// initData : {},
	
	uid : mw.config.get('wgUserId') || 0,
	title : mw.config.get('wgTitle') || '',
	main_floor_num : 0,
	middle_comentSum : 0,
	max_floor_num : 0,
	heat_num : 0,
	
	init : function(){
		//console.log('JS:', 'JC');
		if(mw.config.get('wgNamespaceNumber') != 0){
			$('#JCbox,#let-dianzan').hide();
			return false;
		}
		JC.clientType();
		if(!JC.uid){
			// $('.text-area').append('<span class="wei-dl">您需要<a href="javascript:mw.loginbox.login();">登录</a>后才能发表评论</span>');
			$('.text-area').append('<span class="wei-dl">您需要<a href="javascript:loginDiv();">登录</a>后才能发表评论</span>');
			$('.text-area>textarea').attr('disabled', true);
		}
		// 锚点评论处理
		JC.plid = parseInt(JC.getQueryString('plid'));
		// JC.getInitData();
		// 获取评论数据
		JT.getCommentData();
		// 获取Dialog对象
		mw.loader.using( 'oojs-ui' ).done( function () {
			JC.jcMsgDialog = new OO.ui.MessageDialog();
			JC.jcWinManager = new OO.ui.WindowManager();
			$( 'body' ).append( JC.jcWinManager.$element );
			JC.jcWinManager.addWindows( [ JC.jcMsgDialog ] );
		});
		JC.joymeEmjoy = window.joymeEmjoy;
		JC.emjoyBox();
	},
	imgPosition: function(){
		//评论图片大小及位置的判断
		var imgscroller_obj = $('#JCbox .image-scroller');
		var imgheight=$(imgscroller_obj).find("img.feature-image").height();
		var imgwidth=$(imgscroller_obj).find("img.feature-image").width();
		var marginHeight=300-imgheight/2;
		var marginWidth=410-imgwidth/2;
		if(imgheight<600){
			$(imgscroller_obj).find(".preview").css("display","none");
		}else{
			$(imgscroller_obj).find(".preview").css("display","block");
		}
		if(imgwidth<820){
			$(imgscroller_obj).find("img.feature-image").css("left",marginWidth+"px");
		}
		else{
			$(imgscroller_obj).find("img.feature-image").css("left","0px");	
		}
		if(imgheight<600){
			$(imgscroller_obj).find("img.feature-image").css("top",marginHeight+"px");
		}
		else{
			$(imgscroller_obj).find("img.feature-image").css("top","0px");
		}
		//评论图片大小及位置的判断结束
	},
	
	// getInitData : function(){
		// 初始化评论参数 plid=2,2,21628,21698
		// var plid = JC.getQueryString('plid');
		// if(plid){
			// var arr = plid.split(',');
			// JC.initData.plid = arr[2];
			// JC.initData.rid = arr[3];
			// JC.initData.mp = JT.pnum = arr[0] ? arr[0] : 1;
			// JC.initData.sp = arr[1];
		// }
	// },
	
	emjoyBox : function(){
		var html = '<div class="talk-btn-box"><span></span>';
		var data = JC.joymeEmjoy;
		html += '<div class="talk-btn-facecont"><span class="facecont-tit">';
		var x = 0;
		for(var i in data){
			if(x == 0){
				html += '<em class="'+i+' on">'+i+'</em>';
			}else{
				html += '<em class="'+i+'">'+i+'</em>';
			}
			x++;
		}
		html += '</span>';
		html +='<div class="facecont-cont">';
		var y=0;
		for(var i in data){
			var emjoy = data[i];
			if(y == 0){
				html += '<ul class="'+ i +' on">';
			}else{
				html += '<ul class="'+ i +'">';
			}
			for(var x in emjoy){
				html += '<li><img src="'+ emjoy[x].pic +'" alt="'+ emjoy[x].code +'"></li>';
			}
			html += '<div class="fn-clear"></div></ul>';
			y++;
		}
		html += '</div></div></div>';
		$('.talk-btn-face').html(html);
	},
	
	// 展示评论
	comments : function(data, pageno){
		if(data == null || data.mainreplys.rows.length < 1){
			JC.noData();
			return;
		}
		// 总数
		JC.comentSum = data.comment_sum;

		//当前最高楼层数
		JC.max_floor_num = data.mainreplys.rows[0].reply.reply.floor_num;

		////主楼楼层数
		JC.main_floor_num = data.mainreplys.page.totalRows;

		////楼中楼评论数
		JC.middle_comentSum = JC.comentSum-JC.main_floor_num;
        //
		////热度值
		JC.heat_num = JC.max_floor_num+JC.middle_comentSum;

		var numf = JC.numBig(JC.comentSum);

		$('.commentSum').html( JC.heat_num );

		document.cookie = "Joyme_heat_num_"+mw.config.get('wgArticleId')+"_"+window.wgWikiname+window.wgWikiCom+"="+ escape (JC.heat_num);

		// if(JC.comentSum > 0){
			// $('.pl').html( '<a href="#let-pinglun">评论:' + numf +'</a>' );
			// $('.pl').show();
		// }
		
		// 获取用户信息
		JC.userInfo(data.mainreplys.rows);

		// 热门评论
		var hotsum = data.hotlist.length;
		var hotCommentHtml = '<div class="fb-pl" id="hotBox"><div class="fb-title"><h3>热门评论</h3></div>';
		for( var i=0; i<hotsum; i++ ){
			hotCommentHtml += JC.mainLi(data.hotlist[i], 'hot');
		}
		hotCommentHtml += '</div>';
		
		// 所有评论
		var allCommentHtml = '<a name="allcomment"></a><div class="fb-pl" id="allBox"><div class="fb-title" ><h3>所有评论</h3></div>';
		for( var i=0; i<JC.comentSum; i++ ){
			allCommentHtml += JC.mainLi(data.mainreplys.rows[i], 'all');
		}
		allCommentHtml += '</div>';
		
		var pagelist = JT.pageBreak(data.mainreplys.page, 0, numf);
		if( pageno > 0 ){
			$('.fb-pl, .paging1, a[name="allcomment"]').remove();
		}
		JC.mainBox.append( hotCommentHtml + allCommentHtml + pagelist );

		JC.emjoyBox();
		JC.commonFn();
	},
	
	// 展示评论
	wapComments : function(data, pageno){
		if(data == null || data.mainreplys.rows.length < 1){
			JC.noData();
			return;
		}
		
		// 所有评论
		var allCommentHtml = '';
		for( var i=0; i<JC.comentSum; i++ ){
			allCommentHtml += JC.mainLi(data.mainreplys.rows[i], 'all');
		}
		$('#allBox').append(allCommentHtml);
		if(data.mainreplys.page.maxPage <= pageno){
			$('.load-more:last').remove();
			$('#allBox').find('.fb-detail:last').addClass('last');
		}
		if(JC.isWap){
			$(".pl-img").addClass("webpltc-m").removeClass("pl-img");
			JT.isLoading = false;
		}
	},
	
	subComments : function(data, oid){
		var type = JC.nowBoxID == 'hotBox' ? 'hot' : 'all';
		var replyBoxHtml = JC.replyBox(data, type, oid);
		$('#'+ JC.nowBoxID +'>.fb-detail[data-rid="'+ oid +'"]').find('.erlou-main').remove();
		$('#'+ JC.nowBoxID +'>.fb-detail[data-rid="'+ oid +'"]').append(replyBoxHtml);
		$('#'+ JC.nowBoxID +'>.fb-detail[data-rid="'+ oid +'"]').find('.erlou-main').show();
		// 锚点
		JC.toAnchor( oid );
	},
	
	wapSubComments : function(data, oid){
		var type = JC.nowBoxID == 'hotBox' ? 'hot' : 'all';
		var replyBoxHtml = '';
		if(data != null){
			var replysum = data.rows.length;
			for( var i=0; i<replysum; i++ ){
				replyBoxHtml += JC.subLi(data.rows[i], type);
			}
		}
		$('#'+ JC.nowBoxID +'>.fb-detail[data-rid="'+ oid +'"]').find('.shuoju').before(replyBoxHtml);
		if(data.page.maxPage <= JT.spnum){
			$('#'+ JC.nowBoxID +'>.fb-detail[data-rid="'+ oid +'"]').find('.web-xyy').remove();
			$( '#'+ JC.nowBoxID ).find('.fb-detail:last').find('.er-lou:last').addClass('last');
		}
		JT.isLoading = false;
		// if(JC.initData.rid){
			// window.location.hash = 'allcomment-' + JC.initData.rid;
		// }
	},
	
	// 添加回调
	addComment : function(data, oid){
		if(oid){
			var sbLiHtml = JC.subLi(data.reply, 'all');
			$(".fb-detail[data-rid='"+ oid +"']").find('.erlou-main').prepend(sbLiHtml);
			var replysum = $(".fb-detail[data-rid='"+ oid +"']").find('.huifu').attr('data-rsum');
			replysum = parseInt(replysum)+1;
			$(".fb-detail[data-rid='"+ oid +"']").find('.huifu').attr('data-rsum', replysum);
			$(".fb-detail[data-rid='"+ oid +"']").find('.huifu').each(function(i){
				var btText = $(this).html();
				if(btText != '收起回复'){
					$(this).html('回复 ('+ replysum +')');
				}
			});
			JC.noticeCommentData(data.reply, 'sub');
		}else{
			var mainLiHtml = JC.mainLi(data, 'all');
			
			if( $('#allBox').length < 1 ){
				var allCommentHtml = '<a name="allcomment"></a><div class="fb-pl" id="allBox"><div class="fb-title" ><h3>所有评论</h3></div>'+ mainLiHtml +'</div>';
				$("#JCbox").append(allCommentHtml);
				$('#plnum').next('p').remove();
			}else{
				$("#allBox").find('div:first').after(mainLiHtml);
			}
			JC.noticeCommentData(data, 'main');
			var num = $('.count-num:eq(0)').find('b:eq(1)').html();
			$('.count-num:eq(0)').find('b:eq(1)').html(parseInt(num)+1);
			$('#plnum>span').html(0);
			if(!this.isPC()){
				$(".pl-img").addClass("webpltc-m").removeClass("pl-img");
			}
		}
		++JC.comentSum;
		var numf = JC.numBig(JC.comentSum);
		//当前参与度+1
		$('.commentSum').html( parseInt($('.commentSum').text())+1 );
		$('.page-hot-num').html(parseInt($('.page-hot-num').html())+1);
		// $('.pl').html( '<a href="#let-pinglun">评论:' + numf +'</a>' );
		// $('.pl').show();
		JC.emjoyBox();
		JC.clear();
		JT.userCommentMsgData(JC.data);
		JC.checkAT();
	},
	
	// 点赞回调
	zanComment : function(rid){
		var str = $('.zan[data-id="'+ rid +'"]').html();
		var num = parseInt(JT.getNum(str)) + 1;
		$('.zan[data-id="'+ rid +'"]').html('<a href="javascript:;">赞 ('+ num +')</a>');
		JC.noticeZanData(rid);
		JC.data.echotype = 1;
		JC.data.type = 1;
		if( JC.data.tuid != JC.data.cid ){
			JT.userCommentMsgData(JC.data);
		}
	},
	
	// 删除回调
	delComment : function(rid){
		var classname = $('div[data-rid="'+ rid +'"]:eq(0)').attr('class');
		if(classname.indexOf('fb-detail') == -1){
			var oid = $('div[data-rid="'+ rid +'"]:eq(0)').parents('.fb-detail').attr('data-rid');
			var sum = $('div[data-rid="'+ oid +'"]:eq(0)').find('.huifu').attr('data-rsum');
			sum = parseInt(sum)-1;
			$('div[data-rid="'+ oid +'"]').find('.huifu').attr('data-rsum', sum);
			$("div[data-rid='"+ oid +"']").find('.huifu').each(function(i){
				var btText = $(this).html();
				if(btText != '收起回复'){
					$(this).html('回复 ('+ sum +')');
				}
			});
			$('div[data-rid="'+ rid +'"]').remove();
			--JC.comentSum;
			//$('.commentSum').html( JC.comentSum );
			//$('.page-hot-num').html( JC.comentSum );
			// $('.pl').html( '<a href="#let-pinglun">评论:' + JC.comentSum +'</a>' );
		}else{
			var plsum = $('div[data-rid="'+ rid +'"]:eq(0)').find('.fb-bt.er-lou').length;
			$('div[data-rid="'+ rid +'"]').remove();
			JC.comentSum = JC.comentSum - (plsum+1);
			//$('.commentSum').html( JC.comentSum );
			//$('.page-hot-num').html( JC.comentSum );
			// $('.pl').html( '<a href="#let-pinglun">评论:' + JC.comentSum +'</a>' );
			var num = $('.count-num:eq(0)').find('b:eq(1)').html();
			$('.count-num:eq(0)').find('b:eq(1)').html(parseInt(num)-1);
		}
	},
	
	// 翻页回调
	nextPage : function(num, oid){
		JC.clickPage = true;
		if(oid > 0){
			JT.spnum = num;
			JT.getSubCommentData(oid);
		}else{
			JC.plid = null;
			JT.pnum = num;
			JT.getCommentData();
		}
	},
	
	// 翻页锚点跳转
	toAnchor : function( oid ){
		//window.location.hash = '';
		// 锚点跳转 && !JC.isWap
		if( JC.plid ){
			var obj = $('#allBox').find('div[data-rid="'+ JC.plid +'"]');
			if( obj.length > 0 ){
				var classname = obj.attr('class');
				if(classname.indexOf('fb-detail') == -1){
					$('#allBox').find('div[data-rid="'+ JC.plid +'"]').parents('.erlou-main').show();
				}
				window.location.hash = '#allcomment-' + JC.plid;
			}
			JC.plid = null;
			return;
		}
		if(oid > 0){
			var tag = JC.nowBoxID == 'hotBox' ? 'hot' : 'all';
			var maodian = tag + 'comment-' + oid;
		}else{
			var maodian = 'allcomment';
		}
		if(JC.isWap == 0 && oid != undefined){
			window.location.hash = '#'+maodian;
		}else if( JC.clickPage ){
			window.location.hash = '#'+maodian;
			JC.clickPage = false;
		}
	},
	
	// 清除
	clear : function(){
		$("#jcomment").val('');
		$(':input[name="reply"]').val('');
		$(':input[type="file"]').removeAttr('disabled');
		$('.tp-num>span').html('图片(0/1)');
		$('.tp-details').removeClass('showh');
		$('.tp-left').remove();
	},
	
	// 无数据展示
	noData : function(){
		$('#plnum').after('<p class="mqmypl">目前没有评论，欢迎你发表评论</p>');
	},
	
	// 公共代码
	commonFn : function(){
		// 手机点击查看图片
		if(JC.isWap){
			$(".pl-img").addClass("webpltc-m").removeClass("pl-img");
		}else{
			// 最后一条样式
			$('#hotBox,#allBox').find('.fb-detail:last').addClass('last');
			$('#hotBox,#allBox').find('.fb-detail').each(function(){
				$(this).find('.fb-bt.er-lou:last').addClass('last');
			});
		}
		// 锚点
		JC.toAnchor();
	},
	
	// 确认对话框
	confirmDialog : function(msg, action, pdata){
		if(JC.jcWinManager != null){
			JC.jcWinManager.openWindow( JC.jcMsgDialog, {
				message: msg,
				 actions: [
					{ label: '取消'},
					{ label: '确认', action: action }
				]
			}).then( function ( opened ) {
                opened.then( function ( closing, data ) {
                    if ( data && data.action ) {
                        if(data.action =='del'){
							JT.delComment(pdata.rid);
                        }
                    }
                } );
            } );
		}
	},
	
	mainLi : function(mainItem, type){
		if(typeof(mainItem) == 'undefined'){
			return '';
		}
		var reply = mainItem.reply.reply;
		var user = mainItem.reply.user;
		var subreplys = mainItem.subreplys;
		var replyBoxHtml = JC.replyBox(subreplys, type, reply.rid);
		var delHtml = '';
		if(user.uid == JC.uid){
			var delHtml = '<span class="del" data-id="'+ reply.rid +'"><a href="javascript:;">删除</a></span>';
		}
		var img = '';
		if(reply.body.pic&&reply.body.pic!=="[]"){
			img = '<p class="pl-img"><img src="' + reply.body.pic  +'" class="plimg"/></p>';
		}
		var louceng = '';
		if(type == 'all'){
			louceng = '<b class="lou-num">'+ reply.floor_num +'楼</b>';
		}

		//管理员标识
		if($.inArray(user.uid.toString(), JC.userinfo) > -1){
			var adm = '<span class="vip"></span>';
		}else{
			var adm = '';
		}
		
		//headskin
		if(user.headskin){
			var headskinstr = '<span class="daoju daoju-head'+user.headskin+'"></span>'
		}else{
			var headskinstr = '';
		}
		
		if (user.vtype != "" && user.vtype > 0) {
            var vipstr = '<span class="user-vip left-vip" title="'+user.vdesc+'"></span>';
        }else{
        	var vipstr = '';
        }
		// 道具 daoju-top1(2345678)  
		var mainlihtml = '<div class="fb-detail fn-clear" data-rid="'+ reply.rid +'">' 
				+ '<div class="daoju-top daoju-top'+user.replyskin+'"></div>'
					+'<a name="'+ type +'comment-'+ reply.rid +'"></a>'
					+'<div class="fb-bt"><div class="tx-ms">'
						+'<p class="toux"><a target="_blank" href="'+JC.uc_url+user.pid+'" class="userinfo" data-username="'+user.pid+'">'
							+'<img src="'+ user.icon +'">'+ adm + headskinstr + vipstr +'</a></p>'
						+'<p class="name-des"><span class="user-name">'+louceng+'<a target="_blank" href="'+JC.uc_url+user.pid+'" data-uid="'+ user.uid +'" class="userinfo" data-username="'+user.pid+'">'+ user.name +'</a><span class="user-time fn-clear">';
		
		if( user.uid != mw.config.get('wgUserId') ){
			mainlihtml += '<em class="pl-xl-sj"></em>';
		}
		mainlihtml += '<em class="fb-time">'+ reply.post_date +'</em></span></span><span class="m-user-time"><em class="fb-time">'+ reply.post_date +'</em></span><span class="fb-des">'+ JC.echo(reply.body.text) + '</span></p>'+img+'<div class="fn-clear"></div><div class="fb-img"><p class="zan-huifu no-img"><span class="zan" data-id="'+ reply.rid +'"><a href="javascript:;">赞 ('+ reply.agree_sum +')</a></span><span class="huifu" data-rsum="'+ reply.sub_reply_sum +'">回复 ('+ reply.sub_reply_sum +')</span>'+ delHtml +'</p><div class="fn-clear"></div></div></div></div>'+ replyBoxHtml +'</div>';
		return mainlihtml;
	},
	
	subLi : function(sbItem, type){
		var reply = sbItem.reply;
		var user = sbItem.user;
		var puser = sbItem.puser;
		if(puser != null){
			reply.body.text = '回复 '+ puser.name +' : '+reply.body.text;
		}
		var delHtml = '';
		if(user.uid == JC.uid){
			var delHtml = '<span class="del" data-id="'+ reply.rid +'"><a href="javascript:;">删除</a></span>';
		}
		//管理员标识
		if($.inArray(user.uid.toString(), JC.userinfo) > -1){
			var adm = '<span class="vip"></span>';
		}else{
			var adm = '';
		}
		
		//headskin
		if(user.headskin){
			var headskinstr = '<span class="daoju daoju-head'+user.headskin+'"></span>';
		}else{
			var headskinstr = '';
		}
		
		if (user.vtype != "" && user.vtype > 0) {
            var vipstr = '<span class="user-vip left-vip" title="'+user.vdesc+'"></span>';
        }else{
        	var vipstr = '';
        }
		var reply_userisme_info = '';
		if( user.uid != mw.config.get('wgUserId') ){
			reply_userisme_info = '<em class="pl-xl-sj"></em>';
		}
		var sbLiHtml = '<div class="fb-bt er-lou" data-rid="'
			+ reply.rid +'"><a name="' + type +'comment-'+ reply.rid 
			+'"></a><div class="tx-ms"><p class="toux"><a target="_blank" href="'
			+JC.uc_url+user.pid+'" class="userinfo" data-username="'+user.pid+'"><img src="'
			+ user.icon +'">'+ adm +headskinstr + vipstr
			+'</a></p><p class="name-des"><span class="user-name"><a target="_blank" href="'
			+JC.uc_url+user.pid+'" data-uid="'+ user.uid 
			+'" class="userinfo" data-username="'+user.pid+'">'+ user.name 
			+'</a><span class="user-time fn-clear">'+reply_userisme_info
			+'<em class="fb-time">'+ reply.post_date 
			+'</em></span></span><span class="m-user-time"><em class="fb-time">'
			+ reply.post_date +'</em></span><span class="fb-des">'+ JC.echo(reply.body.text) 
			+'</span></p><div class="fn-clear"></div><div class="fb-img"><p class="zan-huifu no-img"><span class="zan" data-id="'
			+ reply.rid +'"><a href="javascript:;">赞 ('+ reply.agree_sum 
			+')</a></span><span class="reply" data-author="'+ user.name 
			+'"><a href="javascript:;">回复</a></span>'+ delHtml 
			+'</p><div class="fn-clear"></div></div></div></div>';
		return sbLiHtml;
	},
	
	replyBox : function(subreplys, type, oid){
		var replyBoxHtml = '<div class="erlou-main" style="display:none;">';
		var pagelist = '';
		if(subreplys != null){
			var replysum = subreplys.rows.length;
			for( var i=0; i<replysum; i++ ){
				replyBoxHtml += JC.subLi(subreplys.rows[i], type);
			}
			if(JC.isWap == 0){
				pagelist = JT.subPageBreak(subreplys.page, oid);
			}
		}
		if(subreplys != null && subreplys.page != null && subreplys.page.maxPage>1){
			var wappagelist = '<p class="web-xyy" data-pno="2" data-poid="'+ oid +'">加载更多</p>';
		}else{
			var wappagelist = '';
		}
		var disCss = '';
		if(!JC.uid){
			disCss = 'style="display:none;"';
		}
		
		replyBoxHtml += '<div class="shuoju">'+ pagelist +'<a name="wysyj-'+oid+'"></a>'+ wappagelist +'<div class="fn-clear"></div><p class="wysyj"><a href="javascript:;">我也说一句</a></p><div class="sj-text" '+disCss+'><p class="text-say"><input name="reply" type="text" value=""></p><div class="huifu-ta"><div class="talk-btn erlou-wysj"><div class="talk-btn-face"></div></div><a href="javascript:;" class="replyBtn" data-pid="'+oid+'">回复</a></div></div></div></div>';
		return replyBoxHtml;
	},
	
	userInfo : function(data){
		var mainSum = data.length;
		for( var x=0; x<mainSum; x++){
			var uid = data[x].reply.user.uid
			$.inArray(uid, JC.uids)<0 ? JC.uids.push(uid) : '';
			if( data[x].subreplys && data[x].subreplys.rows.length>1 ){
				var sbData = data[x].subreplys.rows;
				var sbSum = data[x].subreplys.rows.length;
				for( var y=0; y<sbSum; y++ ){
					var uid = sbData[y].user.uid;
					$.inArray(uid, JC.uids)<0 ? JC.uids.push(uid) : '';
				}
			}
		}
		var data = {uids:JC.uids.join(','), type:'uid'};
		JT.getUserInfo(data);
	},
	
	checkLogin : function(){
		if(!JC.uid){
			// mw.loginbox.login();
			loginDiv();
			JC.isErr++;
			$('.text-area>textarea').attr('disabled', true);
		}
	},
	
	checkComment : function(){
		JC.body = $.trim(JC.body);
		if(JC.body == ''){
			JC.errDialog('评论内容为空');
		}else if(JC.body.length > 200){
			JC.errDialog('评论内容超过200字限制');
		}
	},
	
	checkAT : function(){
		var s = JC.body + ' ';
		var data = s.match(/@(.+?)\s+/g);
		if( data==null ) return;
		var names =  [];
		for( var i=0; i<data.length; i++ ){
			var tmp = $.trim(data[i]).replace('@', '');
			if( $.inArray(tmp, names)<0 ){
				names.push(tmp);
			}
		}
		var data = {'names':names.join(','), type:'name'};
		JT.getUserInfo(data);
	},
	
	upNoticeAT : function(data){
		for( var i=0; i<data.length; i++ ){
			JC.data.tuid = data[i];
			JC.data.echotype = 3;
			JC.data.type = 1;
			JT.userCommentMsgData(JC.data);
		}
	},
	
	noticeZanData : function(id){
		if(!id) return;
		var sub = $('div[data-rid="'+ id +'"]:eq(0)');
		var main = $('div[data-rid="'+ id +'"]:eq(0)');
		if(main.length>0){
			JC.data.tuid = main.find('.user-name').find('a').attr('data-uid');
			JC.data.desc = main.find('.fb-des').html();
			JC.data.cid = id;
		}else if(sub.length>0){
			JC.data.tuid = sub.find('.user-name').find('a').attr('data-uid');
			JC.data.desc = sub.find('.fb-des').html();
			JC.data.cid = id;
		}else{
			console.log('noticeZanData no data');
		}
	},
	
	noticeCommentData : function(data, type){
		if(type == 'main'){// 主楼
			var reply = data.reply.reply;
			var user = data.reply.user;
			JC.data.tuid = 0;//$('#wgArticleUserID').attr('data-uid');
			JC.data.desc = reply.body.text;//JC.echo(reply.body.text);
			JC.data.cid = reply.rid;
			JC.data.echotype = 2;
			JC.data.type = 1;
			// JC.data.plid = '1,0,'+reply.rid+','+reply.rid;
			JC.data.plid = reply.rid;
		}else if( type == 'sub' ){// 子楼
			var reply = data.reply;
			var user = data.user;
			JC.data.desc = reply.body.text;//JC.echo(reply.body.text);
			JC.data.cid = reply.rid;
			JC.data.echotype = 2;
			JC.data.type = 2;
			JC.data.tuid = $('div[data-rid="'+reply.pid+'"]:eq(0)').find('.user-name').find('a').attr('data-uid');
			var mp = $('.paging1').length>0 ? $('.paging1').find('a.on').html() : 1;
			// JC.data.plid = mp+',0,'+reply.oid+','+reply.rid;
			JC.data.plid = reply.rid;
		}
	},
	
	clientType : function(){
		var u = navigator.userAgent, app = navigator.appVersion;
		var t = {//移动终端浏览器版本信息
                trident: u.indexOf('Trident') > -1, //IE内核
                presto: u.indexOf('Presto') > -1, //opera内核
                webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核
                gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核
                mobile: !!u.match(/AppleWebKit.*Mobile.*/) || !!u.match(/AppleWebKit/), //是否为移动终端
                ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
                android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或者uc浏览器
                iPhone: u.indexOf('iPhone') > -1, //是否为iPhone或者QQHD浏览器
                WPhone: u.indexOf('Windows Phone') > -1,//windows phone
                iPad: u.indexOf('iPad') > -1, //是否iPad
                webApp: u.indexOf('Safari') == -1 //是否web应该程序，没有头部与底部
            };
		if (t.mobile) {
			if (t.android || t.iPhone || t.iPad || t.ios || t.WPhone) {
				JC.isWap = 1;
			}
		}
	},
	
	errDialog : function(msg){
		JC.submitted = 0;
		JC.isErr++;
		if(JC.isErr == 1){
			function closeDialog(){
				mw.ugcwikiutil.autoCloseDialog(msg);
				JC.isErr = 0;
			}
			setTimeout(closeDialog, 2000);
		}

	},
	
	getQueryString : function(name) { 
		if(!name) return null;
		var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i"); 
		var r = window.location.search.substr(1).match(reg);
		if (r != null) return unescape(r[2]); return null; 
	},
	
	// 输出数据处理
	echo : function(str){
		var str = str.replace(/(<\/?a.*?>)/g, ' ');
		str = str.replace(/@(.+?)\s+/g, function(m, p1){
			return '<a href="http://wiki.joyme.'+window.wgWikiCom+'/home/用户:'+p1+'" target="_blank">@'+p1+'</a> ';
		});
		return str;
	},
	// 最大数字格式化
	numBig : function(num){
		if(!num) return 0;
		num = parseInt(num);
		res = num>99999 ? (99999+'+') : num;
		return res;
	},
	isPC:function () {
	    var userAgentInfo = navigator.userAgent;
	    var Agents = new Array("Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod");
	    var flag = true;
	    for (var v = 0; v < Agents.length; v++) {
	        if (userAgentInfo.indexOf(Agents[v]) > 0) {
	            flag = false;
	            break;
	        }
	    }
	    return flag;
	}
};

$( document ).ready( function() {
	
	JC.init();
	// 主评论提交
	$(".fabu").on('click', 'input[type="button"]', function(){
		if(JC.submitted) return;
		JC.submitted = 1;
		JC.body = $('#jcomment').val();
		JC.checkLogin();
		JC.checkComment();
		// JC.checkAT();
		if(JC.isErr){
			JC.isErr = 0;
			return false;
		}
		var pic = $('.tp-left>img') ? $('.tp-left>img').attr('src') : '';
		var oid = pid = 0;
		var body = {
			text: JC.body,
			pic: pic
		};
		JT.postComment(body, oid, pid);
		$('#upImg').show();
	});
	
	// 字数统计
	function fontCount(){
		function count(){
			var len = $('#jcomment').val().length;
			$('#plnum>span').html(len);
		}
		var time = setInterval(count, 1000);
		$(this).bind('blur',function(){  
            clearInterval(time);  
        });
	}
	$('#jcomment').bind('focus', fontCount);
	// function IsPC() {
	//     var userAgentInfo = navigator.userAgent;
	//     var Agents = new Array("Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod");
	//     var flag = true;
	//     for (var v = 0; v < Agents.length; v++) {
	//         if (userAgentInfo.indexOf(Agents[v]) > 0) {
	//             flag = false;
	//             break;
	//         }
	//     }
	//     return flag;
	// }
	$("body").on('click', '', function(){
		$('.talk-btn-face').find('div').hide();
		$('.tp-details.showh').removeClass('showh');
	})
	.on('click', '#jcomment', function(e){
		if(e && e.stopPropagation) {　e.stopPropagation(); }else{window.event.cancelBubble = true;}
		if($('.tp-left').length>0){
			$('.tp-details').addClass('showh');
		}
	})
	//评论点击获取用户状态
	
	.on('click', '.fb-bt .tx-ms .name-des .user-name em.pl-xl-sj', function(){
		if (mediaWiki.config.get('wgUserName') == null) {
			// mw.loginbox.login();
			loginDiv();
			return false;
		}
		var  uid = $(this).parent().prev().data("uid");
		if(mediaWiki.config.get('wgUserId') == uid){
			return false;
		}
		//$("html").addClass("bodypos");
		$('.m-sixin').addClass('sixin-show');
		var  username = $(this).parent().prev().data("username");
		$('.userfollowsx').attr('href','/home/index.php?title=特殊:私信&fid='+uid);
		JT.getUserFollow(uid,username);
	})
	// 关闭M端私信
	.on('click', '.userfollowcancel', function(e){
		$('.m-sixin').removeClass('sixin-show');
		//$("html").removeClass("bodypos");
		$('.userfollowstatus').removeClass("unfollow");
		$('.userfollowstatus').removeClass("followed");
	})
	// 关注用户
	.on('click', '.unfollow', function(e){
		var myuid = mediaWiki.config.get('wgUserId');
		var fid = $('.userfollowstatus').data('uid');
		$('.m-sixin').removeClass('sixin-show');
		//$("html").removeClass("bodypos");
		JT.userUserFollow(myuid,fid);
	})
	// 取消关注
	.on('click', '.followed', function(e){
		var myuid = mediaWiki.config.get('wgUserId');
		var fid = $('.userfollowstatus').data('uid');
		$('.m-sixin').removeClass('sixin-show');
		//$("html").removeClass("bodypos");
		JT.userUserUnFollow(myuid,fid);
	})
	.on('click', '.sixin-popup', function(){
		$('.m-sixin').removeClass('sixin-show');
		//$("html").removeClass("bodypos");
	})
	// 图片上传
	.on('click', '.tupian', function(e){
		if(!JC.uid) return;
		if(e && e.stopPropagation) {　e.stopPropagation(); }else{window.event.cancelBubble = true;}
		$(".talk-btn-box").hide();
		$(".tp-details").toggleClass("showh");
	}).on('click', '.tp-details', function(e){
		if(e && e.stopPropagation) {　e.stopPropagation(); }else{window.event.cancelBubble = true;}
	})
	// 楼中楼回复
	.on('click', '.huifu', function(){
		var bt = $(this).html();
		if(bt == '收起回复'){
			$(this).parents('.fb-detail').find('.erlou-main').hide();
			$(this).html('回复 ('+ $(this).attr('data-rsum') +')');
			return;
		}
		$(this).parents('.fb-detail').find('.erlou-main').show();
		$(this).html('收起回复');
	})
	// 点击我也说一句
	.on('click', '.wysyj', function(){
		JC.checkLogin();
		if(JC.isErr){
			JC.isErr = 0;
			return false;
		}
		var displaycss = $(this).siblings('.sj-text').css('display');
		if(displaycss=='' || displaycss=='none'){
			$(this).siblings('.sj-text').show();
			var pid = $(this).parents('.fb-detail').attr('data-rid');
			$(this).siblings('.sj-text').find('a').attr('data-pid', pid);
			$(':input[name="reply"]').val('');
		}else{
			$(this).siblings('.sj-text').hide();
			$(this).siblings('.sj-text').find('a').removeAttr('data-pid');
		}
	})
	// 楼中楼提交
	.on('click', '.replyBtn', function(){
		if(JC.submitted) return;
		JC.submitted = 1;
		var pid = $(this).attr('data-pid');
		var oid = $(this).attr('data-oid') || pid;
		JC.body = $(this).parents('.sj-text').find('input').val().replace(/回复\s+(.*?)\:/g, '');
		if(oid != pid){
			var replyuser = $('div[data-rid="'+pid+'"]:eq(0)').find('.reply').attr('data-author');
			JC.body = JC.body.replace('@'+ replyuser +':', '');
			JC.data.tuid = $('.fb-bt.er-lou[data-rid="'+pid+'"]').find('.name-des:eq(0)').find('a').attr('data-uid');
			JC.data.desc = $('.fb-bt.er-lou[data-rid="'+pid+'"]').find('.name-des:eq(0)').find('.fb-des').html();
		}else{
			JC.data.tuid = $('.fb-detail[data-rid="'+pid+'"]').find('.name-des:eq(0)').find('a').attr('data-uid');
			JC.data.desc = $('.fb-detail[data-rid="'+pid+'"]').find('.name-des:eq(0)').find('.fb-des').html();
		}
		JC.checkLogin();
		JC.checkComment();
		if(JC.isErr){
			JC.isErr = 0;
			return false;
		}
		var body = {
			text: JC.body,
			pic: ""
		};
		JC.data.echotype = 2;
		JC.data.type = 2;
		JT.postComment(body, oid, pid);
	})
	// 回复楼中楼
	.on('click', '.reply', function(){
		var pid = $(this).parents('.fb-bt.er-lou').attr('data-rid');
		var oid = $(this).parents('.fb-detail').attr('data-rid');
		var replyuser = $(this).attr('data-author');
		$(this).parents('.erlou-main').find('.sj-text').show();
		$(this).parents('.erlou-main').find('.replyBtn').attr('data-pid', pid).attr('data-oid', oid);
		$(this).parents('.erlou-main').find(':input[name="reply"]').val('回复 '+replyuser+':');
		window.location.hash = '';
		window.location.hash = 'wysyj-' + oid;
	})
	// 赞
	.on('click', '.zan', function(){
		if(JC.submitted) return;
		JC.submitted = 1;
		var rid = $(this).attr('data-id');
		JC.checkLogin();
		if(JC.isErr){
			JC.isErr = 0;
			return false;
		}
		JT.agreeComment(rid);
	})
	// 删除
	.on('click', '.del', function(){
		var rid = $(this).attr('data-id');
		var isReply = $(this).prev('span').attr('class');
		JC.checkLogin();
		if(JC.isErr){
			JC.isErr = 0;
			return false;
		}
		var data = {rid:rid};
		// JC.confirmDialog('确认删除吗？', 'del', data);
		// JT.delComment(rid);
		mw.ugcwikiutil.confirmDialog('确认删除吗？',function (action) {
			if(action=="accept"){
				JT.delComment(rid,isReply);
			}
		});
	})
	// 分页页码
	.on('click', '.plPage', function(){
		var pno = $(this).attr('data-pno');
		var oid = $(this).attr('data-poid');
		if(oid > 0){
			JC.nowBoxID = $(this).parents('.fb-pl').attr('id');
		}
		JC.nextPage(pno, oid);
	})
	// 楼中楼回复清除：@xxx:
	// .on('focus', 'input[name="reply"]', function(){
		// var pid = $(this).parents('.sj-text').find('.replyBtn').attr('data-pid');
		// var oid = $(this).parents('.sj-text').find('.replyBtn').attr('data-oid');
		// if(pid != oid){
			// $(this).val('');
		// }
	// })
	// 手机加载更多
	.on('click', '.load-more,.web-xyy', function(){
		if(JT.isLoading){
			return false;
		}
		JT.isLoading = true;
		var pno = $(this).attr('data-pno');
		var oid = $(this).attr('data-poid') || 0;
		if(oid > 0){
			JC.nowBoxID = $(this).parents('.fb-pl').attr('id');
		}
		$(this).attr('data-pno', parseInt(pno)+1);
		JC.nextPage(pno, oid);
	})
	// 删除上传图片
	.on('click', '.tp-left>span', function(e){
		if(e && e.stopPropagation) {　e.stopPropagation(); }else{window.event.cancelBubble = true;}
		$('.tp-left').remove();
		JC.imgsum = 0;
		$('.tp-num>span').html('图片(0/1)');
		$(':input[type="file"]').removeAttr('disabled');
		$('#upImg').show();
	})
	// 图片放大 PC
	.on('click', '.plimg', function(){
		if(JC.isPC()){
			$.openPhotoGallery(this);
		}else{

			/*var natureW = $(this).get(0).natureWidth;
			var natureH = $(this).get(0).natureHeight;
			var containerBoxW = $('.web-pl-img').innerWidth();
			var containerBoxH = $('.web-pl-img').innerHeight();
			var renderW,renderH,ratio1=natureW/natureH,ratio2=containerBoxW/containerBoxH,left_img,top_img;


			if(ratio1>radio2){
				// 图片比例较宽
				if(natureW>containerBoxW){
					// 图片宽大于外框
					renderW = containerBoxW;
					renderH = renderW/radio1;
					left_img = 0;
					top_img = (containerBoxH - renderH)/2;
				}else{
					// 图片框小于外框
					renderW = natureW;
					renderH = renderW/radio1;
					left_img = (containerBoxW - natureW)/2;
					top_img = (containerBoxH - renderH)/2;
				}
				

			}else{
				// 图片比例较窄
				if(natureH>containerBoxH){
					// 图片高大于外框
					renderW = containerBoxH*radio1;
					renderH = containerBoxH;
					left_img = (containerBoxW - renderW)/2;
					top_img = 0;
				}else{
					// 图片高小于外框
					renderW = natureH/ratio2;
					renderH = natureH;
					left_img = (containerBoxW - natureW)/2;
					top_img = (containerBoxH - renderH)/2;
				}
			}*/

		//drag me 清除多次出现的问题
			$('.image-scroller>.preview>.indicator').remove();
			var src = $(this).attr('src');
			$("#JCbox .pl-fa-mc").css("display","block");
			$('#JCbox #container-scroller, #JCbox .web-pl-tc').find('img').attr('src', src);
			$('#JCbox div.image-scroller').imageScroller();
			$(".scroller-close-btn").click(function () {
				$("#JCbox .pl-fa-mc").css("display","none");
				$("#JCbox .image-scroller .preview").css("display","block");
			});
			JC.imgPosition();
		}
	})
	// 图片放大 wap
	.on('click', '.webpltc-m > img', function(){
		$(".pl-fa-mc").hide();
		$(".web-pl-tc").show();
	})
	// 关闭图片放大 wap
	.on('click', '.close-btn-pl-m', function(){
		$(".web-pl-tc").hide();
	})
	// 表情
	.on('click', '.talk-btn-face', function(e){
		if(!JC.uid) return;
		if(e && e.stopPropagation) {　e.stopPropagation(); }else{window.event.cancelBubble = true;}
		$(".tp-details.showh").removeClass("showh");
		if( !$(this).find('div').is(":visible") ){
			$(this).find('div').show();
		}else{
			$(this).find('div').hide();
		}
	})
	// 表情
	.on('click', '.facecont-tit>em', function(e){
		// event.stopPropagation();
		if(e && e.stopPropagation) {　e.stopPropagation(); }else{window.event.cancelBubble = true;}
		
		$('.facecont-tit>em, .facecont-cont>ul').removeClass('on');
		var className = $(this).attr('class');
		$(this).addClass('on');
		$('.facecont-cont').find('.'+className).addClass('on');
	})
	// 表情
	.on('click', '.facecont-cont>ul>li>img', function(e){
		// event.stopPropagation();
		if(e && e.stopPropagation) {　e.stopPropagation(); }else{window.event.cancelBubble = true;}
		var plInput = $(this).parents('.huifu-ta').siblings('.text-say');
		if(plInput.length > 0){
			var val = plInput.find('input').val();
			plInput.find('input').val(val+'['+ $(this).attr('alt') +']');
		}else{
			var val = $('#jcomment').val();
			$('#jcomment').val(val+'['+ $(this).attr('alt') +']');
		}
	})
	if($(window).width()<992)
	{
	// 手机端隐藏分享
	$("body").on('focus', '#jcomment,#scBox,input[name="reply"]', function(){
		$(".wechat-share").hide();
	})
	// 手机端显示分享
	$("body").on('blur', '#jcomment,#scBox,input[name="reply"]', function(){
		$(".wechat-share").show();
	})}
	var Qiniu = new QiniuJsSDK();
	var uploader = Qiniu.uploader({
		runtimes: 'html5,flash,html4',
		browse_button: 'upImg',//pltp
		uptoken:$('#uptoken').val(),
		domain: JT.picdomain,
		container:'szlistBtn',
		max_file_size: '2mb',
		flash_swf_url: '',
		max_retries: 3,
		dragdrop: false,
		chunk_size: '4mb',
		auto_start: true,
		multi_selection:false,
		filters: {
			mime_types : [{ title : "Image files", extensions : "jpg,jpeg,gif,png" }],
			// prevent_duplicates : true //不允许选取重复文件
		},
		
		init: {
			'FilesAdded': function(up, files) {
				//plupload.each(files, function(file){});
			},
			'BeforeUpload': function(up, file) {
				
			},
			'UploadProgress': function(up, file) {},
			'FileUploaded': function(up, file, info) {
				var domain = up.getOption('domain');
				var res = JSON.parse(info);
				var sourceLink = domain + res.key+'?imageView2/1';
				$('.tp-left').remove();
				var imgHtml = '<div class="tp-left"><img src="'+sourceLink+'" width="171" height="129"><span></span></div>';
				$('#upImg').before(imgHtml);
				$('.tp-num>span').html('图片(1/1)');
				$(':input[type="file"]').attr('disabled', true);
				$('#upImg').hide();
				JC.imgsum = 1;
			},
			'Error': function(up, err, errTip) {
				$('.addpic-sure:eq(1)').attr('data-bt', 'true');
				if(err.status == 401){
					JC.errDialog('操作超时，请您刷新页面', false);
				}else{
					JC.errDialog(errTip, false);
				}
			},
			'UploadComplete': function() {},
			'Key': function(up, file) {
				var myDate = new Date();
				var ext = file.type.substr(file.type.indexOf('/')+1);
				var key = JT.wikikey+'/'+myDate.getFullYear()+''+myDate.getMonth()+'/'+myDate.getDate()+''+myDate.getTime()+''+Math.round(Math.random()*1000)+'.'+ext;
				return key;
			}
		}
	});
});
