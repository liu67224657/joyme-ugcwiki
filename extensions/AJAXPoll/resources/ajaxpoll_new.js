//弹窗的出现和隐藏
$(".vote_result").click(function(){
    $(".tc-zz").show();
    //disstop();
});

$(".vote-close-btn").click(function(){
    $(".tc-zz").css("display","none");
    //disstart();
});

function disstop(){
    $("body,html").css({'overflow':'hidden'});
    $("body").height($(window).height());
    $("body").bind("touchmove",function(e){ e.preventDefault();});
    $(".tc-zz .tpjg-cont").bind("touchmove",function(e){
        e.stopPropagation();
    });
}

function disstart(){
    $("body,html").css({'overflow':"auto"});
    $("body").height("auto");
    $("body").unbind("touchmove");
}

function voteDj(){
    $(".vote_result").click(function(){
        $(".tc-zz").css({"display":"block"});
        //disstop();
    });
    $(".vote-close-btn").click(function(){
        $(".tc-zz").css("display","none");
        //disstart();
    });
}
function IsPC() {
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
function voteDjfd(){

    if(window.screen.width<767){
        $(".toupiao .tp-info dl dt img").addClass("webpltc-cktpjg").removeClass("cktp-jg-img");
    }
    $(".webpltc-cktpjg").click(function () {
        $(".cktp-jg-web-tc-"+$(this).attr('data-bt')).show();
    });
    $(".close-btn-cktpjg").click(function(){
        $(".cktp-jg-web-tc-"+$(this).attr('data-bt')).hide();
    });

    
    $(".toupiao .tp-info dl dt .cktp-jg-img").click(function(){
        if(IsPC()){
            $.openPhotoGallery(this,"tp-info","cktp-jg-img");
        }else{    
            $(".cktp-jg-tc-"+$(this).attr('data-bt')).show();
            var imgscroller_obj = $(".cktp-jg-tc-"+$(this).attr('data-bt'));
            $(imgscroller_obj).find('.image-scroller').imageScroller();
            var imgheight=$(imgscroller_obj).find(".image-scroller>p>img").height();
            var imgwidth=$(imgscroller_obj).find(".image-scroller>p>img").width();
            var marginHeight=300-imgheight/2;
            var marginWidth=410-imgwidth/2;
            if(imgheight<600){
                $(imgscroller_obj).find(".preview").css("display","none");
            }else{
                $(imgscroller_obj).find(".preview").css("display","block");
            }
            if(imgwidth<820){
                $(imgscroller_obj).find(".image-scroller p img").css("left",marginWidth+"px");
            }
            else{
                $(imgscroller_obj).find(".image-scroller p img").css("left","0px"); 
            }
            if(imgheight<600){
                $(imgscroller_obj).find(".image-scroller p img").css("top",marginHeight+"px");
            }
            else{
                $(imgscroller_obj).find(".image-scroller p img").css("top","0px");
            }
        }
		
    });
    
    //点击评论放大图片的关闭按钮，对整个浮层进行关闭
    $(".scroller-close-btn").click(function () {
        var id = $(this).attr('data');
        $(".cktp-jg-tc-"+id).css("display","none");
    });
}

$( document ).on( 'click', '.ajaxpoll_new_submit', function ( event ) {

    var answer;
    answer = $(this).val()
    if(answer){
        event.preventDefault();
        var poll , token , endtime;
        poll = $('#ajaxPollinfo').val();
        token = $('#ajaxPollNewToken').val();
        endtime = $('#ajaxPolltime').val();
        var uri = mw.util.wikiScript() + '?action=ajax';
        var func_name = "AJAXPoll::submitVote";
        var args = [poll,answer,token,1,endtime];
        if ( uri.indexOf( '?' ) === -1 ) {
            uri = uri + '?rs=' + encodeURIComponent( func_name );
        } else {
            uri = uri + '&rs=' + encodeURIComponent( func_name );
        }
        for (var i = 0; i < args.length; i++ ) {
            uri = uri + '&rsargs[]=' + encodeURIComponent( args[i] );
        }
        var post_data = null;
        $.ajax({
            url:uri,
            type:'get',
            data:post_data,
            dataType:"jsonp",
            async: false,
            jsonpCallback:"ajaxpollquerycallback"+poll,
            success: function (req) {
                var resMsg = req[0];
                $("#ajaxpoll-container-"+poll)[0].innerHTML = resMsg;
                voteDj();
                voteDjfd();
            },
            error: function () {
                console.log('请求失败，请重新试一下或者刷新页面后再试');
            }
        });
        setupEventHandlers();
    }else{
        alert("系统参数错误!");
    }
});

$(document).ready(function() {

    voteDjfd();
});