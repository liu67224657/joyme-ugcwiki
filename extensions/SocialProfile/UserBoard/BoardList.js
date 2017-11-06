var ws;
var BoardList = {
	//mw.config.get('wgUserId')
	times:0,
	init: function() 
	{
    // 连接服务端
       // 创建websocket
	   console.log('websocket connect times:'+BoardList.times);
       ws = new WebSocket($('#UserBoardWebSocketUrl').val());

       // 当socket连接打开时，输入用户名
       ws.onopen = BoardList.onopen;
       // 当有消息时根据消息类型显示不同信息
       ws.onmessage = BoardList.onmessage; 
       ws.onclose = BoardList.onclose;
      
       ws.onerror = BoardList.onerror;
       
     //初始化表情
       $('#board_list li .item-r-text').each(function(i,v){
    	   $(v).html(BoardList.xssFilter($(v).html()));
       });
       
    },
    onerror: function() 
    {
   	  	console.log("出现错误");
    },
    onclose: function() 
    {
    	if(BoardList.times < 2){
    		BoardList.times++;
    		BoardList.init();
    	}else{
    		mw.ugcwikiutil.ensureDialog('聊天连接已关闭，点击确认重新连接',function (action) {
			   if(action=="accept"){
				   BoardList.init();
			   }
			});
    	}
    },

    // 连接建立时发送登录信息
    onopen: function()
    {
        if(!mw.config.get('wgUserId'))
        {
            return false;
        }
        // 登录
        var login_data = '{"type":"login","uid":"'+mw.config.get('wgUserId')+'"}';
        console.log("websocket握手成功，发送登录数据:"+login_data);
        BoardList.times=0;
        ws.send(login_data);
    },

    // 服务端发来消息时
    onmessage:function(e)
    {
        //console.log(e.data);
        var data = eval("("+e.data+")");
        switch(data['type']){
            // 服务端ping客户端
            case 'ping':
                ws.send('{"type":"pong"}');
                break;;
            // 登录 更新用户列表
            case 'login':
                //console.log(data['client_name']+"登录成功");
                break;
            // 发言
            case 'say':
            	BoardList.say(data);
                break;
        }
    },

    // 发言
    say:function(data){
    	content = BoardList.xssFilter(data['content']);

    	if(data['isfollow'] == $('#boardtype').val()){
	    	if( $('#board_uid_'+data['from_client_id']).length > 0 ){
	    		var tmp = $('#board_uid_'+data['from_client_id']).clone(true);
	    		$('#board_uid_'+data['from_client_id']).remove();
	    		$("#board_list").prepend(tmp);
		    	$('#board_uid_'+data['from_client_id']).find('b[class*="time-stamp"]').html(data['time']);
		    	$('#board_uid_'+data['from_client_id']).find('div[class="item-r-text"]').html(content);
		    	var newcountobj = $('#board_uid_'+data['from_client_id']).find('i[class*="news-count"]');
		    	if(newcountobj.length > 0){
		    		if(parseInt($(newcountobj).html())+1>99){
		    			$(newcountobj).addClass('on');
		    		}
		    		$(newcountobj).html((parseInt($(newcountobj).html())+1)>99?99:parseInt($(newcountobj).html())+1);
		    	}else{
		    		$('#board_uid_'+data['from_client_id']).find('cite[class="board-headicon"]').append('<i class="news-count">1</i>');
		    	}
		    	
	    	}else{
	    		var $output='<li id="board_uid_'+data['from_client_id']+'">'+
	    		'<a href="'+$('#boardpageurl').val()+data['from_client_id']+'">'+
		            '<div class="list-item-l">'+
		                '<cite><img src="'+data['from_client_headicon']+'"></cite><i class="news-count">1</i>'+
		            '</div>'+
		            '<div class="list-item-r">'+
		                '<div class="item-r-name fn-clear">'+
		                    '<span class="fn-left">'+data['from_client_name']+'</span>'+
		                    '<b class="time-stamp fn-right">'+data['time']+'</b>'+
		                '</div>'+
		                '<div class="item-r-text">'+
		                content+
		                '</div>'+
		              '</div>'+
		            '</a>'+
		            '<i class="del-icon" data-uid="'+data['from_client_id']+'"></i>'+
	            '</li>';
	    		if($('div[class="no-data"]').length == 1){
	    			$("#board_list").html('');
	    		}
	    		$("#board_list").prepend($output);
	    	}
    	}
    	if(data['isfollow'] == '1'){
    		if($('#boardcount').length > 0){
				$('#boardcount').html(BoardList.countsub(parseInt($('#boardcount').html())+1));
				if(parseInt($('#boardcount').html())+1>99){
	    			$('#boardcount').addClass('on');
	    		}
			}else{
				$('#boardcount_a').append('<i id="boardcount">1</i>');
			}
    	}else{
    		if($('#preboardcount').length > 0){
				$('#preboardcount').html(BoardList.countsub(parseInt($('#preboardcount').html())+1));
				if(parseInt($('#preboardcount').html())+1>99){
	    			$('#preboardcount').addClass('on');
	    		}
			}else{
				$('#preboardcount_a').append('<i id="preboardcount">1</i>');
			}
    	}
    	if($('#boardtotal').length > 0){
    		$('#boardtotal').html(BoardList.countsub(parseInt($('#boardtotal').html())+1));
    		if(parseInt($('#boardtotal').html())+1>99){
    			$('#boardtotal').addClass('on');
    		}
    	}else{
    		$('#boardtotal_a').prepend('<b id="boardtotal">1</b>');
    	}
    	
    },
    clearMessage: function(fid,type) {
    	
    	if(!type){
    		var confirmsstr = '确定删除与该用户的所有私信内容？删除私信内容将不可恢复';
    	}else{
    		var confirmsstr = '确认要清空所有对话吗？删除私信内容将不可恢复';
    	}
    	mw.ugcwikiutil.confirmDialog(confirmsstr,function (action) {
		   if(action=="accept"){
			   jQuery.post(
	  				mediaWiki.util.wikiScript(), {
	  					action: 'ajax',
	  					rs: 'wfClearBoardMessage',
	  					rsargs: [fid,type]
	  				},
	  				function() {
	  					if(fid){
	  						$('#board_uid_'+fid).remove();
	  						if($('#board_list li').length <=0){
	  							window.location.href="/home/特殊:私信列表";
	  						}
	  					}else{
	  						$('#board_list').html('');
	  						$('.boardpage').remove();
	  						window.location.href="/home/特殊:私信列表";
	  					}
	  					
	  				}
	  			);
		   }
		});
	},
    xssFilter: function(val) {
    	val = decodeURIComponent(val);
    	if(val.indexOf("<br />") > 0){
    		val = val.substr(0,val.indexOf("<br />"));
    	}
    	val = val.replace(/http:\/\/(.*)\.(JPG|PNG|GIF|JPEG)/ig, '[图片]');
        //val = val.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\x22/g, '&quot;').replace(/\x27/g, '&#39;');
        return val;
    },
    countsub:function(num){
    	return (num>99?99:num).toString();
    }
};

jQuery( document ).ready( function() {
	
	BoardList.init();
	console.log('boardlist init');
	
	LoadMore.init('list-item','wfUserViewBoardList',$('#boardtype').val());

	// "Delete" link
	 $('body').on('click','.del-icon', function() {
		BoardList.clearMessage( $(this).attr('data-uid') );
	} );
	 $('body').on('click','#clearBoardAll', function() {
		BoardList.clearMessage('',$('#boardtype').val());
	} );
	
	
} );
