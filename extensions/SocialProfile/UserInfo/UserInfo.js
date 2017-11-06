var profileId = window.wgUserProfileId;
var env = window.wgWikiCom;
var UserInfo = {

	followapiurl:'http://api.joyme.'+env+"/api/usercenter/relation/follow",
	unfollowapiurl:'http://api.joyme.'+env+"/api/usercenter/relation/unfollow",
	infoapiurl  : 'http://api.joyme.'+env+'/joyme/json/usercenter/userinfo',
	
	homeurl   : "http://www.joyme." + env + "/usercenter/home",
    followurl : "http://www.joyme." + env + "/usercenter/follow/mylist",
    fansurl   : "http://www.joyme." + env + "/usercenter/fans/mylist",
    editsurl  : "http://wiki.joyme." + env + "/home/index.php?title=%E7%89%B9%E6%AE%8A:%E7%9D%80%E8%BF%B7%E8%B4%A1%E7%8C%AE&userid=",
    chaturl   : "http://wiki.joyme." + env + "/home/index.php?title=%E7%89%B9%E6%AE%8A:%E7%A7%81%E4%BF%A1&fid=",
    relation : '',
    
    getdata: function(obj,pagex,pagey) {
    	var tempProfileId = username = obj.attr('data-username');
    	if($('.wiki-person-popup[data-username="'+username+'"]').length>0){
    		$('.wiki-person-popup[data-username="'+username+'"]').show();
    		UserInfo.position(obj,pagex,pagey);
    	}else{
    		$.ajax({
                url: UserInfo.infoapiurl,
                type: "get",
                data: {profileId: username},
                dataType: "jsonp",
                jsonpCallback: "callback",
                success: function (req) {
                    var resMsg = req[0];
                    if (resMsg.rs == '1') {
                        var rs = resMsg.result;
                        if (rs.followStatus == 0) {
                        	UserInfo.relation = '关注';
                        } else if (rs.followStatus == '1') {
                        	UserInfo.relation = '已关注';
                        } else if (rs.followStatus == '2') {
                        	UserInfo.relation = '互相关注';
                        }
                        
                        if (profileId != tempProfileId) {
                        	UserInfo.homeurl = "http://www.joyme." + env + "/usercenter/page?pid=" + tempProfileId;
                        	UserInfo.followurl = "http://www.joyme." + env + "/usercenter/follow/list?profileid=" + tempProfileId;
                        	UserInfo.fansurl = "http://www.joyme." + env + "/usercenter/fans/list?profileid=" + tempProfileId;
                        }
                        var html = "";
                        
                        html = '<div class="user-info-box wiki-person-popup" data-username="'+username+'">';
                        if (rs.cardskin != "") {
                            html += '<div class="ui-top ui-top' + rs.cardskin + '">';
                        } else {
                            html += '<div class="ui-top">';
                        }

                        html += '<div class="ui-top-img">';
                        if (rs.headskin != "") {
                            html += '<a target="_blank" href="' + UserInfo.homeurl + '"><div class="ui-top-decorate decorate' + rs.headskin + '"></div></a>';
                        } else {
                            html += '<a target="_blank" href="' + UserInfo.homeurl + '"><div class="ui-top-decorate"></div></a>';
                        }

                        if (rs.vtype != "" && rs.vtype > 0) {
                            html += '<div class="ui-vip"></div>';
                        }
                        html += '<a class="ui-top-img-a" target="_blank" href="' + UserInfo.homeurl + '"><img src="' + rs.icon + '" alt=""></a>' +
                            '</div>' +
                            '<div class="ui-name-box">' +
                            '<a class="ui-name" href="' + UserInfo.homeurl + '">' + rs.nick + '</a>';
                        
                        if (rs.sex != "" && rs.sex != null) {
                            if (rs.sex == '1') {
                                html += '<b class="user-sexes user-male"></b>';
                            } else {
                                html += '<b class="user-sexes user-female"></b>';
                            }
                        }
                        
                        html += '</div>' +
                            '<p class="ui-top-title">简介：' + rs.desc + '</p>' +
                            '</div>' +
                            '<div class="ui-bottom">' +
                            '<ul class="focus-funs-box">' +
                            '<li class="f-f-li">关注<span class="funs-value"><a href="' + UserInfo.followurl + '">' + rs.follows + '</a></span></li>|' +
                            '<li class="f-f-li">粉丝<span class="funs-value"><a href="' + UserInfo.fansurl + '">' + rs.fans + '</a></span></li>|' +
                            '<li class="f-f-li">编辑<span class="funs-value"><a href="' + UserInfo.editsurl+rs.uid + '">' + rs.edits + '</a></span></li>' +
                            '</ul>';

                        html += '<div class="btn-focus-letter-box">';
                        if (profileId != tempProfileId) {
                            if (rs.followStatus == '0') {
                                html += '<div class="focus-btn letter-pre" id="follow' + rs.profileId + '" onclick=UserInfo.followProfile("' + rs.profileId + '"); >' + UserInfo.relation + '</div>';
                            } else {
                                html += '<div class="focus-btn focused" id="follow' + rs.profileId + '" onclick=UserInfo.unfollowProfile("' + rs.profileId + '") >' + UserInfo.relation + '</div>';
                            }
                            html += '<div class="focus-btn letter-pre" onclick="UserInfo.postMessge(' + rs.uid + ')">私信</div>';
                        }

                        html += '</div>' +
                            '<div class="shegwag-box clearfix">' +
                            '<span class="fr fot-colo be-mobai">被膜拜：' + rs.worship + '</span>' +
                            '<span class="fl fot-colo be-shegwag">声望：' + rs.prestige + '</span>' +
                            '</div>'; 
                        if (rs.userMWikiDTO.mWikiInfo != "") {
                            html += '<div class="admin-box fot-colo">' +
                                '管理wiki：' +
                                '<ul class="admin-wiki clearfix">';
                            for (var i = 0; i < rs.userMWikiDTO.mWikiInfo.length; i++) {
                                var result = rs.userMWikiDTO.mWikiInfo[i];
                                html += '<li class="wiki-li-box fl">' +
                                    '<a class="wiki-li-a" href="' + result.url + '"><img src="' + result.icon + '" alt=""></a>' +
                                    '</li>';
                            }

                            html += '</ul>' +
                                '共<span class="fot-colo admin-sum-num">' + rs.userMWikiDTO.count + '</span>个' +
                                '</div>';
                        }

                        html += '</div></div>';

                        $('body').append(html);
	                	UserInfo.position(obj,pagex,pagey);

                    }else{
                    	mw.ugcwikiutil.msgDialog('系统异常');
                    }
                }
            });
    	}
	},
	postMessge:function(uid){
		if (profileId == null || profileId == '') {
            loginDiv();
            return;
        }
		window.open(UserInfo.chaturl+uid);
	},
	position:function(obj,pagex,pagey){
		var username = obj.attr('data-username');
		//$('.wiki-person-popup[data-username="'+username+'"]').css({"left":x+'px',"top":y+'px'});
		//判断当前元素的位置开始
		
		var info_h = $('.wiki-person-popup[data-username="'+username+'"]').height();
        var info_w = $('.wiki-person-popup[data-username="'+username+'"]').width();
        var x,y;
        
        x = pagex - info_w/2;
        y = $(obj).offset().top;

	    if(x < 0){
	    	x = 10;
	    }else if( pagex + info_w/2 > $(window).width() ){
	    	x = $(window).width() - info_w - 10;
	    }

	    if( ($(obj).offset().top-$(window).scrollTop()) > ($(window).height() - info_h) ){
	    	y = y - info_h - 10;
	    }else{
	    	y = y + $(obj).height();
	    }
	    $('.wiki-person-popup[data-username="'+username+'"]').css({"left":x+'px',"top":y+'px'});
	},
	followProfile:function(profileIdObj){
		//profileId
		if (profileId == null || profileId == '') {
            loginDiv();
            return;
        }
        if (profileIdObj != null && profileIdObj != "") {
            $.ajax({
                url: UserInfo.followapiurl,
                type: "post",
                async: false,
                data: {destprofileid: profileIdObj},
                dataType: "jsonp",
                jsonpCallback: "callback",
                success: function (data) {
                    if (data != "") {
                        var sendResult = data[0];
                        var rs = sendResult.rs;
                        if (rs == "1") {
                            $("#follow" + profileIdObj).removeClass("letter-pre");
                            $("#follow" + profileIdObj).addClass("focused")
                            $("#follow" + profileIdObj).attr("onclick", "UserInfo.unfollowProfile('" + profileIdObj + "')");
                            if (sendResult.result == 'e') {
                                $("#follow" + profileIdObj).text("互相关注");
                            } else if (sendResult.result == 't') {
                                $("#follow" + profileIdObj).text("已关注");
                            }
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
        }else{
        	mw.ugcwikiutil.msgDialog('参数异常');
        }
	},
	unfollowProfile:function(profileIdObj){
		
		if (profileId == null || profileId == '') {
            loginDiv();
            return;
        }

        if (profileIdObj != null && profileIdObj != "") {
            $.ajax({
                url: UserInfo.unfollowapiurl,
                type: "post",
                async: false,
                data: {destprofileid: profileIdObj},
                dataType: "jsonp",
                jsonpCallback: "callback",
                success: function (data) {
                    if (data != "") {
                        var sendResult = data[0];
                        var rs = sendResult.rs;
                        if (rs == "1") {
                            $("#follow" + profileIdObj).removeClass("focused");
                            $("#follow" + profileIdObj).addClass("letter-pre");
                            $("#follow" + profileIdObj).attr("onclick", "UserInfo.followProfile('" + profileIdObj + "')");
                            $("#follow" + profileIdObj).text("关注");
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
        }else{
        	mw.ugcwikiutil.msgDialog('系统错误');
        }
	},
	goHome:function(pid){
    	window.open("http://www.joyme." + env + "/usercenter/page?pid="+pid);
    },
};

jQuery( document ).ready( function() {
	function IsPC(){
        var userAgentInfo = navigator.userAgent;
        var Agents = new Array("Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod");
        var flag = true;
        for (var v = 0; v < Agents.length; v++) {
            if (userAgentInfo.indexOf(Agents[v]) > 0) { flag = false; break; }
        }
        return flag;
    }
	if (IsPC()){
		
		var infoTimer=null;
		var infoOverTimer=null;
		$('body').on('mouseenter','.userinfo', function(e) {
			var obj = $(this);
			infoOverTimer = setTimeout(function(){
				UserInfo.getdata(obj, e.pageX, e.pageY);
			},500);
			 
		} ).on('mouseleave','.userinfo', function(e) {
			clearTimeout(infoOverTimer);
			infoTimer = setTimeout(function(){
				if($('body').data('blockOut')){
					return;
				}
				$('.wiki-person-popup').hide();
				$('body').data('blockOut',0)
			},100)
			
		} ).on('mousemove','.userinfo', function(e) {
			return false;
		} );
		$('body').on('mouseenter','.wiki-person-popup', function(e) {
			clearTimeout(infoTimer);
			infoTimer=null;
			 $('body').data('blockOut',1)
		} );
		$('body').on('mouseleave','.wiki-person-popup', function(e) {
			
			$('.wiki-person-popup').hide();	
			$('body').data('blockOut',0)
			
			
		} );
			
	}else{
		//not pc
	}
	
} );
