
	var paramArray = new Array();
	var selectedArray = new Array();
	var buttonObj = new Array();
	var lastGroupName = '';
	var lastButtonObj = new Array();
	var showDiv = new Array();
	var allSelect = -1;
	var allselectObj= new Array();
	//$('div[data-param3=2][data-param2=200][data-param1=mt][data-param1=mt]').css('display','');
	$(document).ready(function(){
		$('.wikitable>tbody>tr>th>span').parent().addClass('headerSort');
		$('.cardSelectOption').click(function(){
			$('.wikitable>tbody>tr>th>span').parent().removeClass('headerSortDown headerSortUp');
			var button=$(this);
			$(".CardSelect div[data-param0='0']").css('display','none');
			var param=$(this).attr('data-option');
			var groupNowName=button.attr('data-group');
			var currKey='';
			var currValue='';
			var ponseOther = new Array();
			$.each(param.split(','),function(i,val){
				  var valArray=val.split('|');
				  currKey=valArray[0];//组
				  currValue=valArray[1];//值
			});
			if(groupNowName == 0){
				window.location.reload();
				return false;
			}

			if(currValue==0){
				$(".CardSelect div[data-param0='0']").css('display','');
				allSelect=0;
				allselectObj.push({key:groupNowName,value:button});
			}else{
				allSelect=-1;
			}
			if(button.hasClass('selected')){
				paramArray = $.grep(paramArray, function(n) {
					return n.key != currKey || n.value!=currValue;
				});
				button.removeClass('selected');
				button.css('color','');
				showDiv[groupNowName] ='';
				var show_div = showDiv;
			}else{
				if(lastGroupName != '' && lastGroupName == groupNowName){
					var lastGroupObj = $('input[data-group='+lastGroupName+']');
					if(lastGroupObj.hasClass('selected')){
						lastButtonObj.removeClass('selected');
						lastGroupObj.css('color','');
					}
				}
				var delArray = new Array();
				if($.inArray(groupNowName,selectedArray) != -1){
					$.each(buttonObj, function(i,val){
					  if(val['key'] == groupNowName){
						if(val['value'].hasClass('selected')){
							val['value'].removeClass('selected');
							val['value'].css('color','');
						}
						delArray.push(i);
					  }
					});
					$.each(delArray, function(i,vl){
						buttonObj.splice(vl,1)	;
					});
					buttonObj.sort();
				}
				$.each(allselectObj, function(i,val){
					if(val['value'].hasClass('selected')){
						val['value'].removeClass('selected');
						val['value'].css('color','');
					}
					delArray.push(i);
				});
				button.addClass('selected');
				button.css('color','red');
				if(currValue==0){
					showDiv={};
					$.each(buttonObj, function(i,val){
					  if(val['key'] != groupNowName){
						if(val['value'].hasClass('selected')){
							val['value'].removeClass('selected');
							val['value'].css('color','');
						}

						buttonObj.splice(i,1);
					  }
					});
				}else{
					paramArray.push({key:currKey,value:currValue});
					if(button.attr("data-s")=="yes"){
						var show_div = new Array();
						$(".divsort").each(function(){
							var attrVal = $(this).attr("data-param"+currKey);
							var divArr = attrVal.split("_");
							if($.inArray(currValue, divArr) > -1){
								show_div.push('[data-param'+currKey+'="'+attrVal+'"]');
							}
						});
						showDiv[groupNowName] = show_div;
					}else{
						showDiv[groupNowName] = '[data-param'+currKey+'='+currValue+']';
					}
					//showDiv[groupNowName] ='[data-param'+currKey+'='+currValue+']';
				}
				buttonObj.push({key:groupNowName,value:button});
				selectedArray.push(groupNowName);
				jQuery.unique(selectedArray);
			}


			var select='';
			$.each(showDiv, function(i,divVal){
				if(divVal != undefined && divVal.constructor !== Array){
					select+=divVal;
				}else if(divVal != undefined){
					show_div = divVal;
				}
			});
			//2014/7/25 修复筛选取消后，查询结果为空的bug
			if(undefined != showDiv.length){
				var s = showDiv.join('');
				if(s==''){
					$(".CardSelect div[data-param0='0']").css('display','');
				}
			}
			//2014/7/25
			if(select.length>0){
				if(show_div == undefined){
					$(".CardSelect div[data-param0='0']"+select).css('display','');
				}else{
					$.each(show_div, function(i,n){
						$(".CardSelect div[data-param0='0']"+select+n).css('display','');
					});
				}
			}else if(select=='' && show_div.length>0){
				$.each(show_div, function(i,n){
					$(".CardSelect div[data-param0='0']"+n).css('display','');
				});
			}
			lastGroupName = groupNowName;
			lastButtonObj = button;
			buttonObj.push({key:groupNowName,value:button});
	   });
	});

	var tag = 1;
	function divsort(divid,divclass,parm,_this){

		var ar=new Array();
        var br=new Array();
        $("."+divclass).each(function(){
            ar[ar.length]=$(this).attr(parm);
        });
        $('.wikitable>tbody>tr>th>span').parent().removeClass('headerSortDown headerSortUp');
		if(tag ==1){
			br=ar.sort(function(a,b){return b-a});
			$(_this).parent().removeClass().addClass('headerSort headerSortDown');
			tag =-1;
		}else{
			br=ar.sort(function(a,b){return a-b});
			$(_this).parent().removeClass().addClass('headerSort headerSortUp');
			tag =1;
		}
        for(var i=br.length-1;i>=0;i--){
            $("."+divid).append($("."+divclass+"["+parm+"="+br[i]+"]"));
        }
	}
