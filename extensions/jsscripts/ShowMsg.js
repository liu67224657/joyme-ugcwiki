$(function(e){
	var e = e || window.event;
	var id, relativeX, relativeY, ehight, escrollTop;
	
	$(".show_or_hide_msg").hover(
		function (e) {
			$(this).children("a").removeAttr("title");
			id = $(this).attr("data-msg");
			//relativeX = $(this).offset().left-$('#bodyContent').offset().left-($("#"+id).width()-$(this).width())/2;
			relativeX = $(this).offset().left-$('#bodyContent').offset().left;
			relativeY = $(this).offset().top-$('#bodyContent').offset().top+35;
			ehight = $(window).height();
			escrollTop = $(window).scrollTop();
			if((ehight-$(this).offset().top) > $("#"+id).height()){
				$("#"+id).css({display:"block", position:"absolute", zIndex:"9999", left:relativeX, top:relativeY });
			}else if($("#"+id).height()<($(this).offset().top-escrollTop)){
				relativeY = $(this).offset().top-$('#bodyContent').offset().top-20-$("#"+id).height();
				$("#"+id).css({display:"block", position:"absolute", zIndex:"9999", left:relativeX, top:relativeY });
			}else{
				$("#"+id).css({display:"block", position:"absolute", zIndex:"9999", left:relativeX, top:relativeY });
			}
		},
		function () {
			$("#"+id).hide();
		}
	);
});