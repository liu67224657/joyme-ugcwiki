jQuery( document ).ready( function() {
	$('body').on('click','#recommend_change',function(){
		var uid = mw.config.get('wgUserId');
		jQuery.post(
            mw.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfUserRecommendUsers',
                rsargs: [uid]
            },
            function( data ) {
                var res = jQuery.parseJSON(data);
                if (res.rs == '1'){
                    $('#recommend_list').html(res.data);
                    
                }else{
                	mw.ugcwikiutil.autoCloseDialog("暂无数据");
                }
            }
       );
	});
	
	$('body').on('click','.user-recommend-follow',function(){
        var that = $(this);

        if (mw.config.get('wgUserName') == null){
            $('.user-login').modal();
            return;
        }
        var uid = mw.config.get('wgUserId');
        var fid = that.attr("data-uid");
        
        jQuery.post(
            mw.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfUserUserFollowsResponse',
                rsargs: [uid, fid]
            },
            function( data ) {
                var res = jQuery.parseJSON(data);
                if (res.success){
                	that.parent().parent().remove();
                	if($('#recommend_list li').length>=4){
                		$('#recommend_list li').eq(3).show();
                	}
                    else if($('#recommend_list li').length==0){
                        $('.no-follow').hide();
                    }
                }else{
                	mw.ugcwikiutil.msgDialog(res.message);
                }
            }
        );
    });
});