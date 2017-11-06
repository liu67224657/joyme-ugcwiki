$(function(){
    var load_type = '',
        isLoading = false,
        loadTime = null;
    // 上拉加载
    function IsPC(){
        var userAgentInfo = navigator.userAgent;
        var Agents = new Array("Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod");
        var flag = true;
        for (var v = 0; v < Agents.length; v++) {
            if (userAgentInfo.indexOf(Agents[v]) > 0) { flag = false; break; }
        }
        return flag;
    }
    function loadRemindMore(config){
        var scroBox =config.scroBox,
            loadBox = config.loadBox,
            evenType = $('#even_type').val(),
            commentsType = '',
            url = $('#local_url').val(),
            loadTips,
            i = 2,
            j=0;
        scroll_load(scroBox,loadBox);
        function loadComment(config){
            $.getJSON(url,{'pb_page':i,'about_type':evenType,'ajax':true},function(json){
                var liHtml = '';
                if(i<= json['result']['max_page'] && json['rs']==1){
                    $.each(json['result']['data'],function(index,array) {
                        liHtml+= '';
                        if(evenType == 'article-comments'){
                            //评论&回复
                            if( array['event_extra']['type'] == 1 ){
                                commentsType = '发表了评论';
                            }else if( array['event_extra']['type'] == 2 ){
                                commentsType = '回复了你';
                            }else{
                                commentsType = '回复了:'.array['event_extra']['othername'];
                            }
                            liHtml+='<li><div class="list-item-l"><cite><img src="'+array['event_extra']['icon']+'"></cite></div><div class="list-item-r situatio-one"><div class="item-r-name "><a target="_blank" href="'+array['event_extra']['user_home_url']+'">'+array['event_extra']['username']+'</a>在“<a target="_blank" href="'+array['event_extra']['action_link']+'">'+array['event_extra']['article']+'</a>”中'+commentsType+'：</div><div class="item-r-text"><a target="_blank" href="'+array['event_extra']['content_link']+'">'+array['event_extra']['synopsis']+'</a></div><div class="item-r-other fn-clear"><b class="from-wiki">出自：'+array['event_extra']['from']+'</b><b class="time-stamp">'+array['timestamp']['time']+'</b></div></div></li>';
                        }else if(evenType == 'article-thumb-up'){
                            //点赞
                            if( array['event_extra']['type'] == 1 ){
                                commentsType = '评论';
                            }else if( array['event_extra']['type'] == 2 ){
                                commentsType = '内容';
                            }
                            liHtml+='<li><div class="list-item-l"><cite><img src="'+array['event_extra']['icon']+'"></cite></div><div class="list-item-r situatio-one"><div class="item-r-name "><a target="_blank" href="'+array['event_extra']['user_home_url']+'">'+array['event_extra']['username']+'</a>赞了我的'+commentsType+'</div><div class="item-r-text"><a target="_blank" href="'+array['event_extra']['content_link']+'">'+array['event_extra']['synopsis']+'</a></div><div class="item-r-other fn-clear"><b class="from-wiki">出自：'+array['event_extra']['from']+'</b><b class="time-stamp">'+array['timestamp']['time']+'</b></div></div></li>';
                        }else if(evenType == 'article-cite-my'){
                            //@我的
                            liHtml+='<li><div class="list-item-l"><cite><img src="'+array['event_extra']['icon']+'"></cite></div><div class="list-item-r situatio-one"><div class="item-r-name "><a target="_blank" href="'+array['event_extra']['user_home_url']+'">'+array['event_extra']['username']+'</a>在“<a  target="_blank" href="'+array['event_extra']['action_link']+'">'+array['event_extra']['article']+'</a>”中@了你：</div><div class="item-r-text"><a target="_blank" href="'+array['event_extra']['content_link']+'">'+array['event_extra']['synopsis']+'</a></div><div class="item-r-other fn-clear"><b class="from-wiki">出自：'+array['event_extra']['from']+'</b><b class="time-stamp">'+array['timestamp']['time']+'</b></div></div></li>';
                        }else if(evenType == 'article-consider-me'){
                            //关注我的
                            var flag = array['event_extra']['type']?'关注了你，去他的<a target="_blank" href="'+array['event_extra']['user_home_url']+'">个人中心</a>看看':'取消了对你的关注';
                            liHtml+='<li><div class="list-item-l"><cite><img src="'+array['event_extra']['icon']+'"></cite></div><div class="list-item-r situatio-one"><div class="item-r-name "><a target="_blank" href="'+array['event_extra']['user_home_url']+'">'+array['event_extra']['username']+'</a>'+flag+'</div><div class="item-r-other fn-clear"><b class="time-stamp">'+array['timestamp']['time']+'</b></div></div></li>';
                        }else if(evenType == 'echo-system-message'){
                            //系统通知
                            liHtml+='<li><div class="list-item-l"><cite><img src="'+array['event_extra']['icon']+'"></cite></div><div class="list-item-r"><div class="item-r-name fn-clear"><span class="fn-left">'+array['event_extra']['username']+'</span><b class="time-stamp fn-right">'+array['timestamp']['time']+'</b></div><div class="item-r-text">'+array['event_extra']['content']+'</div></div></li>';
                        }
                    })
                    $('.loading').remove();
                    $('.'+config).append(liHtml);
                    isLoading = false;
                    i++;
                }else{
                    if(j==0){
                        $('.loading').remove();
                        isLoading = true;
                        loadTips ='<div class="loading" style="text-align: center"><span>没有更多了...</span></div>';
                        $('.'+config).append(loadTips);
                    }
                    j++;
                }
            });

            clearTimeout(loadTime);
            /*新增-没有更多加载*/
            $('.moreNo').show().delay(3000);
            /*新增end*/
        }
        function scroll_load(parentBox,className){
            var className=className,parentBox=parentBox;
            $(parentBox).scroll(function(ev){
                var $this = $(this);
                ev.stopPropagation();
                ev.preventDefault();
                if (load_type == 'shu') {
                    var footerH = $('.footer').height();
                    var sTop = $this.scrollTop();
                    var sHeight = $('body').get(0).scrollHeight;
                    var sMainHeight = $(this).height();
                    var sNum = sHeight - sMainHeight;
                    if (sTop >= sNum && !isLoading) {
                        isLoading = true;
                        loadTime = setTimeout(function () {
                            loadTips = '<div class="loading" style="text-align: center"><span>正在加载...</span></div>'
                            $('.' + className).append(loadTips);
                            loadComment(className);
                        }, 1000);
                    }
                    ;
                }else{
                    //alert(1)
                    $('.loading').hide();
                }
            });
        }
    }

    function init(){
        var W = $(window).width();
        if(!IsPC()){
            $('.paging ').hide();
            load_type = 'shu';
            loadRemindMore({scroBox:window,loadBox:'list-item'});
        }else{
            load_type = 'heng';
        }
    }
    $(window).resize(function(){
        init();
    });
    init();
});