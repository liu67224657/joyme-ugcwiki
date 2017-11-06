jQuery( document ).ready( function() {
	
	//init - 
	var cid = $('#pageContributeId').val();
	var uid = $('#pageContributeUser').attr('data-uid');
	var pageid = mw.config.get('wgArticleId');
	
	var current_point = 0;

	var uc_url = 'http://www.joyme.'+window.wgWikiCom+'/usercenter/page?pid=';
	var pageContribute = {
		init: function(){
			if(cid == undefined){
				return false;
			}
	var timer_ajax = setInterval(function(){
		if(mw.util){
			clearInterval(timer_ajax);
			jQuery.post(
					mw.util.wikiScript(), {
					action: 'ajax',
					rs: 'wfPageContributeList',
					rsargs: [cid]
				},
				function(data) {
					data = eval("("+data+")");
					if(data['rs'] == '1'){
						current_point = data['data']['point'];
						if(data['data']['count']>0){
							$('.list-prostraters').show();
							$('.prostrate-num').html('共膜拜'+data['data']['count']+'次');
							$liststr = '';
							for(var i in data['data']['list'] ){
								$liststr+='<li class="prostraters fl">'
			                        +'<a class="userinfo" href="'+uc_url+data['data']['list'][i]['uno']
									+'" data-uid="'+data['data']['list'][i]['uid']
									+'"data-username="'+data['data']['list'][i]['uno']
									+'"><img class="prostraters-img" src="'+data['data']['list'][i]['icon']+'"/></a>'
			                        +'</li>';
							}
							$('.prostraters-ul').html($liststr);
							
							// 膜拜右侧头像滚动

							var timer_h = setInterval(function(){
								if($.horizontalMove){
									clearInterval(timer_h);
									$.horizontalMove({
									    parent : $('.prostraters-box'),
									    sonUl : $('.prostraters-ul'), // 滑动的块
									    curShowNum : 5, // 显示区内的个数；
									    leftBtn :'.prostraters-left',
									    rightBtn : '.prostraters-right',
									    sonMargin:8
									});
									timer_h = null;
								}
							},800)
							
						}else{
							$('.list-prostraters').hide();
						}
						
					}else{
						$('.list-prostraters').hide();
					}
				}
			);
		}
	},300)		
			
		}
	};

	pageContribute.init();
	// 感谢
	$('.thank-btn').on('click',function(){
		
		if(cid == 0){
			//mw.ugcwikiutil.autoCloseDialog("数据错误，不能进行感谢！");
			return false;
		}
		
		if(mw.config.get('wgUserId') == null){
			loginDiv();
			return false;
		}
		
	    //ajax请求 
        // 1请勿重复感谢（只能感谢一次）
        //mw.ugcwikiutil.autoCloseDialog("请勿重复感谢");
        // 2感谢
		var uid = $('#pageContributeUser').attr('data-uid');
		var pageid = mw.config.get('wgArticleId');
		jQuery.post(
			mw.util.wikiScript(), {
				action: 'ajax',
				rs: 'wfPageContribute',
				rsargs: [pageid,uid,1]
			},
			function(data) {
				data = eval("("+data+")");
				if(data['rs'] == '1'){
					mw.ugcwikiutil.autoCloseDialog("已感谢");
				}else{
					mw.ugcwikiutil.autoCloseDialog(data['data']);
				}
			}
		);
	    
	    
	});
	
	// 膜拜
	$('.prostrate-btn').on('click',function(){
		
		if(cid == 0){
			//mw.ugcwikiutil.autoCloseDialog("数据错误，不能进行膜拜！");
			return false;
		}
		
		if(mw.config.get('wgUserId') == null){
			loginDiv();
			return false;
		}
		
		
	    //ajax获取数据 
        // 1用户积分不足膜拜1次时弹出窗口
		var userCredits = current_point;
		
		if(userCredits < 50){
			mw.ugcwikiutil.autoCloseDialog('<div style="text-align:center;">积分不足</div><div style="text-align:center;">( 膜拜将消耗50积分，可用积分：<i class="sur-num">'+userCredits+'</i> )</div>');
			return false;
		}
		$('.new-window-content').remove();

        // 2用户积分足够膜拜1次时弹出窗口
        var content ='<div class="new-window-content">'+
                  '<div class="new-window-line">'+
                    '<span class="label-word">膜拜次数:</span>'+
                    '<div class="label-input">'+
                      '<span class="fuhao fuhao-unplus noclick">-</span>'+
                      '<input class="fuhao fuhao-times" readonly="readonly" name="times" type="text" value="1">'+
                      '<span class="fuhao fuhao-plus">+</span>'+
                    '</div>'+
                  '</div>'+
                  '<div class="new-window-line">'+
                    '<span class="label-word">消耗积分:</span>'+
                    '<div class="label-input">'+
                      '<span class="showPoint">50</span>'+
                      '<span class="surplus">(可用积分：<i class="sur-num">'+userCredits+'</i>)</span>'+
                    '</div>'+
                  '</div>'+
                '</div>';
        mw.ugcwikiutil.confirmDialog({
            "contenthtml":content,
            "openedCb":function(){
                var parent = $('.new-window-content');
                var step = 50; // 膜拜一次消耗的积分 
                var surnum = parent.find('.sur-num').text()*1 // 剩余积分
                
                $('.fuhao').unbind().click(function(){
                    var times = parent.find('.fuhao-times');
                    var value = times.val();
                    var showPoint = value*step+step;
                    if($(this).hasClass('fuhao-plus')){
                        if($(this).hasClass("noclick")) return;
                        if(showPoint < surnum){
                            // 积分充足
                            $(this).siblings('.fuhao-unplus').removeClass('noclick');
                            value++;
                            showPoint = value*step;
                            times.val(value);
                            parent.find('.showPoint').text(showPoint);
                            
                        }else{
                            // 积分不足
                            $(this).addClass('noclick');
                        }
                    }else if($(this).hasClass('fuhao-unplus')){
                        if($(this).hasClass("noclick")) return;
                        if(value>=2){
                            $(this).siblings('.fuhao-plus').removeClass('noclick');
                            value--;
                            times.val(value);
                            showPoint = value*step;
                            parent.find('.showPoint').text(showPoint);
                        }else{
                            // 膜拜次数不小于1次
                            $(this).addClass('noclick');
                            
                        }
                    }
                    
                })
                
            }

        },function(){
        	var uid = $('#pageContributeUser').attr('data-uid');
    		var pageid = mw.config.get('wgArticleId');
    		var times = $('.fuhao-times').val(); //--这里有问题哦
    		jQuery.post(
    				mw.util.wikiScript(), {
    				action: 'ajax',
    				rs: 'wfPageContribute',
    				rsargs: [pageid,uid,2,times]
    			},
    			function(data) {
    				data = eval("("+data+")");
    				if(data['rs'] == '1'){
    					mw.ugcwikiutil.autoCloseDialog("已膜拜");
    					current_point = current_point-50*times;
    				}else{
    					mw.ugcwikiutil.autoCloseDialog('操作失败，可能由于积分不足引起，请重新操作');
    				}
    				pageContribute.init();
    			}
    		);

        });
	        
	});
} );