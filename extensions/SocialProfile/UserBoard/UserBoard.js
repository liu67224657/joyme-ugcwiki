var ws;

var uc_url = 'http://www.joyme.'+window.wgWikiCom+'/usercenter/page?pid=';
var friend_id = $('#friend_id').val();
var UserBoard = {
	page: 1,
	times:0,
	status:0,
	init: function() 
	{
    // 连接服务端
       // 创建websocket
		console.log('websocket connect times:'+UserBoard.times);
		ws = new WebSocket($('#UserBoardWebSocketUrl').val());

       // 当socket连接打开时，输入用户名
       ws.onopen = UserBoard.onopen;
       // 当有消息时根据消息类型显示不同信息
       ws.onmessage = UserBoard.onmessage; 
       ws.onclose = UserBoard.onclose;
      
       ws.onerror = UserBoard.onerror;
       
       $("#board_list").scrollTop($("#board_list")[0].scrollHeight);
       
       
    },
    onerror: function() 
    {
    	UserBoard.status = 0;
   	  	console.log("出现错误");
    },
    onclose: function() 
    {
    	UserBoard.status = 0;
    	if(UserBoard.times < 2){
    		UserBoard.times++;
    		UserBoard.init();
    	}else{
    		mw.ugcwikiutil.ensureDialog('聊天连接已关闭，点击确认重新连接',function (action) {
    			UserBoard.init();
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
        var login_data = '{"type":"login","uid":"'+mw.config.get('wgUserId')+'","to_uid":"'+friend_id+'"}';
        console.log("websocket握手成功，开始初始化");
        UserBoard.times=0;
        UserBoard.status = 1;
        UserBoard.send(login_data);
    },

    // 服务端发来消息时
    onmessage:function(e)
    {
        //console.log(e.data);
        var data = eval("("+e.data+")");
        switch(data['type']){
            // 服务端ping客户端
            case 'ping':
            	UserBoard.send('{"type":"pong"}');
                break;;
            // 登录 更新用户列表
            case 'login':
                console.log("初始化成功-.-");
                if(data['content'] != ''){
                	var html = '';
                	for(var i in data['content']){
	                	uuid = data['content'][i]['id'];
	                	content = UserBoard.xssFilter(data['content'][i]['message_text']);
	                	html += "<div id=\"user_board_msgid_"+uuid+"\" class=\"user-board-message talk-l\"><cite><img src=\""+profilelist[friend_id].icon+"\"><span class='chat-xt-def chat-xt-01 chat-xt-02 chat-xt-03 chat-xt-04 chat-xt-05 chat-xt-06 chat-xt-07'></span></cite><div class='chat-group-def chat-group-01 chat-group-02 chat-group-03 chat-group-04 chat-group-05 chat-group-06 chat-group-07'><i class='chat-group-icon-def icon1'></i><i class='chat-group-icon-def icon2'></i><i class='chat-group-icon-def icon3'></i><i class='chat-group-icon-def icon4'></i>"+content+"</div></div>";
                	};
                	$("#board_list").append(html);
	            	$(".talk-cont-con").scrollTop($("#board_list")[0].scrollHeight);
                }
                break;
             // 发言
            case 'say':
            	
            	if(data['from_client_id'] == friend_id){
            		UserBoard.say(data,'l','friend_icon');
            		UserBoard.send('{"type":"getmsg","uid":"'+mw.config.get('wgUserId')+'","to_uid":"'+friend_id+'","id":"'+data['id']+'"}');
            	}else if(data['from_client_id'] == mw.config.get('wgUserId')){
            		//
            		if(data['code'] == '0'){
            			mw.ugcwikiutil.msgDialog('对方已关闭私信功能');
            			$('.cancel-flo').addClass('on');
            		}else{
            			$('#user_board_uuid_'+data['uuid']).find('.sending,.send-error').remove();
            			//UserBoard.say(data,'r','user_icon');
            		}
            		
            	}else{
            		//消息数 增加
            		if(data['isfollow'] == '1'){
                		if($('#boardcount').length > 0){
            				$('#boardcount').html(UserBoard.countsub(parseInt($('#boardcount').html())+1));
            				if(parseInt($('#boardcount').html())+1>99){
            	    			$('#boardcount').addClass('on');
            	    		}
            			}else{
            				$('#boardcount_a').append('<i id="boardcount">1</i>');
            			}
                	}else{
                		if($('#preboardcount').length > 0){
            				$('#preboardcount').html(UserBoard.countsub(parseInt($('#preboardcount').html())+1));
            				if(parseInt($('#preboardcount').html())+1>99){
            	    			$('#preboardcount').addClass('on');
            	    		}
            			}else{
            				$('#preboardcount_a').append('<i id="preboardcount">1</i>');
            			}
                	}
                	if($('#boardtotal').length > 0){
                		$('#boardtotal').html(UserBoard.countsub(parseInt($('#boardtotal').html())+1));
                		if(parseInt($('#boardtotal').html())+1>99){
                			$('#boardtotal').addClass('on');
                		}
                	}else{
                		$('#boardtotal_a').prepend('<b id="boardtotal">1</b>');
                	}
            	}
        		break;
             // 接收消息
            case 'getmsg':
            	//$('#user_board_msgid_'+data['id']).find('i[class="user-board-message-status"]').html('已读');
                break;
        }
    },

    // 发言
    say:function(data,talk,icon){
    	uuid = data['uuid'];
    	from_client_id = data['from_client_id'];
    	time = data['time'];
    	content = UserBoard.xssFilter(data['content']);
    	var headskin = profilelist[from_client_id].headskin==''?'':'chat-xt-def chat-xt-0'+profilelist[from_client_id].headskin;
    	var chatskin = profilelist[from_client_id].bubbleskin==''?'':'chat-group-def chat-group-0'+profilelist[from_client_id].bubbleskin;
    	var vipstr = profilelist[from_client_id].vtype>0?"<span class='user-vip' title='"+profilelist[from_client_id].vdesc+"'></span>":"";

    	var html = "<div id=\"user_board_msgid_"+uuid+"\" class=\"user-board-message talk-"+talk+"\">"
    				+"<a href='"+uc_url+profilelist[from_client_id].profileid+"' class='userinfo' data-username='"+profilelist[from_client_id].profileid+"'>"
    					+"<cite><img src=\""+profilelist[from_client_id].icon+"\"><span class='"+headskin+"'></span>"+vipstr+"</cite>"
    				+"</a>"
    				+"<div class='"+chatskin+"'><i class='chat-group-icon-def icon1'></i><i class='chat-group-icon-def icon2'></i><i class='chat-group-icon-def icon3'></i><i class='chat-group-icon-def icon4'></i>"+content+"</div></div>";
    	
    	$("#board_list").append(html);
    	$(".talk-cont-con").scrollTop($("#board_list")[0].scrollHeight);
    },
    xssFilter: function(val) {
    	val = decodeURIComponent(val);
        //val = val.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\x22/g, '&quot;').replace(/\x27/g, '&#39;');
        
    	//过滤图片
        val = val.replace(/http:\/\/(.*)\.(JPG|PNG|GIF|JPEG)/ig, "<img src='http://$1.$2?imageMogr2/auto-orient' />");
        
        //过滤表情
        val=val.replace(/\[(.*?)\]/ig,ints.getface); 
        
        
        return val;
    },
	clearMessage: function(fid) {
		if ( window.confirm( mediaWiki.msg( 'userboard_confirmdelete' ) ) ) {
			jQuery.post(
				mediaWiki.util.wikiScript(), {
					action: 'ajax',
					rs: 'wfClearBoardMessage',
					rsargs: [fid]
				},
				function() {
					$('#board_list').html('');
				}
			);
		}
	},
	sendMessage: function(){
		var message = $( '#message' ).val().trim();
		
		if(message.length <=0){
			$('#sendmsg_rs').parent().parent().addClass('on');
			$('#sendmsg_rs').html('输入内容不可以为空哦');
			return false;
		}
		
		if(message.length > 200){
			$('#sendmsg_rs').parent().parent().addClass('on');
			$('#sendmsg_rs').html('私信内容超过200字限制');
			return false;
		}
		var encodedMsg = encodeURIComponent( message );
		var to_client_id = friend_id;
		UserBoard.send('{"type":"say","uuid":"'+UserBoard.getUuid()+'","icon":"'+profilelist[mw.config.get('wgUserId')].icon+'","uid":"'+mw.config.get('wgUserId')+'","to_uid":"'+to_client_id+'","content":"'+encodedMsg+'"}');
		$( '#message' ).val('');
		//$( '#message' ).focus();
		$('#sendmsg_rs').parent().parent().removeClass('on');
		$('#sendmsg_rs').html('');
	},
	send:function(msg){
		if(UserBoard.status == 0){
			console.log('连接已中断');
			return false;
		}
		msgobj = jQuery.parseJSON(msg);
		if(msgobj.type == 'say'){
			content = UserBoard.xssFilter(msgobj.content);
			var headskin = profilelist[mw.config.get('wgUserId')].headskin==''?'':'chat-xt-def chat-xt-0'+profilelist[mw.config.get('wgUserId')].headskin;
	    	var chatskin = profilelist[mw.config.get('wgUserId')].bubbleskin==''?'':'chat-group-def chat-group-0'+profilelist[mw.config.get('wgUserId')].bubbleskin;
	    	var vipstr = profilelist[mw.config.get('wgUserId')].vtype>0?"<span class='user-vip' title='"+profilelist[mw.config.get('wgUserId')].vdesc+"'></span>":"";
	    	var html = "<div id=\"user_board_uuid_"+msgobj.uuid
	    				+"\" data-content=\""+msgobj.content+"\" class=\"user-board-message talk-r\">"
	    				+"<a href='"+uc_url+profilelist[mw.config.get('wgUserId')].profileid+"' class='userinfo' data-username='"+profilelist[mw.config.get('wgUserId')].profileid+"'>"
	    					+"<cite><img src=\""+profilelist[mw.config.get('wgUserId')].icon+"\">"
	    						+"<span class='"+headskin+"'></span>"+vipstr
	    					+"</cite>"
	    				+"</a>"
	    				+"<div class='"+chatskin+"'><i class='chat-group-icon-def icon1'></i><i class='chat-group-icon-def icon2'></i><i class='chat-group-icon-def icon3'></i><i class='chat-group-icon-def icon4'></i><span class=\"sending\"></span>"
	    				+content+"</div></div>";
	    	
	    	$("#board_list").append(html);
	    	$(".talk-cont-con").scrollTop($("#board_list")[0].scrollHeight);
		}
		ws.send(msg);
		UserBoard.createTimer(msgobj.uuid);
	},
	reSend:function(uuid){
		if(UserBoard.status == 0){
			console.log('连接已中断');
			return false;
		}
		encodedMsg = $('#'+uuid).attr('data-content');
		var to_client_id = friend_id;
		$('#'+uuid).remove();
		UserBoard.send('{"type":"say","uuid":"'
				+UserBoard.getUuid()
				+'","icon":"'+profilelist[mw.config.get('wgUserId')].icon
				+'","uid":"'+mw.config.get('wgUserId')
				+'","to_uid":"'+to_client_id
				+'","content":"'+encodedMsg+'"}');
	},
	createTimer:function(uuid){
		var i = 0;
		var timer = setInterval(function(){
			if(i>=3){
				clearInterval(timer);
				$('#user_board_uuid_'+uuid).find('.sending').addClass('send-error');
				$('#user_board_uuid_'+uuid).find('.sending').removeClass('sending');
			}
			i++;
		},1000);
	},
	getUuid:function(){
	  var len=32;//32长度
	  var radix=16;//16进制
	  var chars='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split('');
	  var uuid=[],i;radix=radix||chars.length;
	  if(len){
		  for(i=0;i<len;i++)
			  uuid[i]=chars[0|Math.random()*radix];
		  }
	  else{
		  var r;uuid[8]=uuid[13]=uuid[18]=uuid[23]='-';uuid[14]='4';
		  for(i=0;i<36;i++){
			  if(!uuid[i]){r=0|Math.random()*16;uuid[i]=chars[(i==19)?(r&0x3)|0x8:r];}
		  }
	  }
	  return uuid.join('');
	},
	getMessage: function(){
		jQuery.post(
			mediaWiki.util.wikiScript(), {
				action: 'ajax',
				rs: 'wfGetBoardMessage',
				rsargs: [friend_id,UserBoard.page]
			},
			function(data) {
				data = eval("("+data+")");
				if(data['rs'] == '-1'){
					$('#getboardmessage').parent().after('<p class="talk-time">没有任何消息</p>');
					$('#getboardmessage').remove();
				}else{
					var content = UserBoard.xssFilter(data['data']);
					$('#getboardmessage').parent().after(content);
					UserBoard.page++;
				}
			}
		);
	},
    countsub:function(num){
    	return (num>99?99:num).toString();
    }
};

var ints={
	facelist:{},
    talk:function(){
       ints.talkFace('http://api.joyme.com/json/mood','.talk-btn-facecont');//表情渲染
       ints.talkBtn('.talk-btns','.talk-btn-box');//表情图片切换
       ints.talkTab('.facecont-tit','.facecont-cont','on');//表情
       ints.faceText();//表情加到输入框

    },
    getface:function(face){
    	var iface = face.replace('[','').replace(']','');
    	//console.log(face);
    	if(ints.facelist[iface] != undefined){
    		return '<img src="'+ints.facelist[iface]+'" />';
    	}else{
    		return face;
    	}
    },
    faceHtml:'',
    talkFace:function(dataurl,parent){
       $.ajax({
             url:dataurl,
             type: "post",
             async: false,
             //data: {unikey:window.uniKey, domain:window.domain, rid:rid},
             dataType: "jsonp",
             //jsonpCallback: "agreecallback",
             success: function (req) {
                if (req.rs == 1) {
                   var facearr = req.result;
                   var tabtit = '', tabcont = '';
                   var strs = { tabicon: "", tabicon1: "", tabicon2: "" };
                   var table = { smiley: "tabicon", pansite: "tabicon1", def: "tabicon2" };

                   var num = 10;
                   var page = 0;
                   for (var i in facearr) {
                       tabtit += '<em class="' + i + '">' + i + '</em>';

                       var ii = table[i] || table.def;

                       page = Math.ceil(facearr[i].length / 10);
                       for (var j = 0; j < page; j++) {
                           //strs[ii] += '<tr>'
                           for (var k = j * num; k < (j + 1) * num; k++) {
                               if (k < facearr[i].length) {
                            	   ints.facelist[facearr[i][k].code] = facearr[i][k].pic;
                            	   //console.log(facearr[i][k].code);
                                   strs[ii] += '<span data-name="'+facearr[i][k].code+'"><img src="' + facearr[i][k].pic + '" alt="' + facearr[i][k].code + '" /></span>';
                               }
                           }
                           //strs[ii] += '</tr>';
                       };
                       tabcont += '<div class="' + i + '">' + strs[ii] + '</div>'
                   }
                   $(parent).each(function(){
                     $(this).children('span').html(tabtit);
                     $(this).find('em:first').addClass('on');
                   });
                    $(parent).each(function(){
                     $(this).children('div').html(tabcont);
                     $(this).find('div div:first').addClass('on');
                   });
                   ints.faceHtml+=$('.talk-btn-facecont:first').html();
                   //初始化表情
                   $('.user-board-message div').each(function(i,v){
                	   $(v).html(UserBoard.xssFilter($(v).html()));
                   });
               }; 
             },
             error: function () {
                   //setErrMsg('agreeComment程序错误');
             }
       });
    },
    talkBtn:function(parent,boxs){
       $(parent).on('click',function(event){
         if(event||event.stopPropagation){
              event.stopPropagation();
         }else{
             window.event.cancelBubble=true;     
         };
           $(this).children('div').show();
           $(this).siblings().children('div').hide();
       });
       $(document).on('click',function(){
          $(boxs).hide();
       })
    },
    faceText:function(){
      $('.facecont-cont').on('click','span',function(event){
       if(event||event.stopPropagation){
              event.stopPropagation();
         }else{
             window.event.cancelBubble=true;     
         };
       var vals='['+$(this).attr('data-name')+']';
       var parents=$(this).parents('.talk-btn-box');
       var textbox;
       parents.hide();
       textbox=$('#message');
       showText(vals,textbox);
      });
      function showText(vals,text){
       var vals;
       if(text.val()===""){
         text.prev('.talk-mr').hide();
         vals=text.val()+vals;
       }else{
         vals=text.val()+vals;
       }
       text.val(vals);
      };
    },
    talkTab:function(tabmenu,tabcont,classn){
       $(tabmenu).each(function(){
         $(this).on('click','em',function(){
             var inds=$(this).index();
             $(this).addClass(classn).siblings().removeClass(classn);
             $(tabcont).each(function(){
               $(this).children('div').eq(inds).addClass(classn).siblings().removeClass(classn);
             })
         })
       });
    }
};


jQuery( document ).ready( function() {
	
	UserBoard.init();
	console.log('board init');
	ints.talk();
	ub_upload();
	
	// "Delete" link
	jQuery( '#clearboardmessage' ).on( 'click', function() {
		//UserBoard.clearMessage( $('#friend_id').val() );
	} );

	// Submit button
	jQuery( '#sendmsg' ).on( 'click', function() {
		UserBoard.sendMessage();
	} );
	
	// get message
	jQuery( '#getboardmessage' ).on( 'click', function() {
		UserBoard.getMessage();
	} );
	
	// resend message
	jQuery( '.talk-box' ).on( 'click', 'span.send-error', function() {
		UserBoard.reSend($(this).parent().parent().attr('id'));
	} );
	
	
	function ub_upload(){
		var Qiniu = new QiniuJsSDK();
		var uploader = Qiniu.uploader({
		    runtimes: 'html5,html4',      // 上传模式，依次退化
		    browse_button: 'commentImg',         // 上传选择的点选按钮，必需
		    unique_names: false,
		    uptoken : $('#uptoken').val(), // uptoken是上传凭证，由其他程序生成
		    //get_new_uptoken: false,             // 设置上传文件的时候是否每次都重新获取新的uptoken
		    domain: 'http://'+$('#qiniu_domain').val(),     // bucket域名，下载资源时用到，必需
		    container: 'szlistBtn',             // 上传区域DOM ID，默认是browser_button的父元素
		    flash_swf_url: '',  //引入flash,相对路径
		    max_file_size: '2mb',             // 最大文件体积限制
		    max_retries: 3,                     // 上传失败最大重试次数
		    dragdrop: false,                     // 开启可拖曳上传
		    drop_element: 'message',          // 拖曳上传区域元素的ID，拖曳文件或文件夹后可触发上传
		    chunk_size: '2mb',                  // 分块上传时，每块的体积
		    auto_start: true,                   // 选择文件后自动上传，若关闭需要自己绑定事件触发上传
		    multi_selection: false,
		    filters : {
		        //prevent_duplicates: true,
		        // Specify what files to browse for
		        mime_types: [
		           {title : "Image files", extensions : "jpg,gif,png"}, // 限定jpg,gif,png后缀上传
		        ]
		    },
		    init: {
		        'FilesAdded': function(up, files) {
		        	var i = 1;
                    plupload.each(files, function (file) {
                        files.splice(i,1);
                        // 文件添加进队列后,处理相关的事情
                    });
		        },
		        'BeforeUpload': function(up, file) {
		        },
		        'UploadProgress': function(up, file) {
		               // 每个文件上传时，处理相关的事情
		        },
		        'FileUploaded': function(up, file, info) {
		               // 每个文件上传成功后，处理相关的事情
		               var domain = up.getOption('domain');
		               var res = Qiniu.parseJSON(info);
		               var sourceLink = domain + '/' + res.key + '';
				   		var encodedMsg = encodeURIComponent( sourceLink );
				   		var to_client_id = friend_id;
				   		UserBoard.send('{"type":"say","uuid":"'+UserBoard.getUuid()+'","icon":"'+profilelist[mw.config.get('wgUserId')].icon+'","uid":"'+mw.config.get('wgUserId')+'","to_uid":"'+to_client_id+'","content":"'+encodedMsg+'"}');
				   		$( '#message' ).focus();
				   		$('#sendmsg_rs').parent().parent().removeClass('on');
						$('#sendmsg_rs').html('');
		        },
		        'Error': function(up, err, errTip) {
		            //上传出错时，处理相关的事情
		        	if(err.code==plupload.FILE_SIZE_ERROR){
		        		$('#sendmsg_rs').parent().parent().addClass('on');
    					$('#sendmsg_rs').html("请上传小于2M的图片");
		        	}else{
		        		$('#sendmsg_rs').parent().parent().addClass('on');
						$('#sendmsg_rs').html(errTip);
		        	}
		        	
		        },
		        'UploadComplete': function() {
		               //队列文件处理完毕后，处理相关的事情
		        },
		        'Key': function(up, file) {
		            // 若想在前端对每个文件的key进行个性化处理，可以配置该函数
		            // 该配置必须要在unique_names: false，save_key: false时才生效
		        	var type = file.name.substring(file.name.lastIndexOf('.'),file.name.length).toUpperCase();
		        	var myDate = new Date();
		            var key = "wiki/"+myDate.getFullYear()+myDate.getMonth()+myDate.getDate()+'/'+myDate.getTime()+Math.floor(Math.random()*1000)+type;
		            // do something with key here
		            return key;
		        }
		    }
		});
	}
	
} );
