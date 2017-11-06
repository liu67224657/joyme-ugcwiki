$(document).ready(function() {
var w = $(window).width();
  //list1中间i宽度控制
	
	$(".list-wjm li").each(function(){
		var wli = $(this).width();
		var wli1=$(this).find(".tag").width();
		var wli2=$(this).find(".date").width();
		$(this).find(".tit").width(wli-wli1-wli2-37);
	});
	

	$(".list2-wjm li").each(function(){
		var w2 = $(this).width();
		$(this).find(".list-titall").css({width:w2-140});
		if($(window).width()<720){
			var w3 = $(this).width();
			$(this).find(".list-titall").css({width:w3-108});
	
			} 
	});
	
	
	//多行文字溢出隐藏+变省略号
	$(".list2-wjm .tit").each(function(i){
		var divH = $(this).height();
		var $p = $("u", $(this)).eq(0);
		while ($p.outerHeight() > divH) {
			$p.text($p.text().replace(/(\s)*([a-zA-Z0-9]+|\W)(\.\.\.)?$/, "..."));
		};
	});

	

	//列表2最后一个li的下边距去掉
	num=$(".list2-wjm li").length;
	$(".list2-wjm li").eq(num-1).css("marginBottom","0px");
	//列表3最后一个li的右边距去掉
	num1=$(".list3-wjm").length;
	$(".list3-wjm").eq(num1-1).css("marginRight","0px");

	//手机版
	if($(window).width()<720){
		//列表3img外框宽度自适应
		$('.list3-wjm .list-img').each(function (){
				var wwww=	$(this).parents(".list3-wjm").width();	
				$(this).width(wwww-24)
		});
		}else{
			}
	//ipad特有
	function isIpad(){
	  var browser = navigator.userAgent;
	  if(browser.indexOf('iPad')>-1){
		return true;
	  }
	  return false;
	}
	
	//ipad独有
	if(isIpad()){
	  $(".list3-wjm").css({width:320});
	  
	  // 说明是Ipad；
	}else{
	  // 不是ipad；
	}
		

});