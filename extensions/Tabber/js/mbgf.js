$(document).ready(function() {


	//超出宽度的横版按钮隐藏
  	$('.news1-wjm').each(function (){
		var that=$(this);
		var mb=$(this).children(".meunbox1-wjm").children(".mb-box")
		var n=$(this).children(".meunbox1-wjm").children(".mb-box").length;
		var wt=$(this).width();if($(window).width()<720){wt=$(this).width()-20};
		var gd= $(this).children(".meunbox1-wjm").children(".gengduo").width();
		var sum=0;
		var ll=n-1;
		var tttt=wt-gd-40;if($(window).width()<720){tttt=wt-gd-20}
		for(var num = 0; num < n; num++ ){
			var uu=$(this).children(".meunbox1-wjm").children(".mb-box").eq(num).width()+40;if($(window).width()<720){uu=$(this).children().children(".mb-box").eq(num).width()+20}
			sum+=uu;
			if(sum > tttt){
                        mb.eq(ll).hide();
						var newDiv = $('<a>');
						newDiv.attr('class','hm-box').prependTo($(this).children(".meunbox1-wjm").children(".hid-menu-wjm"));
						var text= mb.eq(ll).text()
						newDiv.text(text);ll=ll-1;
						 
             }
			}
			
			if($(this).children(".meunbox1-wjm").children(".hid-menu-wjm").children(".hm-box").length>0){$(this).children(".meunbox1-wjm").children(".gengduo").show()}
	});
	//pc端下拉菜单最后一个box去掉右侧间距
	if($(window).width()>720){
		$('.hm-box').each(function (i){		
			var wd2=$(this).parents(".hid-menu-wjm").width();
			var n=(wd2+10)/102;
			var qq=Math.floor(n);
			var v=i+1;
			if( wd2-qq*102+10>=102){
				qq=qq+1;
				if(v % qq == 0 ){
					$(this).css("marginRight","0px")
					}
			}else{
				if(v % qq == 0 ){
					$(this).css("marginRight","0px")
					}
				 }	
		})
	}
	
	//手机端超出宽度的竖版按钮隐藏
	if($(window).width()<720){
		$('.news1-wjm').each(function (){
		var that=$(this);
		var mb=$(this).children(".meunbox3-wjm").children(".mb-box")
		var n=$(this).children(".meunbox3-wjm").children(".mb-box").length;
		var wt=$(this).width()-20;
		var gd= $(this).children(".meunbox3-wjm").children(".gengduo").width();
		var sum=0;
		var ll=n-1;
		var tttt=wt-gd-20;
		for(var num = 0; num < n; num++ ){
			var uu=$(this).children(".meunbox3-wjm").children(".mb-box").eq(num).width()+20;
			sum+=uu;
			if(sum > tttt){
                        mb.eq(ll).hide();
						var newDiv = $('<a>');
						newDiv.attr('class','hm-box').prependTo($(this).children(".meunbox3-wjm").children(".hid-menu-wjm"));
						var text= mb.eq(ll).text()
						newDiv.text(text);ll=ll-1;
						 
             }

			 
			}
			
			if($(this).children(".meunbox3-wjm").children(".hid-menu-wjm").children(".hm-box").length>0){$(this).children(".meunbox3-wjm").children(".gengduo").show()}
		});
	}

	//横版按钮的条初始宽度设置
	$(".tiao").each(function(){
			var www1=$(this).siblings(".mb-box").eq(0).width();
			$(this).width(www1+40);
			if($(window).width()<720){
				$(this).width(www1+20);
			}
		});
	//横版按钮的条动画效果
		var timer;
		$(".meunbox1-wjm .mb-box").on({
		"mouseenter":function(){
			clearTimeout(timer);
			$(this).addClass('mb-box-on');
			$(this).siblings(".mb-box").removeClass('mb-box-on');
			$(this).siblings(".hid-menu-wjm").children(".hm-box").removeClass("hm-box-on");
			 var that=$(this);
			timer=setTimeout(function(){
			   var po1=that.position().left;
					that.siblings('.tiao').animate({ left:po1},"fast");
					 var wd=that.width(); 
					 that.siblings('.tiao').css({width:wd+40});//这里触发hover事件
					 if($(window).width()<720){
						that.siblings('.tiao').css({width:wd+20});;	
					 }
			},100);
		},
		"mouseleave":function(){
			clearTimeout(timer);
		}
		});
		
			
		//横版按钮下拉菜单条动画
	$('.meunbox1-wjm .gengduo').hover(function(){
		var po1=$(this).position().left;
		var wd=$(this).width();
		$(this).siblings('.tiao').css({width:wd+40});
		$(this).siblings('.tiao').animate({ left:po1},"fast");
	});
	//横版按钮手机版下拉菜单点击条动画
		if($(window).width()<720){			
			$('.meunbox1-wjm .gengduo').hover(function(){
				var wd=$(this).width();
				$(this).siblings('.tiao').css({width:wd+20});
			});
			
			}
	//横版按钮下拉菜单点击框小图旋转
	
			$(".meunbox1-wjm .gengduo").click(function(){
			    var hdmenu=$(this).siblings(".hid-menu-wjm")
				if($(this).siblings(".hid-menu-wjm").css("display") == "none"){
					hdmenu.show(0).animate({bottom:'-116px',height:'102px'},"fast");
					if($(window).width()<720){ $(this).addClass("xiala1");}
				}else{
					hdmenu.animate({bottom:'-0px',height:'0px'},"fast").hide(0);	
					if($(window).width()<720){ $(this).removeClass("xiala1");}
				}
					
			})
		
	
	
	//横版按钮，鼠标离开隐藏菜单，隐藏菜单消失
	var timer2;
		$(".hid-menu-wjm").on({
		"mouseenter":function(){
			clearTimeout(timer2);
			$(this).show(); 
		},
		"mouseleave":function(){
			clearTimeout(timer2);
			var that=$(this);
			timer2=setTimeout(function(){
			  that.animate({bottom:'0px',height:'0px'},"fast").hide(0);
			},150);
			if($(window).width()<720){ $(this).siblings(".gengduo").removeClass("xiala1");}
		}
		});
		

	//竖版按钮pc端hover效果
		$('.meunbox3-wjm .mb-box').hover(function(){
				$(this).addClass('mon3');
				$(this).siblings().removeClass("mon3");
				},function(){

			});

     
	 //竖版按钮pc端内容窗口宽度设定
	 if($(window).width()>=720){
	 	$('.news1-con-all-3').each(function (){
			var w3=$(this).parents(".wjm1125").width();
			$(this).css({width:w3-150});
			});
			}	
    
	//横版按钮pc点击出现相应内容
	if($(window).width()>720){
		$('.meunbox1-wjm .mb-box').hover(function(){
		var num = $(this).index();
		$(this).parents(".meunbox1-wjm").siblings('.news1-con-all').children(".news1-con").hide();
		$(this).parents(".meunbox1-wjm").siblings('.news1-con-all').children(".news1-con").eq(num).show();
	})
		}else{
			//横版按钮手机点击效果
			$('.meunbox1-wjm .mb-box').click(function(){
				var num = $(this).index();
				$(this).parents(".meunbox1-wjm").siblings('.news1-con-all').children(".news1-con").hide();
				$(this).parents(".meunbox1-wjm").siblings('.news1-con-all').children(".news1-con").eq(num).show();
				
			})
			}
	
	
	//横版下拉菜单悬停显示内容框
	if($(window).width()>720){	
		$('.hid-menu-wjm .hm-box').hover(function(){
			$(this).parents(".hid-menu-wjm").siblings(".mb-box").removeClass("mb-box-on");
			num3=$(this).parents(".meunbox1-wjm").children(".mb-box:hidden").length;
			num2=$(this).parents(".meunbox1-wjm").children(".mb-box").length
			num4=num2-num3;
			var num1 = $(this).index();
			$(this).parents(".meunbox1-wjm").siblings('.news1-con-all').children(".news1-con").hide();
			$(this).parents(".meunbox1-wjm").siblings('.news1-con-all').children(".news1-con").eq(num1+num4).show();
			
		})
	}
	//  竖版按钮效果
	if($(window).width()<720){
		//手机端竖版按钮点击效果
		$('.meunbox3-wjm .mb-box').click(function(){
		//1.显示相应内容框
			var num = $(this).index();
			$(this).parents(".meunbox3-wjm").siblings('.news1-con-all').children(".news1-con").hide();
			$(this).parents(".meunbox3-wjm").siblings('.news1-con-all').children(".news1-con").eq(num).show();
		//1.5 去掉隐藏菜单样式
			$(this).siblings(".hid-menu-wjm").children(".hm-box").removeClass("hm-box-on");
		//2.条动画
			var po=$(this).position().left;
			var wd=$(this).width();
			$(this).siblings('.tiao').css({width:wd+20});
			$(this).siblings('.tiao').animate({ left:po});
		});
		//竖版按钮手机端下拉菜单点击效果
		$('.meunbox3-wjm .gengduo').click(function(){
			var po=$(this).position().left;
			var wd=$(this).width();
			$(this).siblings('.tiao').css({width:wd+20});
			$(this).siblings('.tiao').animate({ left:po});
			if($(this).siblings(".hid-menu-wjm").css("display") == "none"){
				$(this).siblings(".hid-menu-wjm").show(0).animate({bottom:'-116px',height:'102px'},"fast");
				$(this).addClass("xiala1");
				
			}else{
				$(this).siblings(".hid-menu-wjm").animate({bottom:'-0px',height:'0px'},"fast").hide(0);
				 $(this).removeClass("xiala1");
			}
		})

	}else{
		//pc端竖版菜单hover效果
		$('.meunbox3-wjm .mb-box').hover(function(){
			var num = $(this).index();
			$(this).parents(".meunbox3-wjm").siblings('.news1-con-all').children(".news1-con").hide();
			$(this).parents(".meunbox3-wjm").siblings('.news1-con-all').children(".news1-con").eq(num).show();
		});
	}
	
	//hmbox手机端竖版按钮下拉菜单点击出现内容框效果
	if($(window).width()<720){
		$('.hid-menu-wjm .hm-box').click(function(){
			$(this).addClass('hm-box-on');
			$(this).siblings('.hm-box').removeClass("hm-box-on")
			$(this).parents(".hid-menu-wjm").siblings(".mb-box").removeClass("mb-box-on")
			$(this).parents(".hid-menu-wjm").siblings(".mb-box").removeClass("mon3")
			num3=$(this).parents(".meunbox3-wjm").children(".mb-box:hidden").length;
			num2=$(this).parents(".meunbox3-wjm").children(".mb-box").length
			num4=num2-num3;
			var num1 = $(this).index();
			$(this).parents(".meunbox3-wjm").siblings('.news1-con-all').children(".news1-con").hide();
			$(this).parents(".meunbox3-wjm").siblings('.news1-con-all').children(".news1-con").eq(num1+num4).show();
		});
	}
	
	
	
	//ipad独有js
	function isIpad(){
	  var browser = navigator.userAgent;
	  if(browser.indexOf('iPad')>-1){
		return true;
	  }
	  return false;
	}
	
	if(isIpad()){
	  $('.hm-box').hover(function(){
			$(this).addClass('hm-box-on');
			$(this).siblings('.hm-box').removeClass("hm-box-on")
			$(this).parents(".hid-menu-wjm").siblings(".mb-box").removeClass("mb-box-on")
		});
	  // 说明是Ipad；
	}else{
		//下拉菜单box点击效果
		$('.hm-box').click(function(){
			$(this).addClass('hm-box-on');
			$(this).siblings('.hm-box').removeClass("hm-box-on")
			$(this).parents(".hid-menu-wjm").siblings(".mb-box").removeClass("mb-box-on")
		});
	  // 不是ipad；
	}
	
	//旋转屏幕
	$(window).on("orientationchange",function(){
	  $('.news1-con-all-3').each(function (){
			var w3=$(this).parents(".wjm1125").width();
			$(this).css({width:w3-150});
			});
	});
	
})