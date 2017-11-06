//JLike.js
var JL = {
	submitted: 0,
	articleId : mw.config.get('wgArticleId'),
	uid : mw.config.get('wgUserId') || 0,
	clickBt : 0,
	scBt : 0,
	scClick : 0,
	
	init : function(){
		// short comments + click like
		var timer = setInterval(function(){
			if(mediaWiki.util){
				clearInterval(timer);
				$.post(
					mediaWiki.util.wikiScript(), {
						action: 'ajax',
						rs: 'wfGetPageAddons',
						rsargs: [JL.articleId]
					},
					function(data){
						var data = JSON.parse(data);
						if(data.rs == 1){
							//短评
							var color = ['tbb', 'jgl', 'zb', 'tbzb'];
							var lis = '';
							if(data.data.list.length > 0){
								var list = data.data.list;
								var len = y = 0;
								for(var i=0; i<list.length; i++){
									var index = Math.floor((Math.random()*color.length));
									len += list[i].body.length;
									var dis = '';
									if( len > 50 ){
										dis = 'style="display:none;"';
									}
									var numf = JL.numBig(list[i].like_count);
									lis += '<span class="'+color[index]+'" data-id="'+ list[i].psc_id +'" '+ dis +'>'+list[i].body+'(<em>'+ numf +'</em>)</span>';
								}
							}
							if( len > 50 && y == 0){
								lis += '<span class="more">展开&gt;</span>';
								dis = 'style="display:none;"';
								y = 1;
							}
							var html = '<div class="nr-dp"><h3>内容短评:</h3><p class="tjpl clearfix"><input type="text" class="text" placeholder="最多10个字" id="scBox"><input type="button" class="button" value="发布短评" id="scBtn"></p><p class="dp-des">'+ lis + '</p><div class="fn-clear"></div><p></p></div>';
							$('#duanping').append(html);
							
							// click like
							var numf = JL.numBig(data.data.like_count);
							$('.dz-num').html( numf );
							//热度值显示
							var arr,reg=new RegExp("(^| )Joyme_heat_num_"+mw.config.get('wgArticleId')+"_"+window.wgWikiname+window.wgWikiCom+"=([^;]*)(;|$)");
							if(arr=document.cookie.match(reg)){
								$('.page-hot-num').html(parseInt(data.data.countnum)+parseInt(unescape(arr[2])));
							}else{
								$('.page-hot-num').html(parseInt(data.data.countnum));
							}
							//$('span.wikizan>a').html( '赞:' + numf );
							//$('span.wikizan').show();
						}else{
							console.log(data);
						}
					}
				);
				timer = null;
			}
		},300)
	},
	errDialog : function(msg){
		JL.scBt = 0;
		mw.ugcwikiutil.autoCloseDialog(msg);
		JL.isErr = 0;
	},
	// 点赞
	clickLike : function(){
		var msgData = {};
		msgData.tuid = $('#wgArticleUserID').attr('data-uid');
		msgData.echotype = 1;
		msgData.type = 2;
		msgData.desc = mw.config.get('wgTitle');
		msgData.cid = 0;
		$.post(
			mediaWiki.util.wikiScript(), {
				action: 'ajax',
				rs: 'wfClickLike',
				rsargs: [JL.articleId, JL.uid]
			},
			function(data){
				var data = JSON.parse(data);
				if(data.rs == 1){
					var num = JL.numBig(parseInt($('.dz-num').html())+1);
					$('.dz-num').html( num );
					$('.page-hot-num').html(parseInt($('.page-hot-num').html())+1);
					$('span.wikizan>a').html( '赞:' + num );
					$('span.wikizan').show();
					JL.errDialog('点赞成功');
					JL.wikiClickLikeMsg(msgData);
				}else if( data.rs == 0 ){
					JL.errDialog(data.data);
				}else{
					console.log(data);
				}
			}
		);
	},
	
	// 点赞上报
	wikiClickLikeMsg : function(data){
		
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
					JL.errDialog('usercommentdatacount error');
				}else{
					// console.log('JT.usercommentdatacount:', req);
					// JC.zanComment(rid);
					console.log('AT-req:', req);
				}
			},
			error: function() {}
		});
	},
	
	// 短评
	shortComment : function( con ){
		$.post(
			mediaWiki.util.wikiScript(), {
				action: 'ajax',
				rs: 'wfAddShortComment',
				rsargs: [JL.articleId, con, JL.uid]
			},
			function(data){
				var data = JSON.parse(data);
				if(data.rs == 1){
					var row = data.data;
					var color = ['tbb', 'jgl', 'zb', 'tbzb'];
					$('.dp-des').find('span[data-id="'+row.psc_id+'"]').remove();
					var index = Math.floor((Math.random()*color.length));
					var numf = JL.numBig(row.like_count);
					var li = '<span class="'+color[index]+'" data-id="'+ row.psc_id +'">'+row.body+'(<em>'+ numf +'</em>)</span>';
					$('.dp-des').prepend(li);
					$('#scBox').val('');
					JL.scBt = 0;
					$('.page-hot-num').html(parseInt($('.page-hot-num').html())+1);
					JL.errDialog('短评成功');
					// console.log(data);
				}if(data.rs == 2){
					var data = data.data;
					JL.scBt = 0;
					JL.errDialog(data.msg);
					console.log(data);
				}else{
					console.log(data);
					JL.scBt = 0;
				}
			}
		);
	},
	// 短评点赞
	shortCommentClickLike : function(pscid){
		$.post(
			mediaWiki.util.wikiScript(), {
				action: 'ajax',
				rs: 'wfShortCommentClickLike',
				rsargs: [JL.articleId, pscid, JL.uid]
			},
			function(data){
				var data = JSON.parse(data);
				if(data.rs == 1){
					var num = parseInt($('.dp-des').find('span[data-id="'+pscid+'"]>em').html())+1;
					num = JL.numBig(num);
					$('.dp-des').find('span[data-id="'+pscid+'"]>em').html(num);
					JL.scClick = 0;
					$('.page-hot-num').html(parseInt($('.page-hot-num').html())+1);
					JL.errDialog('短评点赞成功');
				}else{
					console.log(data);
					JL.scClick = 0;
				}
			}
		);
	},
	
	numBig : function(num){
		if(!num) return 0;
		num = parseInt(num);
		res = num>99999 ? (99999+'+') : num;
		return res;
	}
};

$( document ).ready( function() {
	
	JL.init();
	// click like
	$( 'body' ).on( 'click', '#ClickLike', function() {
		if(JL.clickBt){
			JL.errDialog('请勿重复点赞');
			return;
		}
		JL.clickBt = 1;
		JL.clickLike();
	} )
	// short comment
	.on( 'click', '#scBtn', function() {
		if(JL.scBt){
			JL.errDialog('请勿重复提交');
			return;
		}
		JL.scBt = 1;
		var con = $.trim($('#scBox').val());
		// console.log(con);
		if(con == ''){
			JL.errDialog('数据不能为空');
			return false;
		}else if( con.length > 10 ){
			JL.errDialog('不能超过10个字');
			return false;
		}
		JL.shortComment(con);
	} )
	// show more dp-des
	.on( 'click', '.dp-des>span', function() {
		var pscid = $(this).attr('data-id');
		if(typeof(pscid) == 'undefined'){
			var bt = $(this).html();
			if(bt == '收起'){
				var len = 0;
				$('.dp-des>span').each(function(i){
					var taghtml = $(this).html();
					len += taghtml.replace(/\(.*?\)/, '').length;
					if( len > 50 && taghtml != '收起' ){
						$(this).hide();
					}
				});
				$(this).html('展开');
			}else{
				$('.dp-des>span').show();
				$(this).html('收起');
			}
			return false;
		}
		if(JL.scClick){
			JL.errDialog('请勿重复提交');
			return;
		}
		JL.scClick = 1;
		JL.shortCommentClickLike(pscid);
	} );
} );