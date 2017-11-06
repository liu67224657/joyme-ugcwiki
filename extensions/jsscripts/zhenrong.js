$(function(){
	var zlist = [];
	var zr_one_num = parseInt($('#zr_one_num').val());
	var zr_on_num = parseInt($('#zr_on_num').val());
	var zr_num = zr_one_num+1-zr_on_num;
	if(zr_num>zr_one_num){
		zr_num = zr_one_num;
	}
	(function zhenrong_init(){
		$('#zhenrong div[data-zhenrong-name]').each(function(i){
			if($(this).find("div:first").css("display") == "block"){
				zlist[zlist.length+1] = $(this).attr('data-zhenrong-name');
			}
		});
	})();
	$('#zhenrong div[data-zhenrong-name]').click(function(){
		if(in_array($(this).attr('data-zhenrong-name'),zlist)){
			zlist = del_array($(this).attr('data-zhenrong-name'),zlist);
			$(this).find("div:first").hide();
		}else{
			zlist[zlist.length+1] = $(this).attr('data-zhenrong-name');
			$(this).find("div:first").show();
		}
	});
	$('#zhenrong_create').click(function(){
		for(var i=0,j=zr_num;i<j;i++){
			$("#zhenrong"+i).html('');
			$('#zhenrongt'+i).hide();
		}
		$(".zhenrong").attr('data-zhenrong-num','0');
		$(".zhenrong div[data-zhenrong-name]").find("div:first").show();
		$(".zhenrong").hide();
		$('.zhenrong div[data-zhenrong-name]').each(function(i){
			if(in_array($(this).attr('data-zhenrong-name'),zlist)){
				$(this).find("div:first").hide();
				var num = parseInt($(this).parents(".zhenrong").attr('data-zhenrong-num'));
				num++;
				$(this).parents(".zhenrong").attr('data-zhenrong-num',num);
			}
		});
		$("#zhenrong-null").show();
		$('.zhenrong[data-zhenrong-num]').each(function(i){
			var num = parseInt($(this).attr('data-zhenrong-num'));
			if(num>=zr_on_num){
				$('#zhenrong'+(zr_one_num-num)).append($(this).clone());
				$('#zhenrong'+(zr_one_num-num)).find('.zhenrong').show();
				$('#zhenrongt'+(zr_one_num-num)).show();
				$("#zhenrong-null").hide();
			}
		});
	});
	$('#zhenrong_clear').click(function(){
		zlist = [];
		for(var i=0,j=zr_num;i<j;i++){
			$("#zhenrong"+i).html('');
			$('#zhenrongt'+i).hide();
		}
		$('#zhenrong div[data-zhenrong-name]').find("div:first").hide();
		$(".zhenrong").attr('data-zhenrong-num','0');
		$(".zhenrong div[data-zhenrong-name]").find("div:first").show();
		$(".zhenrong").hide();
	});
	function in_array(search,array){
		for(var i in array){
			if(array[i]==search){
				return true;
			}
		}
		return false;
	}
	function del_array(search,array){
		var narr = [];
		var tmp = 0;
		for(var i in array){
			if(array[i]==search){
				tmp++;
				continue;
			}
			if(tmp == 0){
				narr[i] = array[i];
			}else{
				narr[i-tmp] = array[i];
			}
		}
		return narr;
	}
}); 