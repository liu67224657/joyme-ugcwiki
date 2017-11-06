/*global mw,$*/

var JC = {
	loadMorePage:2,
	isLoading:false,
	'getMore':function(className, data){
		data.pageno = JC.loadMorePage;
		data.action = 'contriblist';
		data.format = 'json';
		$.ajax( {
			url: mw.config.get( 'wgScriptPath' ) + '/api.php',
			data: data,
			cache: false
		} ).done( function( response ) {
			if(response.contriblist){
				var html =response.contriblist.li;
				if(html){
					$('.'+className).append(html);
					JC.isLoading = false;
					JC.loadMorePage++;
				}else{
					var sum = parseInt($('#editSum').attr('data-editSum'));
					if(sum>25){
						$('.'+className).append('<div style="text-align:center;"><span>没有更多了</span></div>');
					}
				}
			}else{
				console.log(response);
			}
		} );
		$('.loading').remove();
	}
};

	

$( document ).ready( function() {
	var userid = $('input[name="userid"]').val();
	var title = mw.config.get('wgPageName');
	var year = $('select[name="year"]').find('option:selected').val();
	var month = $('select[name="month"]').find('option:selected').val();
	var actype = $('select[name="actype"]').find('option:selected').val();
	var wikikey = $('select[name="wiki_key"]').find('option:selected').val();
	var data = {'userid':userid,'year':year,'month':month,'actype':actype,'wikikey':wikikey};
	function IsPC(){
	    var userAgentInfo = navigator.userAgent;
	    var Agents = new Array("Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod");
	    var flag = true;
	    for (var v = 0; v < Agents.length; v++) {
	        if (userAgentInfo.indexOf(Agents[v]) > 0) { flag = false; break; }
	    }
	    return flag;
    }
	if(!IsPC()){
		$(window).scroll(function(ev){
			var $this = $(this);
			ev.stopPropagation();
			ev.preventDefault();
			var footerH = $('.footer').height();
			var sTop=$this.scrollTop();
			var sHeight=$('body').get(0).scrollHeight -footerH -150;
			var sMainHeight=$(this).height();
			var sNum=sHeight-sMainHeight;
			var loadTips='<div class="loading"><span>正在加载...</span></div>';
			if(sTop>=sNum && !JC.isLoading){
				JC.isLoading=true;
				var className = 'list-item';
				$('.'+className).append(loadTips);
				JC.getMore(className,data);
			};
		});
	}
	
	$( 'body' ).on( 'change', 'select', function() {
		$('#jcform').submit();
	} );
} );