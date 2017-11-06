$(document).ready(function () { 	
	var m,swfname,htmlname,moviepic,vWidth,vHeight;
	var hostname =  window.location.hostname;
	//var hostname = 'http://mt.joyme.com';
	
	if(hostname == 'localhost'){
			favorite_url_name = window.location.hostname;
	}else{
		var valArray=hostname.split('.');
	    m = valArray[1];
	}
	var movie_all =  $("#show_div").val();
	$.each(movie_all.split('_'),function(i,val){
		var swfname = $("#move_"+val+"_swfname").val();
		var htmlname = $("#move_"+val+"_htmlname").val();
		var moviepic = $("#move_"+val+"_moviepic").val();
		var vWidth = $("#move_"+val+"_vWidth").val();
		var vHeight = $("#move_"+val+"_vHeight").val();
		if(m == 'm'){
			//<a href=\"+htmlname+\" target=\"_blank\" ><img width=\"+vWidth+\" height=\"+vHeight+\" src=\"+moviepic+\"><\/a>
			$("#"+val).html("<a href='"+htmlname+"' target='_blank' ><img width="+vWidth+" height="+vHeight+" src='"+moviepic+"'></a>"+'<div><span style="color:red;">如果您是在手机端浏览的用户,请从此窗口进入视频</span>&nbsp;:&nbsp;&nbsp;<a href="'+htmlname+'" target="_blank" >[&nbsp;手机视频入口&nbsp;]</a></div>');
		}else{
			var src ;
			$("#"+val).html("<object width='528' height='420' align='middle' classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' codebase='http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0'><param name='AllowFullScreen' value='true'><param name='allowScriptAccess' value='sameDomain'><param name='movie' value='"+swfname+"'><param name='wmode' value='Transparent'><embed width='"+vWidth+"' height='"+vHeight+"' allowfullscreen='true' wmode='Transparent' type='application/x-shockwave-flash' src='"+swfname+"'></object>");
		}
	});
	
	

});