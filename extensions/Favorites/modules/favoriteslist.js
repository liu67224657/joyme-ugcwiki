/*global mw,$*/

var fav = {
	loadMorePage:2,
	isLoading:false,
	'getMore':function(className, data){
		data.action = 'favoritelist';
		data.format = 'json';
		data.pageno = fav.loadMorePage;
		$.ajax( {
			url: mw.config.get( 'wgScriptPath' ) + '/api.php',
			data: data,
			cache: false
		} ).done( function( response ) {
			if(response.favoritelist){
				var html = response.favoritelist.li;
				$('.loading').remove();
				if(html){
					$('.'+className).append(html);
					fav.isLoading = false;
					fav.loadMorePage++;
				}else{
					var sum = $('#favtotal').attr('data-count');
					if(sum > 20){
						$('.'+className).append('<div style="text-align:center;"><span>没有更多了</span></div>');
					}
				}
			}else{
				console.log(response);
			}
		} );
		
	},
	
	'unfavorite':function(data, _this){
		mw.ugcwikiutil.confirmDialog('确认取消收藏吗？',function (action) {
			if(action=="accept"){
				unFav(data, _this);
			}
		});
		
		function unFav(){
			data.action = 'favorite';
			data.format = 'json';
			$.ajax( {
				url: '/'+data.wikikey + '/api.php',
				type: "POST",
				data: data,
				cache: false
			} ).done( function( response ) {
				if(response.favorite){
					var title = response.favorite.title;
					if(title){
						$(_this).parent().remove();
						var n = $('#favtotal').attr('data-count')-1;
						$('#favtotal').html('收藏页面数量:' + n);
						$('#favtotal').attr('data-count', n);
						if($('.like-list>dd').length<1){
							var url = 'http://'+window.location.host+'/home/%E7%89%B9%E6%AE%8A:%E6%94%B6%E8%97%8F%E5%88%97%E8%A1%A8';
							window.location.href = url;
						}
					}
				}else{
					console.log(response);
				}
			} );
		}
	}
};

	

$( document ).ready( function() {
	function IsPC(){
	    var userAgentInfo = navigator.userAgent;
	    var Agents = new Array("Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod");
	    var flag = true;
	    for (var v = 0; v < Agents.length; v++) {
	        if (userAgentInfo.indexOf(Agents[v]) > 0) { flag = false; break; }
	    }
	    return flag;
    }
	var uid = $('input[name="uid"]').val();
	var title = mw.config.get('wgPageName');
	var wikikey = $('select[name="wikikey"]').find('option:selected').val();
	var data = {'uid':uid,'wikikey':wikikey};
	if(!IsPC()){
		$(window).scroll(function(ev){
			var $this = $(this);
			ev.stopPropagation();
			ev.preventDefault();
			var footerH = $('.footer').height();
			var sTop=$this.scrollTop();
			var sHeight=$('body').get(0).scrollHeight -footerH;
			var sMainHeight=$(this).height();
			var sNum=sHeight-sMainHeight;
			var loadTips='<div class="loading" style="text-align:center;"><span>正在加载...</span></div>';
			if(sTop>=sNum && !fav.isLoading){
				fav.isLoading=true;
				var className = 'list-item';
				$('.'+className).append(loadTips);
				fav.getMore(className,data);
			};
		});
	}

	$( 'body' ).on( 'change', 'select', function() {
		$('#favform').submit();
	} )
	
	.on('click', 'span[class="unfavorite"]', function(){
		var title = $(this).attr('data-title');
		var wikikey = $(this).attr('data-wikikey');
		var wikins = $(this).attr('data-ns');
		var data = {'title':title, 'wikikey':wikikey, 'wikins':wikins, 'unfavorite':1};
		fav.unfavorite(data, $(this));
	});
} );