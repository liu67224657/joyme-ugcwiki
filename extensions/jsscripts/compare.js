var bodyh = 0;

var wikikey = 'mt';

var channel = 'wiki';


//获取环境 alpha beta com
var wikipath = location.hostname.substring(location.hostname.lastIndexOf('.')+1);

//获取channel pc还是mobile
if(window.location.href.indexOf('wiki/') != -1 && window.location.href.indexOf('/wiki/') == -1 ){
	channel = 'mwiki';
}

if(location.hostname.substring(0,6) == 'm.wiki'){
	channel = 'mwiki';
}

//获取wikikey

if(location.hostname.substring(0,4) == 'wiki' || location.hostname.substring(0,6) == 'm.wiki'){
	wikikey = location.href.substring(location.href.indexOf('.'+wikipath+'/')+wikipath.length+2,location.href.lastIndexOf('/'));
}else if(location.hostname.substring(0,3) == 'www'){
	wikikey = location.href.substring(location.href.indexOf('wiki/')+5,location.href.lastIndexOf('/'));
}else{
	wikikey = location.hostname.substring(0,location.hostname.indexOf('.'));
}


$(function(){
	$.cookie = function(name, value, options) { 
		if (typeof value != 'undefined') { 
			options = options || {}; 
			if (value === null) { 
				  value = ''; 
				  options = $.extend({}, options); 
				  options.expires = -1; 
			} 
			var expires = ''; 
			if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) { 
				var date; 
				if (typeof options.expires == 'number') { 
					date = new Date(); 
					date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000)); 
				} else { 
					date = options.expires; 
				} 
				expires = '; expires=' + date.toUTCString(); 
			} 
			var path = options.path ? '; path=' + (options.path) : '; path=/'; 
			var domain = options.domain ? '; domain=' + (options.domain) : '; domain=' + location.hostname.substring(location.hostname.indexOf('.')); 
			var secure = options.secure ? '; secure' : ''; 
			document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join(''); 
		} else { 
			var cookieValue = null; 
			if (document.cookie && document.cookie != '') { 
				var cookies = document.cookie.split(';'); 
				for (var i = 0; i < cookies.length; i++) { 
					var cookie = jQuery.trim(cookies[i]); 
					if (cookie.substring(0, name.length + 1) == (name + '=')) { 
						  cookieValue = decodeURIComponent(cookie.substring(name.length + 1)); 
						  break; 
					} 
				} 
			} 
			return cookieValue; 
		} 
	}; 
	card_init();
	bodyh=$(window).height();
	$(window).resize(function() {
		bodyh=$(window).height();
		$('#op_bg').height(bodyh);
	});
	$('.alert_compare_list ul li span').click(function(){
		if($(this).hasClass('compare_sel')){
			$(this).removeClass('compare_sel');
		}else{
			$('.alert_compare_list ul li span').each(function(i,e){
				var _e=$(e);
				if(_e.hasClass('compare_sel')){
						_e.removeClass('compare_sel');
				}
			});
			$(this).addClass('compare_sel');
		}
	});
}); 
function card_sel(obj){
	if(!window.sessionStorage){
		alert('您使用的浏览器不支持哦');
		return false;
	}
	var boxId = $(obj).parent().parent().attr('data-id');
	var urlId = $(obj).parent().parent().attr('data-card');
	var infoId = $(obj).parent().parent().attr('data-cardinfo');
	if(urlId == null || infoId == null || boxId == null){
		alert('配置有误');
	}
	card_compare(urlId,infoId,boxId);
}
function card_compare(urlId,infoId,boxId){
	if($('span[data-id="'+boxId+'"]>label>input').is(':checked') == true){
		compare_cookie = window.sessionStorage.compare;
		if(compare_cookie != null){
			compare = eval("("+compare_cookie+")"); 
			if(compare.length>=3){
				alert('最多对比三个哦=。=');return false;
			}
		}
		$('span[data-id="'+boxId+'"]>label').addClass("compare_on");
		
		var z = 0;
		var compare = [];
		var compare2 = [];
		if(compare_cookie != null){
			compare = eval("("+compare_cookie+")"); 
			//继续
			for(var i=0,j=compare.length;i<j;i++){
				if(urlId == compare[i]['urlid']){
					alert('已加入对比');return false;
				}
			}
			z = j;
		}
		var url = $('span[data-cardurl="'+urlId+'"]').find("a").attr('href');
		var loc = window.location.href;
		if(url == null){
			url = loc;
		}else{
			url = loc.substring(0,loc.lastIndexOf('/')+1)+url;
		}
		compare[z] = {
				'urlid':urlId,
				'boxid':boxId,
				'url':HtmlEncode(url),
				'info':HtmlEncode($('span[data-id="'+infoId+'"]').html())
				};
		if($.cookie('compare') != null){
			compare2 = eval("("+$.cookie('compare')+")"); 
		}
		compare2[z] = {'url':HtmlEncode(url)};
		compare = JSON.stringify(compare);
		window.sessionStorage.compare = compare;
		compare2 = JSON.stringify(compare2);
		$.cookie('compare',compare2);
		card_init();
	}else{
		card_del(urlId);
	}
}
function card_init(){
	if(!window.sessionStorage){
		return false;
	}
	var compare_cookie = window.sessionStorage.compare;
	if(compare_cookie != null){
		compare = eval("("+compare_cookie+")");
		for(var i=0;i<3;i++){
			if(i<compare.length){
				compare[i]['info'] = compare[i]['info']?compare[i]['info']:'';
				compare[i]['urlid'] = compare[i]['urlid']?compare[i]['urlid']:'';
				$('#compare .td-bg'+(i+1)).html('<div class="td-data-dox">'+HtmlDecode(compare[i]['info'])+'<a href="javascript:;" onclick="card_del(\''+compare[i]['urlid']+'\')">删除</a></div>');
			}else{
				$('#compare .td-bg'+(i+1)).html('');
			}
		}
		if(compare.length>0) {
			$('#compare').show();
			$('#wap_float').hide();
		}
		if(compare.length>=2){
			$('#tocompare').addClass("on");
		}else{
			$('#tocompare').removeClass("on");
		}
	}else{
		card_hidden();
		
	}
}
function card_clear(){
	$.cookie('compare' ,null);
	window.sessionStorage.removeItem('compare');
	card_init();
}
function card_del(id){
	var compare_cookie = window.sessionStorage.compare;
	var ncompare = [];
	var ncompare2 = [];
	if(compare_cookie != null){
		compare = eval("("+compare_cookie+")");
		var tmp = 0;
		for(var i=0,j=compare.length;i<j;i++){
			if(i>2) break;
			if(id == compare[i]['urlid']) {
				tmp = 1;
				$('span[data-id="'+compare[i]['boxid']+'"]>label>input').removeAttr("checked");
				$('span[data-id="'+compare[i]['boxid']+'"]>label').removeClass("compare_on");
				//$('#'+compare[i]['boxid']).removeAttr("checked");
				continue;
			}
			if(tmp == 0){
				ncompare[i] = compare[i];
				ncompare2[i] = {'url':compare[i]['url']};
			}else{
				ncompare[i-1] = compare[i];
				ncompare2[i-1] = {'url':compare[i]['url']};
			}
		}
		compare = JSON.stringify(ncompare);
		window.sessionStorage.compare = compare;
		compare2 = JSON.stringify(ncompare2);
		$.cookie('compare',compare2);
		card_init();
	}else{
		alert('已清空');
	}
}
function card_hidden(){
	$('#compare').hide();
	$('#wap_float').show();
}
function card_chakan(){
	var compare_cookie = window.sessionStorage.compare;
	if(compare_cookie != null){
		compare = eval("("+compare_cookie+")");
		if(compare.length>=2){
			window.location.href='http://wiki.joyme.'+wikipath+'/wiki/tools/'+wikikey+'/compare.do';
		}else{
			alert('请选择一个进行对比');
		}
	}else{
		alert('请选择一个进行对比');
	}
}
function view_add(){
	id = location.href.substring(location.href.lastIndexOf('/'),location.href.lastIndexOf('.'));
	window.location.href='http://wiki.joyme.'+wikipath+'/'+channel+'/tools/'+wikikey+id+'/opinion.do';
}
//隐藏显示多选框
function boxdisplay(t){
	if(t == 0){
		$('#op_bg').height(0);
		$('#op_bg').hide();
		$('.alert_compare_wrap').removeClass('acw');
	}else{
		$('#op_bg').height(bodyh);
		$('#op_bg').show();
		$('.alert_compare_wrap').addClass('acw');
	}
}
//多选写入cookie
function boxtocookie(){
	var loc = window.location.href;
	var compare = [];
	compare[0] = {'url':HtmlEncode(loc)};
	$('.compare_sel').each(function(i,e){
		compare[i+1] = {'url':HtmlEncode(loc.substring(0,loc.lastIndexOf('/')+1)+$('span[data-id="'+$(this).attr('data-card')+'"] a').attr('href'))}
	});
	compare = JSON.stringify(compare);
	window.sessionStorage.compare = compare;
	$.cookie('compare',compare);
	box_chakan();
}
function box_chakan(){
	var compare_cookie = window.sessionStorage.compare;
	if(compare_cookie != null){
		compare = eval("("+compare_cookie+")");
		if(compare.length>=2){
			window.location.href='http://wiki.joyme.'+wikipath+'/wiki/tools/'+wikikey+'/compare.do?channel=wx';
		}else{
			alert('请选择一个进行对比');
		}
	}else{
		alert('请选择一个进行对比');
	}
}

function HtmlEncode(text)
{
	return text.replace(/&/g, '&amp').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function HtmlDecode(text)
{
	return text.replace(/&amp;/g, '&').replace(/&quot;/g, '"').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
}