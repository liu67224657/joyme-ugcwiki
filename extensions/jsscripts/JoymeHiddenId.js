$(function($){	
	var hostname =  window.location.hostname;
	var noneObj = {};
	var host_url = window.location.href;
	var host_split_d = hostname.split('.');
	var pfm = hostname.split('.')[1];
	if(pfm == 'joyme'){
		pfm = 'pc';
		if(host_split_d[0]=='www'){
			var host_split_xg = host_url.split('/')[3];
			if(host_split_xg =='mwiki' || host_split_xg =='appwiki' || host_split_xg =='360wiki'){
				pfm = 'm';
			}else if(host_split_xg =='wiki'){
				pfm = 'pc';
			}
		}
	}
	//alert(pfm);
	var tag = $("#hidden-tag").val().split('||');
	$.each(tag,function(i,val){
		var st_val = val.split('-');
		if(st_val[0] == pfm){
			noneObj = st_val;
		}
	});
	$.each(noneObj,function(i,val){
		if(i!=0){
			$("#"+val).css('display','none');
		}
	});
});