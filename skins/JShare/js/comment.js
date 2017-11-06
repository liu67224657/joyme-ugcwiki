//var commentRelease = (function(){
;var postComment = (function(){
    var locationUrl = location.href;
    var _com = "alpha";
    if(locationUrl.indexOf(".alpha")>-1){
        _com = 'alpha';
    }else if(locationUrl.indexOf('.beta')>-1){
        _com = "beta";
    }else if(locationUrl.indexOf('.com')>-1){
        _com = "com"
    }
    var api = "http://api.joyme." + _com;
    var api2 = "http://wikiservice.joyme." + _com;
    var static ="http://static.joyme." + _com; 
    var title = mw.config.get('wgTitle');
    var desc = '';
    var pic = '';
    var unikey= '';
    var share_task = null;
    var domain = '';
    var pnum = 1;
    var psize = 10;
    var uid = "";
    var comment_p = null;
    var comment_num = 0;
    var comment_sum = 0;
    // var pid = '6decd31939f18bd154935b956338bbfc';
    var archiveid = '';

    
    // alert("cookie:" + document.cookie);
    var cookieString = decodeURIComponent(getCookie('JParam'));
    // alert("JParam:" + cookieString);
    var objCookie = fomatCookie(cookieString);
    /*alert("appkey :" + objCookie.appkey );

    alert("uno :" + objCookie.uno );
    alert("uid :" + objCookie.uid );
    alert("logindomain:" + objCookie.logindomain);*/
    var appUserId = objCookie.pid;
    var logindomain = objCookie.logindomain;
    var pid = objCookie.pid;
    var uno = objCookie.uno;
    // alert(JSON.stringify(objCookie));
    function fomatCookie(cookieString){
        var arr = cookieString.split('; ');
        var obj = {};
        for(var i=0;i<arr.length;i++){
            var arr2 = arr[i].split('=');
            obj[arr2[0]] = arr2[1];
        }
        return obj;
    }

    function getCookie(name){
        var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
        if(arr=document.cookie.match(reg))
        return unescape(arr[2]);
        else
        return null;
    }
    function setCookie(name,value) 
    { 
        var Days = 30; 
        var exp = new Date(); 
        exp.setTime(exp.getTime() + Days*24*60*60*1000); 
        document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString(); 
    } 
    var browser={  
        versions:function(){  
               var u = navigator.userAgent, app = navigator.appVersion;  
               return {//移动终端浏览器版本信息  
                    trident: u.indexOf('Trident') > -1, //IE内核  
                    presto: u.indexOf('Presto') > -1, //opera内核  
                    webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核  
                    gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核  
                    mobile: !!u.match(/AppleWebKit.*Mobile.*/)||!!u.match(/AppleWebKit/), //是否为移动终端  
                    ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端  
                    android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或者uc浏览器  
                    iPhone: u.indexOf('iPhone') > -1 || u.indexOf('Mac') > -1, //是否为iPhone或者QQHD浏览器  
                    iPad: u.indexOf('iPad') > -1, //是否iPad  
                    webApp: u.indexOf('Safari') == -1, //是否web应该程序，没有头部与底部  
                    weixin:u.indexOf('MicroMessenger') > -1
                };  
             }(),  
             language:(navigator.browserLanguage || navigator.language).toLowerCase()  
    } 

    var pageHandler={
        init:function(){
            this.block = false,
            this.isLoading = false,
            this.loadTime = null,
            this.versions = browser.versions;
            
            // this.store(); // 收藏;
            // this.renderArticalZan();
            this.documentready()
            this.winH = $(window).height();
            // this.renderComments();
            // this.renderShare();
            // this.renderGameName();

        },
        documentready:function(){
            var that = this;
            $(function(){
                FastClick.attach(document.body);
                that.fastClick = 0;
                comment_p = $('.comment-p');
                // title = $('#_title').text();
                desc = $('#_desc').text();
                pic = $('#_clientpic').text();
                pic = pic=="无图片信息！"?'':pic;
                // var shareImg = $('.art-body p img:first');
                // if(shareImg.length > 0){
                //     $('#_clientpic').text(shareImg.attr('src'))
                    
                // }else{
                //     $('#_clientpic').text(static+'/mobile/cms/apparticledetail/img/app.png');
                // }
                that.weichat = $('.wechat-share');
                unikey=window.wgWikiname + '|' + title;
                domain=6;
                archiveid = unikey;
                // that.authorTime = $('.author-wrapper').find('.time');
                // var text = $('#_ptime').text().replace(/年/g,'/').replace(/月/,'/').replace(/日/,'');
                // var ms = new Date(text).getTime();
                // share_task = $('#_share_url');
                // $('#_ptime').text(ms);
                // that.authorTime.text(that.caculateTime(ms));
                // that.renderArticalZan();
                // that.renderShare();
                // that.renderGameName();
								that.PCorM();
                // that.bindEvent();
                // that.onloaded();
            })
        },
        initScroll:function(){
    	    var BSobj = {
              probeType: 1,
            }
            if(this.versions.android){
                BSobj.click = true;
            }
            this.scroll = new window.BScroll(document.getElementById('wrapper'), BSobj);
        	this.initLoading();
        },
        initLoading:function(){
        	var dom = '<div class="loading-wrapper bottom">'+
				        	     '<div class="uil-default-css" style="transform:scale(0.15);-webkit-transform:scale(0.15)"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>'+
				        	     '</div>'+
				        	   '</div>';
				        	   $(dom).appendTo($("body"));
        },
        PCorM:function(){
            if(this.versions.android || this.versions.ios){
                // m 端
                this.initScroll();
                // this.renderComments();
                this.bindEvent();
                this.onloaded();
            }else{
                // pc端；
                // this.renderComments();
                // this.bindPc();
            }
            this.comment = new Commentjs({
                jsonparam:{
                    title:title,
                    pic:pic,
                    uri:'',
                    description:desc,
                },
                data:{
                    unikey:unikey,
                    domain:domain,
                    pnum:1,
                    psize:10,
                    flag:false,
                    uid:'',
                    callback:"querycallback",
                },
                scrollObj:{
                    initEl:$('#bigwrapper'),
                    innerEl:$('#bigwrapper>div:first'),
                    scroll:this.scroll,
                }
            })
            this.comment.renderComments();
        },
        onloaded:function(){
            var that = this;
            window.onload=function(){
                that.scroll.refresh();
                
            }
        },
        
        
       
        
       
  
        bindEvent:function(){
            // 下拉刷新
            var that = this;
              
            this.scroll.on('touchend', function (pos) {
              // if (pos.y > 50) {
              //   setTimeout(function () {
              //     location.reload();
              //   }, 1000)
              // }
              var documentH = $('#bigwrapper>div').height();
              var screenH = $(window).height();
              if ((-pos.y+screenH)-documentH > 60) {
                  // 上拉加载
                  that.comment.renderComments();
                  //that.scroll.scrollTo(0,-documentH,200,'easing');
                  //that.scroll.refresh();
              }
            })
          
           
            // 查看大图
            this.bindImage();
            this.toTop();
            
        },
        toTop:function(){
            var that = this;
            this.lastPagex = null;
            this.scroll.on('scrollEnd',function(){
                console.log(this.absStartY,that.winH,this)
                if(that.lastPagex !== this.absStartY){

                    if(-this.absStartY > that.winH){
                        that.weichat.show();

                    }else{
                        that.weichat.hide();
                    }
                }
                that.lastPagex = this.absStartY
            })
            this.weichat.find('.go-top').click(function(){
                that.scroll.scrollTo(0,0,200);
                that.weichat.hide();
            })
        },
        bindImage:function(){
            var that =this;
                var imgs = $('.art-body').find('img'),arrImg=[];
                imgs.each(function(ind,item){
                   
                    arrImg.push(encodeURIComponent($(this).attr('src')));
                    $(this).data('index',ind);
                })
                imgs.on('click',function(e){
                    if(that.fastClick==0){
                        that.fastClick++;
                        e.stopPropagation();
                        console.log(that.fastClick);
                        var ind = $(this).data('index');
                        var src = encodeURIComponent($(this).attr('src'));
                        _jclient&&_jclient.openImg('img=' + src +"&position=" + ind +"&imgs=" + arrImg)
                        setTimeout(function(){
                            that.fastClick=0;
                        },1500)
                    }


                })
        },
        
        
        caculateTime:function(ms){
            // ms 评论发布时间 毫秒
            var oldDate = new Date(ms);
            var curDate = new Date().getTime();
            var chaMs = curDate-oldDate;

            var chaMin = 3600000,chaHour = 24*chaMin,chaWeek=7*chaHour,chaMonth = 4* chaWeek;

            var second = chaMs/1000/60;
            var hour = second/60;
            var day = hour/24;
            var week = day/7;
            if(chaMs<chaMin){
                if(chaMs < 60000){
                    return '刚刚'
                }else{

                    return Math.floor(second) + '分钟前'
                }
                // <59min
            }else if(chaMs >= chaMin && chaMs < chaHour ){
                // <hour
                return Math.floor(hour) + '小时前'
            }else if(chaMs >= chaHour && chaMs < chaWeek){
                // <day

                return Math.floor(day) + '天前'
            }else if(chaMs >= chaWeek && chaMs < chaMonth){
                // <week

                return Math.floor(week) + '周前'
            }else if(chaMs >= chaMonth){
                // <month
                var month = parseInt(oldDate.getMonth())+1;
                return oldDate.getFullYear() + "年" + month + "月" + oldDate.getDate() + "日"
            }

        },
       
    }
    pageHandler.init();
    // console.log(pageHandler.commentRelease);
    return function(obj){pageHandler.comment.commentRelease(obj)}

})();
// console.log(commentRelease);
