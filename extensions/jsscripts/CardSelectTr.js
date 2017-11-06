var paramArray = new Array();
	var selectedArray = new Array();
	var buttonObj = new Array();
	var lastGroupName = '';
	var lastButtonObj = new Array();
	var showDiv = new Array();
	var allSelect = -1;
	var allselectObj= new Array();
	//$('tr[data-param3=2][data-param2=200][data-param1=mt][data-param1=mt]').css('display','');
	$(document).ready(function(){

		$('.wikitable>tbody>tr>th>span').parent().addClass('headerSort');	
		$('.cardSelectOption').click(function(){
			$('.wikitable>tbody>tr>th>span').parent().removeClass('headerSortDown headerSortUp');
			var button=$(this);
			$('#CardSelectTr>tbody>tr').css('display','none');
			$('#CardSelectTabHeader').css('display','');
			var param=$(this).attr('data-option');
			var groupNowName=button.attr('data-group');
			var currKey='';
			var currValue='';
			var ponseOther = new Array();
			$.each(param.split(','),function(i,val){
				  var valArray=val.split('|');
				  currKey=valArray[0];
				  currValue=valArray[1]; 				 
			});		
			if(currValue==0){
				$('#CardSelectTr>tbody>tr').css('display',''); 
				allSelect=0;			
				allselectObj.push({key:groupNowName,value:button});
			}else{
				allSelect=-1;
			}
			if(button.hasClass('selected')){	
				// 按钮选中		
				paramArray = $.grep(paramArray, function(n) {
					return n.key != currKey || n.value!=currValue;
				});
				button.removeClass('selected');
				button.css('color','');
				var arr = showDiv[groupNowName];
				// alert(currValue);
				var currValueIn = $.inArray(currValue,arr)
				if(currValueIn>-1){
					arr.splice(currValueIn,1);
					showDiv[groupNowName] = arr;
				}
			}else{	
				// 按钮未选中		
				if(lastGroupName != '' && lastGroupName == groupNowName){
					// 上次选中组和当前组相同
					// var lastGroupObj = $('input[data-group='+lastGroupName+']');
					// if(lastGroupObj.hasClass('selected')){					
					// 	lastButtonObj.removeClass('selected');
					// 	lastGroupObj.css('color','');
					// }						
				}	
				var delArray = new Array();// 看似array 实际只有一个值
				if($.inArray(groupNowName,selectedArray) != -1){	
					// 已选的组包含当前的点击的组
					// $.each(buttonObj, function(i,val){	
					//   if(val['key'] == groupNowName){
					// 		if(val['value'].hasClass('selected')){					
					// 			val['value'].removeClass('selected');
					// 			val['value'].css('color','');
					// 		}
					// 		delArray.push(i);						
					//   }					 					  
					// });	
					// $.each(delArray, function(i,vl){					
					// 	buttonObj.splice(vl,1)	;
					// });		
					// buttonObj.sort();									
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
				}
				
				if(currValue!=0){
					paramArray.push({key:currKey,value:currValue});
					if(!(showDiv[groupNowName] instanceof Array)){
						showDiv[groupNowName] = [];
					}
					if(button.attr("data-s")=="yes"){
						// var show_div = new Array();
						// $(".divsort").each(function(){
						// 	var attrVal = $(this).attr("data-param"+currKey);
						// 	var divArr = attrVal.split("_");
						// 	if($.inArray(currValue, divArr) > -1){
						// 		show_div.push('[data-param'+currKey+'="'+attrVal+'"]');
						// 	}
						// });
						// showDiv[groupNowName].push(show_div);
					}else{
						// showDiv[groupNowName].push('[data-param'+currKey+'='+currValue+']');
						showDiv[groupNowName].push(currValue);// [1:['圣','暗']]
					}
					//showDiv[groupNowName] ='[data-param'+currKey+'='+currValue+']';
				}
				buttonObj.push({key:groupNowName,value:button});
				selectedArray.push(groupNowName);
				jQuery.unique(selectedArray);				
			}
				
		
			// var select='';
			// $.each(showDiv, function(i,divVal){
			// 	if(divVal != undefined && divVal.constructor !== Array){
			// 		select+=divVal;
			// 	}else if(divVal != undefined){
			// 		show_div = divVal;
			// 	}
			// });
			// if(select.length>0){
				
			// 	if(show_div == undefined){
			// 		$('tr'+select).css('display','');
			// 	}else{
			// 		$.each(show_div, function(i,n){
			// 			$('tr'+select+n).css('display','');
			// 		});	
			// 	}
			// }else if(select=='' && show_div.length>0){
			// 	$.each(show_div, function(i,n){
			// 		$('tr'+n).css('display','');
			// 	});
			// }

			$('#CardSelectTr tr').each(function(index,el){
				var flag = true;
				debugger;
				for ( key in showDiv){
					var showDivArr = showDiv[key];
					// if(showDivArr.length==0){
					// 	continue;
					// }
					var str = $(el).data('param'+key);
					if(typeof str == 'number'){
						str = str.toString(10);
					}
					console.log(el);
					if(str!==undefined){
						var arr = str.split(',');
						if(arr.length>0){
							var obj = {};
							if(arr.length >= showDivArr.length){
							  // 被筛选元素data-param数>=筛选条件
								for(var j = 0;j < arr.length;j++){
									obj[$.trim(arr[j])] = true;
								}
								for(var m = 0; m < showDivArr.length;m++){
									if(!obj[showDivArr[m]]){

										flag = false;
										break;
									}
								}
								if(flag){
									$(el).show();
								}
							}else{
								flag=false;
							}	
						}
						
					}
					if(!flag){
						$(el).hide();
						break;

					}
				}
				
			})
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
			br=ar.sort(function(a,b){return a-b});
			$(_this).parent().removeClass().addClass('headerSort headerSortDown');
			tag =-1;
		}else{
			br=ar.sort(function(a,b){return b-a});
			$(_this).parent().removeClass().addClass('headerSort headerSortUp');
			tag =1;
		}
        for(var i=br.length-1;i>=0;i--){
            $("."+divid).append($("."+divclass+"["+parm+"='"+br[i]+"']"));
        }
	}
