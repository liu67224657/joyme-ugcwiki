/**
 * Created by kexuedong on 2016/12/22.
 */
;
var JProficient = {
    joymeapi: 'http://api.joyme.' + window.wgWikiCom,
    profileid : window.wgUserProfileId,
    main : function (type) {
        var pno = parseInt($(" #jproficients-pagenum ").val());
        var data = {'profileId':JProficient.profileid,'pno':pno,'psize':'4'};
        $.ajax({
            url: JProficient.joymeapi + "/usercenter/proficient",
            type: "post",
            async: false,
            data: data,
            dataType: "jsonp",
            success: function(data) {
                res = jQuery.parseJSON(data);
                if(res.rs=='1'){
                    if(type=="jproficients-change"){
                        $('#jproficients-lists').html("");
                    }
                    JProficient.display(res.result,pno);
                }
            },
            error: function() {}
        });
    },

    display : function (data,pno) {
        if(data){
            var html = '';
            for(var i in data){
                var jproficient = data[i];
                if(jproficient.followStatus){
                    jproficient.followStatus = '<span class="jproficient-followed" data-pid="'+jproficient.profileId+'"><i class="fa fa-check"></i>已关注</span>';
                }else {
                    jproficient.followStatus = '<span class="jproficient-unfollowed" data-pid="'+jproficient.profileId+'"><i class="fa fa-plus" aria-hidden="true"></i>关注</span>';
                }
                html += '<li><div class="int-tj-l"><cite><a href="javascript:;"><img src="'+ jproficient.iconurl + '" alt="img"></a></cite></div><div class="int-tj-r"><font>' +jproficient.nick+ '</font><b>' +jproficient.desc+ '</b>' +jproficient.followStatus+ '</div></li>';
            }
            $('#jproficients-lists').html(html);
            $(" #jproficients-pagenum ").val(pno+1);
        }
        return false;
    },

    follow : function (type,focusProfileid,clickobj) {
        if(type=="unfollowed"){
            var data = {'srcprofileid':JProficient.profileid,'destprofileid':focusProfileid};
            $.ajax({
                url: JProficient.joymeapi + "/api/relation/follow",
                type: "post",
                async: false,
                data: data,
                dataType: "jsonp",
                success: function(data) {
                    res = jQuery.parseJSON(data);
                    console.log(res);
                    if(res.rs=='1'){
                        $(clickobj).html('<i class="fa fa-check"></i>已关注');
                        $(clickobj).addClass("jproficients-followed");
                        $(clickobj).removeClass("jproficients-unfollowed");
                        return false;
                    }
                },
                error: function() {}
            });
        }
        else if(type == "followed"){
            var data = {'srcprofileid':JProficient.profileid,'focusprofileid':focusProfileid};
            $.ajax({
                url: JProficient.joymeapi + "/api/relation/unfollow",
                type: "post",
                async: false,
                data: data,
                dataType: "jsonp",
                success: function(data) {
                    res = jQuery.parseJSON(data);
                    console.log(res);
                    if(res.rs=='1'){
                        $(clickobj).html('<i class="fa fa-plus" aria-hidden="true"></i>关注');
                        $(clickobj).addClass("jproficients-unfollowed");
                        $(clickobj).removeClass("jproficients-followed");
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
    JProficient.main();
    $(document).on('click',".jproficients-change",function(){
        JProficient.main("jproficients-change");
        return false;
    }).on('click',".jproficients-unfollowed",function(){
        if (mediaWiki.config.get('wgUserName') == null) {
            loginDiv();
            return false;
        }
        var focusProfileid = $(this).data("pid");
        var that = $(this);
        JProficient.follow("unfollowed",focusProfileid,that);
        return false;
    }).on('click',".jproficients-followed",function(){
        if (mediaWiki.config.get('wgUserName') == null) {
            loginDiv();
            return false;
        }
        var focusProfileid = $(this).data("pid");
        var that = $(this);
        JProficient.follow("followed",focusProfileid,that);
        return false;
    });
});