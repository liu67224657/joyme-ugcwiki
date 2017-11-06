var timernew=null;
var LoadMore = {
    loadMorePage: 2,
    isLoading: false,
    init: function (loadBox, action, rsargs) {
        LoadMore.scroll_load(window, loadBox, action, rsargs);
    },
    scroll_load: function (parentBox, className, action, rsargs) {
        var className = className, parentBox = parentBox;
        $(parentBox).scroll(function (ev) {
            var $this = $(this);
            ev.stopPropagation();
            ev.preventDefault();
            var footerH = $('.footer').height();
            var sTop = $this.scrollTop();
            var sHeight = $('body').get(0).scrollHeight - footerH;
            var sMainHeight = $(this).height();
            var sNum = sHeight - sMainHeight;
            var loadTips = '<div class="loading"><span style="display: block;line-height:22px;text-align: center;">正在加载...</span></div>';
            if (sTop >= sNum && !LoadMore.isLoading) {
                LoadMore.isLoading = true;
                $('.' + className).append(loadTips);
                LoadMore.loadComment(className, action, rsargs);
            }
            ;
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
                    mw.hook('postEdit').fire({
                        message: res.data
                    });
                }
                $('.loading').remove();
            }
        );
    }
};

$(function () {

    var sidebarJs = {
        init:function(){
            this.initEl();
            this.render();
            this.bind();
        },
        initEl:function(){
            this.sidebar=$('.section-left2');
        },
        render:function(){
            this.renderSidebarHeight()
        },
        bind:function(){
            if(this.IsPC){
                // pc
                this.bindPc();
            }else{
                // m
                if(this.isIpad()){
                    // ipad
                    this.bindIpad();
                }
                // common m
                this.bindM();
            }

        },
        bindPc:function(){

        },
        bindIpad:function(){

        },
        bindM:function(){
            var that = this;
            window.resize=function(){
                that.renderSidebarHeight();
            }
            $(window).bind('orientationchange', function (e) {
                that.renderSidebarHeight();
            });
            // m端编辑功能
            $('.dropdown-toggle-edit').click(function(){
               window.location.href = $('#p-actions>a').attr('href');
            })
            // m端源代码功能
            $('.ca-edit').click(function(){
               window.location.href = $('#ca-edit>a').attr('href');
            })
        },
        renderSidebarHeight:function(){
            var heightvalue = $(window).height() - 40;
            this.sidebar.css("height", heightvalue);
        },
        isPc:function(){
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
        },
        isIpad:function(){
            var userAgentInfo = navigator.userAgent;
            var flag = userAgentInfo.indexOf('iPad')>-1? true :false;
            
        }
    }
    sidebarJs.init();
	function browserRedirect() {
        var sUserAgent = navigator.userAgent.toLowerCase();
        var bIsIpad = sUserAgent.match(/ipad/i) == "ipad";
        return bIsIpad;
    }

    var isAgent = true;
    if (browserRedirect()) {
        $(window).bind('orientationchange', function (e) {
        	 if ($(window).width() > 991) {
        		 //maodian_pc();
        	 }
        	
        });
    }

    var W = $(window).width();

    function changeW(config) {
        var ele = config.ele;
        ele.css({'width': W});

    }

    setTimeout(function(){
        $('iframe').each(function(){
            var url = $(this).attr("src");
            $(this).attr("src",url+"?wmode=transparent");
        });
        
    },4000)
    
    $(document).on("touchstart", function (e) {
        e.stopPropagation();
        var navIn = $('.navbar-collapse');
        if (navIn.hasClass('in')) {
            navIn.removeClass('in');
        }
    });
    $('.navbar-toggle').on("touchstart", function (e) {
        e.stopPropagation();
    });
    $(".cancel-flo").click(function (e) {
        e.stopPropagation();
    });
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
    if (W < 992) {
      //  changeW({ele: $('.notice-list')});
       // changeW({ele: $('.setting-box ul')});
    }

    //ugcwiki

    $(".navbar-header2 .header-search").click(function () {
        $(".header-search-text").toggle();
    });

    //share
    $('.wechat-share .fx').click(function () {
        $('.wechat-share .mengceng-share').show();
    });
    $('.mengceng-share .share-with-wrap .close-btn').click(function () {
        $('.wechat-share .mengceng-share').hide();
    });
    //mulu 弹层
    var catalog = $('#toc');
    if (catalog.length) {
        $(".wechat-share .mulu").css('display', 'block');
    }
    else {
        $(".section-recommend .section-nav").css("display", "none");
        $(".section-nav .bottom-wrap .toggle-button").css("display", "none");
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
        var res = $('#joymewiki-navigation').hasClass('dh-change');
        $('#sidebar-menu-bg').toggle();
        $("body").toggleClass("bodypos");
        if (res) {
            $("body").removeClass("bodypos");
            $('.header-search').removeClass('on');
            $('#joymewiki-navigation').removeClass('dh-change');
            $("#sidebar-menu").removeClass("header-dh");
        } else {
            $("body").addClass("bodypos");
            $('.header-search').addClass('on');
            $('#joymewiki-navigation').addClass('dh-change');
            $("#sidebar-menu").addClass("header-dh");
        }
    });

    $("#sidebar-menu-bg").click(function () {
        $('#sidebar-menu-bg').toggle();
        $('.header-search').removeClass('on');
        $("#joymewiki-navigation").removeClass('dh-change');
        $("#sidebar-menu").removeClass("header-dh");
        $("body").removeClass("bodypos");
    });
	console.log("Sample log");
    //sidebar - 展开

    $('.wiki-nav a[class*="visited"]').each(function (i, v) {
        if ($(v).parent().parent().attr('class') == 'sj') {
            //$(v).parent().parent().show();
            $(v).parent().parent().prev().addClass('zhank');
            $(v).parent().parent().parent().parent().prev().addClass('zhank');
            $(v).parent().parent().parent().parent().parent().addClass('active');
        } else if ($(v).parent().attr('class') == 'ej') {
            $(v).parent().parent().prev().addClass('zhank');
            $(v).parent().parent().parent().addClass('active');
        }
    });


    //左侧导航创建页面
    $('.create-wiki .createbox .search').click(function () {
        if (mw.config.get('wgUserId') > 0) {
            if ($('.create-wiki .createbox .place').val() == '') {
                $('.create-wiki .input-warn').show();
            } else {
                $('.create-wiki .input-warn').hide();
                $('.create-wiki .createbox').submit();
            }
        } else {
            // mw.loginbox.login();
            loginDiv();
        }
    });

    $(".tl-ej-nav .nav-tool").click(function () {
        if ($(".zd-tool").hasClass("zd-tool-show")) {
            $(".zd-tool").stop().animate({width: 0}, 200);
            $(".zd-tool").removeClass("zd-tool-show")
        } else {
            $(".zd-tool").stop().animate({width: 240}, 200);
            $(".zd-tool").addClass("zd-tool-show");

        }
    });
    //导航高度
    if ($(window).width() > 991) {
        //maodian_pc();

        $(".ej-nav-con .logo-focus .wiki-focus").click(function () {
            $(this).toggleClass("gz-done");
        });
        //PC通栏导航结束
        $(".wiki-action li.ej").hover(function () {
            $(".sj").removeClass("show");
            $(this).find(".sj").addClass("show");
        }, function () {

        });
        $(".wiki-action ul.sj").mouseleave(function () {
            $(".wiki-action ul.sj").removeClass("show");
        });
        // 左右侧同高js
        $(function () {
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
                // js添加广告框；
                if($('#mw-data-after-content-block').length>0){
                  $('<div class="advertise"></div>').insertBefore($("#mw-data-after-content-block"));  
                }
                var left_obj = $('#innerbodycontent>div.zd-tool');
                var right_nav_obj = $('#innerbodycontent>div.section-tjq-nav');
                var right_obj = $('#innerbodycontent>div.section-right');

                function set_height() {
                    left_obj.css("min-height", 1000);
                    right_obj.css("min-height", 1000);
                    var left_height = left_obj.height();
                    var right_height = right_obj.height();
                    if (left_height > right_height) {
                        right_obj.css('min-height', left_height);
                    } else {
                        left_obj.css('height', right_height - 30);
                        right_nav_obj.css('height', right_height);
                    }
                }

                set_height();

                $(window).scroll(function () {
                    set_height();
                });
            }
        });
        //左侧等于右侧高度js 结束
        $(document).scroll(function () {
            if ($(document).scrollTop() > $(window).height()) {
                $(".share-right .top-icon").css("display", "block");
            }
            else {
                $(".share-right .top-icon").css("display", "none");
            }
            if ($(document).scrollTop() > 42) {
                $(".tl-ej-nav").addClass("tl-ej-nav-fixed");
                //$("body").addClass("body-top");
            }
            else {
                $(".tl-ej-nav").removeClass("tl-ej-nav-fixed");
                //$("body").removeClass("body-top");
            }
        });
        if ($(document).scrollTop() > 42) {
            $(".tl-ej-nav").addClass("tl-ej-nav-fixed");
        }
        else {
            $(".tl-ej-nav").removeClass("tl-ej-nav-fixed");
        }

    } else {
		/*
        $(".sixin-popup,#sidebar-menu-bg").on("touchmove", function (e) {
            e.preventDefault();
        });
        $(".dh-change,.tpjg-tc").on("touchmove", function (e) {
            e.stopPropagation();
        });*/
        //M端左侧导航吸顶。滑动的时候改变样式
    	
        if ($(document).scrollTop() > 40) {
            $(".navbar-header2").addClass("navbar-header2-fixed");
        }
        else {
            $(".navbar-header2").removeClass("navbar-header2-fixed");
        }
        $(document).scroll(function () {
            if ($(document).scrollTop() > 40) {
                $(".navbar-header2").addClass("navbar-header2-fixed");
            }
            else {
                $(".navbar-header2").removeClass("navbar-header2-fixed");
            }
        });
        //左侧菜单栏收缩展开效果
        //左侧菜单栏收缩展开效果
        // $(".wiki-ul-ej .ej>a").unbind().on("touchend",function(e){
        //     if($(this).siblings('.sj').length>0){
        //         e.preventDefault();
        //         alert('2');
        //     }
        // });
        // $(".wiki-ul-ej .ej li>a").unbind().on("touchend",function(e){
        //     e.stopPropagation();
        // });
      
        // $(".wiki-ul-ej .sj>li>a").addClass('aaa').click(function(){
        //     alert(111);

        // });
        $(".wiki-ul-ej .sj>li>a").bind("touchend",function(e){
            e.stopPropagation();
        });
       
        $(".wiki-ul-ej .ej").unbind().on("click",function (e) {
            // e.stopPropagation();
        	// var obj = $(this);
            // clearTimeout(timernew);
            var that =this;
            // timernew = setTimeout(function(){
                if ($(that).children('.sj').length > 0 ) {
                    // e.preventDefault();
                    $(".wiki-person,.nav-left-tj").toggleClass("wiki-hide");
                    $(".wiki-action li.ej").toggleClass("ejtz");
                    $(".wiki-action .wiki-ul-ej").toggleClass("ul-ej-ys");
                    $(".wiki-action").toggleClass("m-wiki-action");
                    $(that).parents(".wiki-action").children(".fl").toggleClass("wiki-hide");
                    $(that).siblings(".ej").toggleClass("wiki-hide");
                    $(that).children('.sj').toggleClass("wiki-show");
                    $(that).children(".menu2").toggleClass("zhank1");
                    $(that).parents(".wiki-action").siblings(".wiki-action").toggleClass("wiki-hide");
                    $("#joymewiki-navigation").scrollTop(0);
                }
            // },300)
            // } else if($(this).children('.sj').hasClass('wiki-show')){

            // }


        });
        // $(".wiki-ul-ej .menu2").unbind().on("touchend",function(event){
        //      alert(3);
            
        //      if($(this).hasClass('zhank1')){
        //         event.preventDefault();
        //         event.stopPropagation();

        //         $(this).removeClass('zhank1');
        //         $(".wiki-person,.nav-left-tj").removeClass("wiki-hide");
        //         $(".wiki-action li.ej").removeClass("ejtz");
        //         $(".wiki-action .wiki-ul-ej").removeClass("ul-ej-ys");
        //         $(".wiki-action").removeClass("m-wiki-action");
        //         $("#joymewiki-navigation").scrollTop(0);
        //         $(this).parents(".wiki-action").children(".fl").removeClass("wiki-hide");
        //         // $(this).parent().toggleClass('').siblings(".ej").toggleClass("wiki-hide");
        //         $(this).siblings('.sj').removeClass('wiki-show');
        //         $(this).parent().siblings(".ej").removeClass("wiki-hide");
        //         $(this).parents(".wiki-action").siblings(".wiki-action").removeClass("wiki-hide");
        //      }
        // })
        var heightvalue = $(window).height() - 40;
        $(".section-left2").css("height", heightvalue);
        $(document).scroll(function () {
            //超过一屏显示回到顶部but
            if ($(document).scrollTop() > $(window).height()) {
                $(".wechat-share .go-top").css("display", "block");
            }
            else {
                $(".wechat-share .go-top").css("display", "none");
            }
        });
    }

});
window.onload = function () {
    function browserRedirect() {
        var sUserAgent = navigator.userAgent.toLowerCase();
        var bIsIpad = sUserAgent.match(/ipad/i) == "ipad";
        return bIsIpad;
    }

    if (browserRedirect()) {
        $('.venus-menu>li').each(function (i) {
            if ($(this).children('.indicator').length > 0) {
                $(this).children('a').attr('href', 'javascript:;')
            }
        });
    }
    (function(window, undefined) {
        '$:nomunge';
        var $ = window.jQuery || window.Cowboy || (window.Cowboy = {}), jq_throttle;
        $.throttle = jq_throttle = function(delay, no_trailing, callback, debounce_mode) {
            var timeout_id, last_exec = 0;
            if (typeof no_trailing !== 'boolean') {
                debounce_mode = callback;
                callback = no_trailing;
                no_trailing = undefined;
            }
            function wrapper() {
                var that = this
                  , elapsed = +new Date() - last_exec
                  , args = arguments;
                function exec() {
                    last_exec = +new Date();
                    callback.apply(that, args);
                }
                ;function clear() {
                    timeout_id = undefined;
                }
                ;if (debounce_mode && !timeout_id) {
                    exec();
                }
                timeout_id && clearTimeout(timeout_id);
                if (debounce_mode === undefined && elapsed > delay) {
                    exec();
                } else if (no_trailing !== true) {
                    timeout_id = setTimeout(debounce_mode ? clear : exec, debounce_mode === undefined ? delay - elapsed : delay);
                }
            }
            ;if ($.guid) {
                wrapper.guid = callback.guid = callback.guid || $.guid++;
            }
            return wrapper;
        }
        ;
        $.debounce = function(delay, at_begin, callback) {
            return callback === undefined ? jq_throttle(delay, at_begin, false) : jq_throttle(delay, callback, at_begin !== false);
        }
        ;
    })(this);
};

/*function maodian_pc() {
    $(document).scroll(function () {
        if ($(document).scrollTop() > $(".section-recommend .section-tjq").height() + 60) {
            $(".section-nav").addClass("section-nav-show");
        }
        else {
            $(".section-nav").removeClass("section-nav-show");
        }
    });
	$("#toc").appendTo(".section-nav-con");
	$(".section-nav .toc").before('<div class="side-bar"><em class="circle start"></em><em class="circle end"></em></div>');
	$(".section-nav #toc").after('<div class="right-wrap"><a class="go-up disable" href="javascript:void(0);"></a><a class="go-down" href="javascript:void(0);"></a></div>');
	$(".section-nav").append('<div class="bottom-wrap"><a class="toggle-button" href="javascript:void(0);"></a></div>');
	$(".section-nav .toc>ul>li").prepend('<em class="pointer"></em>');
	$(".section-nav .toc>ul li a").prepend('<cite></cite>');

      var count = 0;
      var timer = setInterval(function(){
        if(count<20){
          console.log(count);
          if(typeof $.fn.perfectScrollbar == "function"){
            clearInterval(timer);
            
            $(".section-nav .toc").perfectScrollbar();
            return ;
          }
          count++
        }else{
          console.log(count);
          clearInterval(timer);
        }
      },400);
      
   
	$(".bottom-wrap a").click(function () {
	    $(".section-nav-con").toggleClass("collapse");
	});
	
	//底部对齐代码
    $(document).scroll(function () {
        var aaa = $(".section-bodycontent").height() - $(document).scrollTop();
        if (aaa < 670 && aaa > 315) {
            $(".section-nav").addClass("section-nav-bottom");
            $(".section-nav").stop(true).animate({
                'bottom': 740 - aaa
            }, 0);
        }

        else if (aaa <= 315) {
            $(".section-nav").addClass("section-nav-bottom");
            $(".section-nav").stop(true).animate({
                'bottom': 393
            }, 0);
        }

        else {
            $(".section-nav").removeClass("section-nav-bottom");
        }

    });
//左右内容对应
    var arr = new Array();
    for (var i = 0; i < $("#mw-content-text .mw-headline").length; i++) {
        arr.push($("#mw-content-text .mw-headline").eq(i).offset().top);
    }
    var index = 0;
    var isn = true;
    
    if ($('#toc').length) {
	    $(window).scroll(function () {
	
	        var scroll_len = $(window).scrollTop() + 100;
	        arrtop(scroll_len);
	
	    });
	    var a = [];
	    var stop = 0;
	
	    function arrtop(scroll_len) {
	        a = [];
	        for (var i = 0; i < arr.length; i++) {
	            a.push(arr[i]);
	        }
	        a.push(scroll_len);
	        for (var j = 0; j < a.length; j++) {
	            if (a[a.length - 1] >= a[j] && a[a.length - 1] <= a[j + 1]) {
	            	$('.section-nav .toc>ul li a').removeClass("current");
	                $('.section-nav .toc>ul li a').eq(j).addClass("current");
	                //$('.section-nav .toc>ul li a').eq(j).focus().blur();
	                //stop = ($(window).scrollTop() - $('#mw-content-text').offset().top )/$('#mw-content-text').height()*$(".section-nav .toc").height();
	                stop = $('.section-nav .toc>ul li a').eq(j).offset().top - $('.section-nav .toc>ul').offset().top;
	                $(".section-nav .toc").scrollTop(stop);
	            }
	        }
	    }
    }
    $('.section-nav ul a').on('click', function (event) {
        sidebarScroll($(this));
        return false;
    });
    function sidebarScroll(that) {
        var title = that.attr('href');
        var top = $(title).offset().top - $('.ej-nav-wrap').height();
        $('body,html').stop(true).animate({scrollTop: top}, 500);
    }
}*/

$(function () {
    var myTop = $(".section-nav .toc").scrollTop();
    var boxH = $(".section-nav .toc").height();
    var addNUm = boxH / 10;
    // 元素滚动事件开始----------------------------------------------------------------
    $(".section-nav .toc").scroll(function () {
        myTop = $(".section-nav .toc").scrollTop();
        var boxH = $(".section-nav .toc > ul").height() - $(".section-nav .toc").height();
        if (myTop <= 0) {
            myTop = 0;
            $(".section-nav .go-up").addClass("disable");
            $(".section-nav .go-down").removeClass("disable");
        }
        else if (myTop > 0 && myTop <= boxH) {
            $(".section-nav .go-up").removeClass("disable");
            $(".section-nav .go-down").removeClass("disable");
        }
        else {
            $(".section-nav .go-down").addClass("disable");
            $(".section-nav .go-up").removeClass("disable");
        }
    });
    // 元素滚动事件结束----------------------------------------------------------------

    // 点击向下按钮开始------------------------------------------------------------------

    $(".section-nav  .go-down").click(function () {
        $(".section-nav  .go-up").removeClass("disable");
        myTop += addNUm;
        console.log(myTop, boxH);

        if (myTop < boxH) {
            $(".section-nav .toc").scrollTop(myTop);
        } else {
            myTop = boxH;
            $(".section-nav .go-down").addClass("disable");
        }
        console.log(myTop);
    });
    // 点击向下按钮结束------------------------------------------------------------------

    // 点击向上按钮开始------------------------------------------------------------------
    $(".section-nav  .go-up").click(function () {
        $(".section-nav  .go-down").removeClass("disable");
        myTop -= addNUm;
        $(".section-nav .toc").scrollTop(myTop);
        if (myTop <= 0) {
            myTop = 0;
            $(".section-nav  .go-up").addClass("disable");
        }
    });
});
