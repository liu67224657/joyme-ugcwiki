/**
 * JavaScript for UserSiteFollow
 * Used on Sidebar.
 */

function requestUserUserFollowsResponse( uid, fid, action,that,nohtml ) {

    //TODO: add waiting message.
    //TODO: validate wgUserName.
    if (action =='follow'){
        jQuery.post(
            mw.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfUserUserFollowsResponse',
                rsargs: [uid, fid]
            },
            function( data ) {
                var res = jQuery.parseJSON(data);
                if (res.success){
                    var count = jQuery( '#user-follower-count').html();
                    count = parseInt(count)+1;
                    jQuery( '#user-follower-count').html(count.toString());
                    that.removeClass('user-user-follow');
                    if(nohtml != '1'){
                    	that.addClass('each-follow');
                    	if(that.attr('data-follow-status') == '0'){
                    		that.html('<i class="fa fa-check" aria-hidden="true"></i>已关注');
                    	}else{
                    		that.html('<i class="fa fa-exchange" aria-hidden="true"></i>相互关注');
                    	}
                    	
                	}else{
                		that.removeClass('user-user-follow');
                		that.addClass('followed');
                		that.html('<i class="fa fa-check"></i>已关注');
                	}
                }else{
                	mw.ugcwikiutil.msgDialog(res.message);
                }
            }
        );
    } else if(action =='unfollow') {
    	mw.ugcwikiutil.confirmDialog('是否确认取消关注？',function (action) {
		   if(action=="accept"){
			   jQuery.post(
    	            mw.util.wikiScript(), {
    	                action: 'ajax',
    	                rs: 'wfUserUserUnfollowsResponse',
    	                rsargs: [uid, fid]
    	            },
    	            function( data ) {
    	                var res = jQuery.parseJSON(data);
    	                if (res.success){
    	                    var count = jQuery( '#user-follower-count').html();
    	                    count = parseInt(count)-1;
    	                    if (count >= 0){
    	                        jQuery( '#user-follower-count').html(count.toString());
    	                        jQuery( '#user-follower-count_top').html(count.toString());
    	                    }else{
    	                        jQuery( '#user-follower-count').html('0');
    	                        jQuery( '#user-follower-count_top').html('0');
    	                    }

    	                    jQuery( '#user-follow-'+fid).remove();
    	                    if(jQuery( '.list-item li').length <= 0){
    	                    	window.location.href="/home/特殊:ViewFollows";
    	                    }
    	                    
    	                }else{
    	                	mw.ugcwikiutil.msgDialog(res.message);
    	                }
    	            }
    	        );
		   }
		});
        
    } else if(action =='unfans') {
    	mw.ugcwikiutil.confirmDialog('是否确认移除此粉丝？',function (action) {
		   if(action=="accept"){
			   jQuery.post(
    	            mw.util.wikiScript(), {
    	                action: 'ajax',
    	                rs: 'wfUserUserUnfollowsResponse',
    	                rsargs: [fid, uid]
    	            },
    	            function( data ) {
    	                var res = jQuery.parseJSON(data);
    	                if (res.success){
    	                    var count = jQuery( '#user-fans-count').html();
    	                    count = parseInt(count)-1;
    	                    if (count >= 0){
    	                        jQuery( '#user-fans-count').html(count.toString());
    	                        jQuery( '#user-fans-count_top').html(count.toString());
    	                    }else{
    	                        jQuery( '#user-fans-count').html('0');
    	                        jQuery( '#user-fans-count_top').html('0');
    	                    }
    	                    jQuery( '#user-follow-'+fid).remove();
    	                    if(jQuery( '.list-item li').length <= 0){
    	                    	window.location.href="/home/index.php?title=特殊:ViewFollows&rel_type=2";
    	                    }
    	                    
    	                }else{
    	                	mw.ugcwikiutil.msgDialog(res.message);
    	                }
    	            }
    	        );
		   }
		});
        
    }
}

jQuery( document ).ready( function() {
	/*
    $('li#user-user-follow').on('click',function(){
        var that = $(this);

        if (mw.config.get('wgUserName') == null){
            $('.user-login').modal();
            return;
        }

        that.html('<a><i class="fa fa-spinner fa-pulse"></i></a>');
        var followee = that.attr("data-username");
        requestUserUserFollowsResponse(
            mw.config.get('wgUserName'),
            mw.config.get('wgTitle'),
            that.hasClass('unfollow'),
            that
        );
    });*/
	
	LoadMore.init('list-item','wfUserViewFollows',$('#user_id').val()+','+$('#rel_type').val());
    $('body').on('click','.user-user-follow',function(){
        var that = $(this);

        if (mw.config.get('wgUserName') == null){
            $('.user-login').modal();
            return;
        }

        var friend_id = that.attr("data-uid");
        var data_nohtml = that.attr("data-nohtml");
        requestUserUserFollowsResponse(
            mw.config.get('wgUserId'),
            friend_id,
            that.attr('data-action'),
            that,
            data_nohtml
        );
    });
});