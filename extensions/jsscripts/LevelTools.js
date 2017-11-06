function pchange()  
{
	iv1 = $('input[name="addition"]:checked').attr('iv1') == undefined?1:$('input[name="addition"]:checked').attr('iv1'); 
	iv2 = $('input[name="addition"]:checked').attr('iv2') == undefined?1:$('input[name="addition"]:checked').attr('iv2'); 
	iv3 = $('input[name="addition"]:checked').attr('iv3') == undefined?1:$('input[name="addition"]:checked').attr('iv3'); 
	$('.plevelspan').each(function(i,v){
		$(v).attr('data-min')==''?1:$(v).attr('data-min');
		$(v).attr('data-min')==''?maxLevel:$(v).attr('data-max');
		ylv = lv;
		if($(v).attr('data-min')!='' && lv<=$(v).attr('data-min')){
			//$(v).html($(v).attr('data-min'));
			lv = $(v).attr('data-min');
		}else if($(v).attr('data-max')!='' && lv>=$(v).attr('data-max')){
			//$(v).html($(v).attr('data-max'));
			lv = $(v).attr('data-max');
		}
		// 修改此处--重新计算alv
		if($("#mswiki").html() != undefined){
			var ilv = parseInt(98*(parseInt(lv)-1)/(parseInt($("#maxLevel").val())-1)) + 1;
			alv = $('#data-lv'+ilv).val()?$('#data-lv'+ilv).val():ilv;
		}else{
			alv = $('#data-lv'+lv).val()?$('#data-lv'+lv).val():lv;
		}
		html = eval($(v).attr('data-method'));
		$(v).html(html);
		lv = ylv;
		if($(v).parent()[0].tagName.toUpperCase() == 'B'){
			tmpwidth=parseInt($(v).parent().attr('data-b-width'))+_x/25;
			tmpwidth=tmpwidth>=90?90:tmpwidth;
			$(v).parent().css({"width":tmpwidth+"%"});
		}
	});
	  
}
function formatFloat(src, pos)
{
	return Math.round(src*Math.pow(10, pos))/Math.pow(10, pos);
} 
var sliderW,offset,maxLevel,dlev,_x,ylv,lv,alv,dlev,html,w,iv1,iv2,iv3;
$(document).ready(function()  
{  
	sliderW = $('#aslider').offset().left-$('#slider').offset().left;
	offset = $('#slider').offset();
	maxLevel = $('#maxLevel').val();
	dlev = $('#dlev').val();
	_x = 1;
	ylv = lv = alv = dlev; //ylv原始 lv当前 alv系数 dlev默认等级
	html = 0;
	w = parseInt(lv/maxLevel*100)+'%';
	iv1 = $('input[name="addition"]:checked').attr('iv1') == undefined?1:$('input[name="addition"]:checked').attr('iv1'); 
	iv2 = $('input[name="addition"]:checked').attr('iv2') == undefined?1:$('input[name="addition"]:checked').attr('iv2'); 
	iv3 = $('input[name="addition"]:checked').attr('iv3') == undefined?1:$('input[name="addition"]:checked').attr('iv3'); 
	$('#userLevel').val(lv);
	$('#dslider').css({'width':w});
	$('#aslider').css({'left':w});
	
	var u = navigator.userAgent; 
	var mobile = !!u.match(/AppleWebKit.*Mobile.*/);//||!!u.match(/AppleWebKit/);
	
	if(mobile){
		var div = document.getElementById('slider');
		div.addEventListener('touchmove',touchMove,false);
		div.addEventListener('touchend',pchange,false);
	}else{
		var div = $('#slider');
	    div.mousedown(function(e){  
	        $(document).bind("mousemove",function(ev){  
	        	ev = ev || window.event;
	        	touchMove(ev);
	        });  
	    });  
	      
	    $(document).mouseup(function(){
	    	pchange();
	    	$(this).unbind("mousemove");
	    });
	}
	$('#slider').click(function(e){
		ev = e || window.event;
        _x = ev.pageX - offset.left;
        if(_x<=0){
        	_x = 1;
        }else if(_x>=sliderW){
        	_x=100;
        }else{
        	_x = _x/sliderW*100;
        }
        $("#aslider").css({"left":_x+"%","top":"-0.3em"});
        $("#dslider").css({"width":_x+"%"});
        lv = parseInt(_x/100*maxLevel);
		lv = lv<1?1:lv;
    	$('#userLevel').val(lv);
	});
    
    function touchMove(event) {
		event.preventDefault();//阻止其他事件
		if (mobile) {
			var ev = event.targetTouches[0];  // 把元素放在手指所在的位置
		}else{
			var ev = event || window.event;
		}
		$("#aslider").stop();
        
        _x = ev.pageX - offset.left;
        if(_x<=0){
        	_x = 1;
        }else if(_x>=sliderW){
        	_x=100;
        }else{
        	_x = _x/sliderW*100;
        }
        $("#aslider").css({"left":_x+"%","top":"-0.3em"});
        $("#dslider").css({"width":_x+"%"});
        lv = parseInt(_x/100*maxLevel);
		lv = lv<1?1:lv;
    	$('#userLevel').val(lv);
	}
    
    $('input[name="addition"]').click(function()  
    {
    	 pchange();
    });  
	pchange();
	
});




