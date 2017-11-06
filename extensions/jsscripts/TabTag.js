;$(function(){
	window.setTab = function(obj, id){
		$(obj).siblings().attr('class', '');
		$("#"+id).children().hide();
		$(obj).attr('class', 'hover');
		var new_id = $(obj).attr("id");
		$("#con_"+new_id).show();
	}
});