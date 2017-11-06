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
if (IsPC()) {
    $('.activity-more-con').hide();
} else {
    $('.paging').hide();

}
// 上拉加载
var LoadMore = {
    loadMorePage: 2,
    isLoading: false,
    load_type: '',
    loadTime: null,
    loadBox: null,
    actions: null,
    rsargs: null,
    init: function (loadBox, actions, rsargs) {
        LoadMore.loadBox = loadBox;
        LoadMore.actions = actions;
        LoadMore.rsargs = rsargs;
        LoadMore.initdata();
        $(window).resize(function () {
            LoadMore.initdata();
        });
    },
    initdata: function () {
        if (!IsPC()) {
            $('.paging').hide();
            LoadMore.load_type = 'shu';
            LoadMore.scroll_load(window, LoadMore.loadBox, LoadMore.actions, LoadMore.rsargs);
        } else {
            LoadMore.load_type = 'heng';
        }
    },
    scroll_load: function (parentBox, className, action, rsargs) {
        var className = className, parentBox = parentBox;
        $(parentBox).scroll(function (ev) {
            var $this = $(this);
            ev.stopPropagation();
            ev.preventDefault();
            if (LoadMore.load_type == 'shu') {
                var footerH = $('.footer').height();
                var sTop = $this.scrollTop();
                var sHeight = $('body').get(0).scrollHeight;
                var sMainHeight = $(this).height();
                var sNum = sHeight - sMainHeight;
                var loadTips = '<div class="loading"><span style="display: block;line-height:22px;text-align: center;">正在加载...</span></div>';
                if (sTop >= sNum && !LoadMore.isLoading) {
                    console.log(11)
                    LoadMore.isLoading = true;
                    $('.' + className).append(loadTips);
                    LoadMore.loadComment(className, action, rsargs);
                }
                ;
            }
        });
    },
    loadComment: function (className, action, rsargs) {
        var rsargs_val = rsargs.split(",");
        rsargs_val.splice(0, 0, LoadMore.loadMorePage);
        jQuery.post(
            mw.util.wikiScript(), {
                action: 'ajax',
                rs: action,
                rsargs: rsargs_val
            },
            function (data) {
                var res = jQuery.parseJSON(data);
                if (res.rs == '1') {
                    $('.' + className).append(res.data);
                    LoadMore.isLoading = false;
                    LoadMore.loadMorePage++;
                } else {
                    /*mw.hook('postEdit').fire({
                        message: res.data
                    });*/
                    mw.ugcwikiutil.autoCloseDialog(res.data)
                }
                $('.loading').remove();
            }
        );
    }
};

$(function () {
    var W = null;
    var dh_change=false;
    var timer=null;
    function changeW(config) {
        var ele = config.ele;
        elePar = ele.parent('.dropdown'),
            eleW = config.eleW;
        ele.css({'width': eleW});
    };
    // 管理wiki中的下拉
    function Ocount() {
        $(document).on('click', '.count-icon', function () {
            var addEdit = $(this).siblings('.add-edit');
            if (!$(this).hasClass('on')) {
                $(this).addClass('on');
                addEdit.show();
            } else {
                $(this).removeClass('on');
                addEdit.hide();
            }
        });
        $('#managewikimore').click(function () {
            $('.count-icon').removeClass('on');
            $('.add-edit').hide();
        });
    }

    Ocount();
    // tab切换
    function tabMenu(conf) {
        var tabMain = conf.tabMain,
            tabTit = conf.tabTit,
            iconChild = tabTit.children(),
            tabCon = conf.tabcon,
            activing = conf.activing;
        iconChild.click(function () {
            var _this = $(this).index();
            conChild = $(this).parent(tabTit).siblings(tabCon).children();
            $(this).addClass(activing).siblings(iconChild).removeClass(activing);
            conChild.eq(_this).addClass(activing).siblings(conChild).removeClass(activing);
        });
    }

    tabMenu({tabMain: $('.tab-box'), tabTit: $('.tab-tit'), tabCon: $('.tab-con'), activing: "on"});

    // 登陆、注册调用的方法
    /*$('.register-mask').click(function () {
        mw.loginbox.register();
    });
    $('.login-mask').click(function () {
        mw.loginbox.login();
    });
    $('.get-password').click(function () {
        mw.loginbox.getpassword();
    });
    $('.close-icon').click(function () {
        mw.loginbox.closebox();
    });*/


    // select 下拉
    function selectMenu() {
        $(".select-area select").each(function () {
            var value = $(this).find("option:selected").text();
            $(this).parent(".select-area").find(".select-value").text(value);
        });
        $(".select-area select").change(function () {
            var value = $(this).find("option:selected").text();
            $(this).parent(".select-area").find(".select-value").text(value);
        });
    }

    selectMenu();
    // 上拉加载
    // if(W<992){
    //      changeW({ele:$('.notice-list')});
    //      changeW({ele:$('.setting-box ul')});
    //  }


    // 1.1beta  实时监听窗口大小
    function resizeW() {
        W = $(window).width();
        if (W < 992) {
            W = W;
        } else {
            W = 'auto'
        }
        changeW({ele: $('.notice-list'), eleW: W});
        changeW({ele: $('.setting-box ul'), eleW: W});
    }

    $(window).resize(function () {
        resizeW();
    });
    resizeW();
    // 以上为个人中心模块的js

    //ugcwiki

    $(".navbar-header2 .header-search").click(function () {
        $(".header-search-text").toggle();
    });

    //share
    $('.wechat-share .fx').click(function () {
        $('.wechat-share .mengceng-share-popup,.mengceng-share-icon').show();
    });
    $('.mengceng-share-popup .share-with-wrap .close-btn,.mengceng-share-icon').click(function () {
        $('.wechat-share .mengceng-share-popup,.mengceng-share-icon').hide();
    });
    //mulu 弹层
    var catalog = $('#toc');
    if (catalog.length) {
        $(".wechat-share .mulu").css('display', 'block');
    }

    $(".wechat-share .mulu").click(function () {
        $('.toctoggle').html('');
        $("#toc,.toc-mengceng").show();
    });
    
    if ($(window).width() < 991) {
    	$("#toctitle,.toc-mengceng").click(function () {
            $("#toc,.toc-mengceng").hide();
        });
        $("#toc ul li a").click(function () {
            $("#toc,.toc-mengceng").hide();
        });
    }
    $("#sidebar-menu").click(function () {
		//var scrollTop = $('body').scrollTop();
		//$('body').css({'overflow':'hidden','position': 'fixed','top': scrollTop,'height':'100%'});
		//$('html').addClass('ovfHiden');
        var res = $('.col-md-3').hasClass('dh-change');
        dh_change =!res;
        $('#sidebar-menu-bg').toggle();

        $("html").toggleClass("bodypos");
        //$(".navbar-header2").toggleClass("navbar-header2-fixed");
        if (res) {
            //$(".navbar-header2").removeClass("navbar-header2-fixed");
            $("html").removeClass("bodypos");
            $('.header-search').removeClass('on');
            $('.col-md-3').removeClass('dh-change');
            $("#sidebar-menu").removeClass("header-dh");
        } else {
            $("html").addClass("bodypos");
            $('.header-search').addClass('on');
            $('.col-md-3').addClass('dh-change');
            $("#sidebar-menu").addClass("header-dh");
            //$(".navbar-header2").addClass("navbar-header2-fixed");
        }
    });
 //使网页恢复可滚动
    $("#sidebar-menu-bg").click(function () {
		//$('body').css({'overflow':'auto','position': 'static','top': 'auto','height':'auto'});
        $('#sidebar-menu-bg').toggle();
        $('.header-search').removeClass('on');
        $(".col-md-3").removeClass('dh-change');
        $("#sidebar-menu").removeClass("header-dh");
        $("html").removeClass("bodypos");
		//$('html,body').removeClass('ovfHiden');
        //$(".navbar-header2").removeClass("navbar-header2-fixed");
    
    });

    //左侧导航创建页面
    $('.create-wiki .createbox .search').click(function () {
        if (mw.config.get('wgUserId') > 0) {
            if ($('.create-wiki .createbox .place').val() == '') {
                $('.create-wiki .input-warn').show();
                $('.create-wiki').addClass('wiki-top');
            } else {
                $('.create-wiki').removeClass('wiki-top');
                $('.create-wiki .input-warn').hide();
                $('.create-wiki .createbox').submit();
            }
        } else {
            // mw.loginbox.login();
            loginDiv();
        }
    });

    if ($(window).width() < 992) {
        $(".joyme-dialog-wiki-popup").css("left", ($(window).width() - $(".joyme-dialog-wiki-popup").width()) / 2 + "px");
    }

    function IsPC_nopad() {
        var userAgentInfo = navigator.userAgent;
        var Agents = new Array("Android", "iPhone", "SymbianOS", "Windows Phone", "iPod","iPad");
        var flag = true;
        for (var v = 0; v < Agents.length; v++) {
            if (userAgentInfo.indexOf(Agents[v]) > 0) {
                flag = false;
                break;
            }
        }
        return flag;
    }
    if (IsPC_nopad()) {
        //pc
        nav_pc('pc');
        $(".gl-tool .create-newpage").hover(function () {
            $(".gl-tool .create-newpage .create-wiki").css("display", "block");
        }, function () {
            $(".gl-tool .create-newpage .create-wiki").css("display", "none");
        });
        // js添加广告框；
        if($('#mw-data-after-content').length>0){
          $('<div class="advertise"></div>').insertBefore($("#mw-data-after-content"));  
        }
    }
    else {
        // pc以外的设备
        if (browserRedirect()) {
               // ipad
            function orient() {
                if (window.orientation == 0 || window.orientation == 180) {
                    //竖屏
                    // alert(33)
                    mbindEvent();
                } else if (window.orientation == 90 || window.orientation == -90) {
                    // 横屏
                    nav_pc();return;

                }
            }

            orient();
            // mbindEvent();
            $(window).bind('orientationchange', function (e) {
                // if(dh_change){
                    // nav_pc()
                // //     alert(1)
                //     dh_change=false;
                // }else{
                // //     alert(2);
                //     mbindEvent();
                // }
                orient()
                // alert(1);
                // mbindEvent();
            });
        }else{
            // alert(44);
           mbindEvent();
        }
        
        // mbindEvent();
        // nav_pc();
    }

    function mbindEvent(){
            // alert("ca-edit:"+$('#ca-edit>a').attr('href'))
            // alert($('#p-actions>a').attr('href'));
        // m端编辑功能
        $('.dropdown-toggle-edit').click(function(){
           window.location.href = $('#p-actions>a').attr('href');
        })
        // m端源代码功能
        $('.ca-edit').click(function(){
           window.location.href = $('#ca-edit>a').attr('href');
        })
        checkOutScroll();
        $(document).bind("scroll",function (e) {
            checkOutScroll();
            if ($(document).scrollTop() > 200) {

                $(".wechat-share .go-top").css("display", "block");
            }
            else {
                $(".wechat-share .go-top").css("display", "none");
            }
        });
        var heightvalue = (document.documentElement.clientHeight || document.body.clientHeight) - 40;
        // alert('heightvalue:'+heightvalue);
        // alert('section-left-wrap:'+$('.section-left-wrap').outerHeight());
        // alert('heightvalue:'+heightvalue);
        var count = 0;
        var timer = setInterval(function(){
          if(count<20){
            if(typeof $.fn.perfectScrollbar == "function"){
              clearInterval(timer);
              
              $(".section-left2").perfectScrollbar("destroy");
              return ;
            }
            count++
          }else{
            clearInterval(timer);
          }
        },400);
        $(".section-left-wrap").css("height", 'auto');
        $(".section-left").css("height", heightvalue + "px");
        $('.section-left2').css('height',"auto");
        $('.zhank').each(function(){
            if($(this).siblings('.sj').length>0){
                sjOpen($(this).parent('.ej'));
                $(this).siblings('.sj').show().parents('.wiki-action').removeClass('wiki-hide');
                $(this).addClass('zhank1');

            }else{
            }
            $(this).removeClass('zhank');
        })
        $(".wiki-action .ej").unbind().bind("click",function (e) {
            e.stopPropagation();
            if($(this).find('.sj').length>0){

                if($(this).find('.sj').css('display') == 'none'){
                    // 三级展开
                    $(this).find('.sj').show();
                    sjOpen(this);
                }else{
                    // 三级关闭
                    sjClose(this);
                    $(this).find('.sj').hide();
                }
                // $(".wiki-person,.nav-left-tj").toggleClass("wiki-hide");
                // $(".wiki-action li.ej").toggleClass("ejtz");
                // $(".wiki-action .wiki-ul-ej").toggleClass("ul-ej-ys");
                // $(".wiki-action").toggleClass("m-wiki-action");
                // $(this).parents(".wiki-action").children(".fl").toggleClass("wiki-hide");
                // $(this).siblings(".ej").toggleClass("wiki-hide");
                
                // $(this).children(".menu2").toggleClass("zhank1");
                // $(this).parents(".wiki-action").siblings(".wiki-action").toggleClass("wiki-hide");
                // $("#joymewiki-navigation").scrollTop(0);
            }
            return;
        });


        $(".wiki-action .ej .sj>li>a").bind('touchend',function(e){
            e.stopPropagation();
            $(this).parents('.sj').siblings('.zhank').removeClass('zhank');
        });
        
    }
    function sjOpen(el){
        $(".wiki-person,.nav-left-tj").addClass("wiki-hide");
        $(".wiki-action li.ej").addClass("ejtz");
        $(".wiki-action .wiki-ul-ej").addClass("ul-ej-ys");
        $(".wiki-action").addClass("m-wiki-action");
        $(el).parents(".wiki-action").children(".fl").addClass("wiki-hide");
        $(el).siblings(".ej").addClass("wiki-hide");
        
        $(el).children(".menu2").addClass("zhank1");
        $(el).parents(".wiki-action").siblings(".wiki-action").addClass("wiki-hide");
        $("#joymewiki-navigation").scrollTop(0);
    }
    function sjClose(el){
        // $('.wiki-hide').removeClass('wiki-hide');
        $(".wiki-person,.nav-left-tj").removeClass("wiki-hide");
        $(".wiki-action li.ej").removeClass("ejtz");
        $(".wiki-action .wiki-ul-ej").removeClass("ul-ej-ys");
        $(".wiki-action").removeClass("m-wiki-action");
        $(el).parents(".wiki-action").children(".fl").removeClass("wiki-hide");
        $(el).siblings(".ej").removeClass("wiki-hide");
        // alert($(el).children(".menu2").length);
        $(el).children(".menu2").removeClass("zhank1");
        $(el).parents(".wiki-action").siblings(".wiki-action").removeClass("wiki-hide");
        $("#joymewiki-navigation").scrollTop(0);
    }
    function checkOutScroll(){
        if ($(document).scrollTop() > 40) {
            $('.mengceng-share,.col-md-3.section-left').css('top',$('.ysw-wiki').outerHeight() + "px");
            $(".navbar-header2").addClass("navbar-header2-fixed");
        }
        else {
            $(".navbar-header2").removeClass("navbar-header2-fixed");
            $('.mengceng-share,.col-md-3.section-left').css('top',$('.navbar-header').outerHeight() + "px");
        }
    }
    function browserRedirect() {
        var sUserAgent = navigator.userAgent.toLowerCase();
        var bIsIpad = sUserAgent.match(/ipad/i) == "ipad";
        return bIsIpad;
    }

   
    
    function nav_pc(terminal) {
        // 视频遮挡登录框
        setTimeout(function(){
            $('iframe').each(function(){
                var url = $(this).attr("src");
                $(this).attr("src",url+"?wmode=transparent");
            });
            
        },4000)
        // var elevent = terminal == 'pc' ? "scroll" : "touchmove";
        var elevent = "scroll";
        $('.col-md-3.section-left').css('top','0');
        var count = 0;
        var timer = setInterval(function(){
          if(count<20){
            if(typeof $.fn.perfectScrollbar == "function"){
              clearInterval(timer);
              
              $(".section-left2").perfectScrollbar();
              return ;
            }
            count++
          }else{
            clearInterval(timer);
          }
        },400);
        
        
        var clientH = (document.documentElement.clientHeight || document.body.clientHeight);
        $(".section-left").css("height", clientH + "px");
        $(".zhank1").addClass('zhank');
        initleft2();
        
		function initleft2(){
			if ($(document).scrollTop() > 73) {
                $(".section-left-wrap").addClass("section-left-fixed");
                var left_Height2 = $(window).height() - $(".section-left-top").height() - 73;
                $(".section-left2").css("height", left_Height2 + "px");
                $(".section-left2").css("min-height", left_Height2 + "px");
            }
            else {
                $('.col-md-3.section-left').css('top','0');
                $(".section-left2").css("height", "500px");
                $(".section-left2").css("min-height", "500px");
                $(".section-left-wrap").removeClass("section-left-fixed");
            }
            var count = 0;
            var timer = setInterval(function(){
              if(count<20){
                if(typeof $.fn.perfectScrollbar == "function"){
                  clearInterval(timer);
                  
                  $(".section-left2").perfectScrollbar('update');
                  return ;
                }
                count++
              }else{
                clearInterval(timer);
              }
            },400);
		}
        // 避免干扰到其他页面
        if($('.section-left').length>0){

            $(document).unbind("scroll").bind(elevent,function () {
    			var container  = $('.footer').height() +30;
    			var bodyHeight = $('.container-wrap').height();
    			var screenHeight = window.screen.availHeight;
    			var scrollTop = $(document).scrollTop()+screenHeight;
    			var sectionHeight=scrollTop - bodyHeight;
    			var ser_h = $(".section-right2").offset().top + $(".section-right2").height() - $(document).scrollTop();
                var aaa = $(window).height() - ser_h ;

                initleft2();
                //右侧置顶
                if ($(document).scrollTop() > $(window).height()) {
                    console.log(1);
                    $(".share-right .top-icon").css("display", "block");
                }
                else {
                    console.log(2);
                    $(".share-right .top-icon").css("display", "none");
                }
    			//console.log('test:',aaa ,ser_h );
    			  if(aaa <= 0){
                    // 
    				  $(".section-left-wrap").addClass("section-left-bottom");
    				  $(".section-left-wrap").stop(true).animate({
                        'bottom':0,
    					'height': '100%'
                    }, 0);
    			  }else{
                    // 底部 
    				  $(".section-left-wrap").addClass("section-left-bottom");
    				  $(".section-left-wrap").stop(true).animate({
                        'bottom':aaa-20
                    }, 0);
    			  }
                

            });
        }
        //sidebar - 展开  记录展开的目录，刷新之后继续展开

        $('.wiki-nav a[class*="visited"]').each(function (i, v) {
            if ($(v).parent().parent().hasClass('sj') ){
                var zhankELe = $(v).parent().parent();
                $(v).parent().parent().show();

                zhankELe.prev().addClass('zhank');
                zhankELe.parent().parent().prev().addClass('zhank');
                zhankELe.parent().parent().parent().addClass('active');
            } else if ($(v).parent().hasClass('ej') ){
                $(v).parent().parent().prev().addClass('zhank');
                $(v).parent().parent().parent().addClass('active');
            }
        });
        $(".wiki-action .ej").unbind().click(function () {
            if ($(this).children('.sj').length > 0) {
                var that=this;
                // alert($(this).children('.sj').css('display') == 'block');
                // alert("dh_change:"+dh_change);
                if(dh_change){
                    // 竖屏下已展开菜单
                    if($(this).children('.sj').css('display') == 'block'){
                        // 三级展开
                        clearTimeout(timer);
                        timer = setTimeout(function(){
                            $(that).children('.sj').hide();
                            $('.zhank').removeClass('zhank');
                            $(that).children(".menu2").addClass("zhank1");
                            sjClose(that);
                        },300)
                        
                    }else{
                        // 三级闭合
                        clearTimeout(timer);
                        timer = setTimeout(function(){
                            $(that).children('.sj').show();
                            $(that).children(".menu2").removeClass("zhank1");
                            sjOpen(that);
                        },300)
                    }
                    // dh_change = false;
                }else{
                    // 竖屏下未展开菜单
                    $(that).children('.sj').toggle();
                    $(that).children(".menu2").toggleClass("zhank");
                }
            }else {

            }
        });
    }
});

