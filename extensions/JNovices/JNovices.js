/**
 * Created by kexuedong on 2016/12/22.
 */
;
var JNovice = {
    joymeapi: 'http://api.joyme.' + window.wgWikiCom,
    profileid : window.wgUserProfileId,
    main : function (type) {
        var pno = parseInt($(" #jnovices-pagenum ").val());
        var data = {'profileId':JNovice.profileid,'pno':pno,'psize':'4'};
        $.ajax({
            url: JNovice.joymeapi + "/usercenter/novice",
            type: "post",
            async: false,
            data: data,
            dataType: "jsonp",
            success: function(data) {
                res = jQuery.parseJSON(data);
                if(res.rs=='1'){
                    if(type=="jnovices-change"){
                        $('#jnovices-lists').html("");
                    }
                    JNovice.display(res.result,pno);
                }
            },
            error: function() {}
        });
    },
    
    display : function (data,pno) {
        if(data){
            var html = '';
            for(var i in data){
                var jnovice = data[i];
                if(jnovice.followStatus=='1'){
                    jnovice.followStatus = '<span class="jnovice-followed" data-pid="'+jnovice.profileId+'"><i class="fa fa-check"></i>已关注</span>';
                }else {
                    jnovice.followStatus = '<span class="jnovice-unfollowed" data-pid="'+jnovice.profileId+'"><i class="fa fa-plus" aria-hidden="true"></i>关注</span>';
                }
                html += '<li><div class="int-tj-l"><cite><a href="javascript:;"><img src="'+ jnovice.iconurl + '" alt="img"></a></cite></div><div class="int-tj-r"><font>' +jnovice.nick+ '</font><b>' +jnovice.desc+ '</b>' +jnovice.followStatus+ '</div></li>';
            }
            $('#jnovices-lists').html(html);
            $(" #jnovices-pagenum ").val(pno+1);
        }
        return false;
    },

    follow : function (type,focusProfileid,clickobj) {
        if(type=="unfollowed"){
            var data = {'srcprofileid':JNovice.profileid,'destprofileid':focusProfileid};
            $.ajax({
                url: JNovice.joymeapi + "/api/relation/follow",
                type: "post",
                async: false,
                data: data,
                dataType: "jsonp",
                success: function(data) {
                    res = jQuery.parseJSON(data);
                    console.log(res);
                    if(res.rs=='1'){
                        $(clickobj).html('<i class="fa fa-check"></i>已关注');
                        $(clickobj).addClass("jnovice-followed");
                        $(clickobj).removeClass("jnovice-unfollowed");
                        return false;
                    }
                },
                error: function() {}
            });
        }
        else if(type == "followed"){
            var data = {'srcprofileid':JNovice.profileid,'focusprofileid':focusProfileid};
            $.ajax({
                url: JNovice.joymeapi + "/api/relation/unfollow",
                type: "post",
                async: false,
                data: data,
                dataType: "jsonp",
                success: function(data) {
                    res = jQuery.parseJSON(data);
                    console.log(res);
                    if(res.rs=='1'){
                        $(clickobj).html('<i class="fa fa-plus" aria-hidden="true"></i>关注');
                        $(clickobj).addClass("jnovice-unfollowed");
                        $(clickobj).removeClass("jnovice-followed");
                        return false;
                    }
                },
                error: function() {}
            });
        }
        else {
            mw.ugcwikiutil.msgDialog("参数错误");
            return false;
        }
    }
};
$( document ).ready( function () {
    JNovice.main();
    $(document).on('click',".jnovices-change",function(){
        JNovice.main("jnovices-change");
        return false;
    }).on('click',".jnovice-unfollowed",function(){
        if (mediaWiki.config.get('wgUserName') == null) {
            loginDiv();
            return false;
        }
        var focusProfileid = $(this).data("pid");
        var that = $(this);
        JNovice.follow("unfollowed",focusProfileid,that);
        return false;
    }).on('click',".jnovice-followed",function(){
        if (mediaWiki.config.get('wgUserName') == null) {
            loginDiv();
            return false;
        }
        var focusProfileid = $(this).data("pid");
        var that = $(this);
        JNovice.follow("followed",focusProfileid,that);
        return false;
    });
});