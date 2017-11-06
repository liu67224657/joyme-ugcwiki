/*global mw,$*/
/**
 * JavaScript for the ShortComments extension.
 * Rewritten by Jack Islander <memcached@sina.cn> to be more
 * object-oriented.
 *
 * @file
 * @date 18:04 2016/6/24
 */
var ShortComments = {
	submitted: 0,
	clicktimes: 0,
	/**
	 * Submit a new ShortComments.
	 */
	submit: function() {
		if ( ShortComments.submitted === 0 ) {
			ShortComments.submitted = 1;
			var pageID = mw.config.get( 'wgArticleId' );
			var shortcomment = $('#shortcomment').val();

			$.ajax( {
				url: mw.config.get( 'wgScriptPath' ) + '/api.php',
				data: { 'action': 'shortcomments', 'format': 'json', 'pageID': pageID, 'text':shortcomment },
				cache: false
			} ).done( function( response ) {
				if ( response.shortcomments.ok ) {
					var res = response.shortcomments.ok;
					if(res.msg != undefined){
						alert(res.msg);
					}else{
						//(<em>0</em>)
						var css = ['tbb', 'jgl', 'zb', 'tbzb'];
						var index = Math.floor((Math.random()*css.length));
						var html = '<span class="'+css[index]+'" data-id='+res.id+'>'+shortcomment+'</span>';
						$('#shortcommentlist').prepend(html);
						$('#shortcomment').val('');
						// $('#shortcommentlist').append(html);
					}
				} else {
					window.alert( response.responseText );
					ShortComments.submitted = 0;
				}
			} );
		}else{
			var msg = mw.msg( 'shortcomments-once' );
			alert(msg);
		}
	},
	
	// click like
	clicklike : function(pscID){
		if ( ShortComments.clicktimes === 0 ) {
			ShortComments.clicktimes = 1;
			var pageID = mw.config.get( 'wgArticleId' );

			$.ajax( {
				url: mw.config.get( 'wgScriptPath' ) + '/api.php',
				data: { 'action': 'shortcommentslike', 'format': 'json', 'pageID': pageID, 'pscID':pscID },
				cache: false
			} ).done( function( response ) {
				if ( response.shortcommentslike.ok ) {
					$('#shortcommentlist span[data-id='+pscID+'] em').html(response.shortcommentslike.ok.like_count);
				} else {
					window.alert( response.responseText );
					ShortComments.clicktimes = 0;
				}
			} );
		}else{
			var msg = mw.msg( 'shortcomments-once' );
			alert(msg);
		}
	}
};

$( document ).ready( function() {
	$( 'body' ).on( 'click', '#shortcommentbtn', function() {
		ShortComments.submit();
	} )
	
	// click like
	.on('click', '#shortcommentlist span', function(){
		var that = $( this );
		ShortComments.clicklike(
			that.data( 'id' )
		);
	})
} );