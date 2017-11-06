/*global mw,$*/
/**
 * JavaScript for the ClickLike extension.
 * Rewritten by Jack Islander <memcached@sina.cn> to be more
 * object-oriented.
 *
 * @file
 * @date 18:04 2016/6/24
 */
var ClickLike = {
	submitted: 0,
	/**
	 * Submit a new ClickLike.
	 */
	submit: function() {
		if ( ClickLike.submitted === 0 ) {
			ClickLike.submitted = 1;
			var pageID = mw.config.get( 'wgArticleId' );

			$.ajax( {
				url: mw.config.get( 'wgScriptPath' ) + '/api.php',
				data: { 'action': 'clicklike', 'format': 'json', 'pageID': pageID },
				cache: false
			} ).done( function( response ) {
				if ( response.clicklike.ok ) {
					$('#ClickLike>span').html(response.clicklike.ok.like_count);
					$('.djd>.zan').html('赞:'+response.clicklike.ok.like_count);
				} else {
					window.alert( response.responseText );
					ClickLike.submitted = 0;
				}
			} );
		}else{
			var msg = mw.msg( 'clicklike-once' );
			alert(msg);
		}
	}
};

$( document ).ready( function() {
	$( 'body' ).on( 'click', '#ClickLike', function() {
		ClickLike.submit();
	} )
} );