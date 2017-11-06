/**
 * Additional mw.Api methods to assist with (un)favoriting wiki pages.
 * @since 1.19
 */


( function ( mw, $ ) {
	// favorite | unfavorite
	$('body').on('click', '#ca-favorite', function(){
		var id = $(this).attr('id');
		var uid = mw.config.get('wgUserId') || 0;
		if(uid){
			doFavFn(id);
		}else{
			// mw.loginbox.login();
			loginDiv();
		}
	});
	
	function doFavFn(id){
		var action = id.replace('ca-', '');
		var title = mw.config.get( 'wgRelevantPageName' );
		var wikins = mw.config.get('wgNamespaceNumber');
		var data = { 'action': 'favorite', 'format': 'json', 'title': title, 'wikins':wikins };
		if(action == 'unfavorite'){
			data.unfavorite = 1;
			var bttext = '收藏';
			var idattr = 'ca-favorite';
		}else{
			var bttext = '取消收藏';
			var idattr = 'ca-unfavorite';
		}
		$.ajax( {
			type: "POST",
			url: mw.config.get( 'wgScriptPath' ) + '/api.php',
			data: data,
			cache: false
		} ).done( function( response ) {
			// mw.notify( $(response.favorite.message) );
			if(action == 'favorite'){
				$('#ca-favorite').addClass('shouc-done');
				$('#ca-favorite').html('取消收藏');
				$('#ca-favorite').attr('id', 'ca-unfavorite');
				// favoriteDialog('收藏成功');
			}else{
				$('#ca-unfavorite').removeClass('shouc-done');
				$('#ca-favorite').html('');
				$('#ca-unfavorite').attr('id', 'ca-favorite');
				// favoriteDialog('取消收藏成功');
			}
			$('#'+id).html(bttext);
			$('#'+id).attr('id', idattr);
		} );
	}
	
	$('body').on('click', '#ca-unfavorite', function(){
		var id = $(this).attr('id');
		mw.ugcwikiutil.confirmDialog('确认取消收藏吗？',function (action) {
			if(action=="accept"){
				doFavFn(id);
			}
		});
	});
	
	/**
	 * @context {mw.Api}
	 */
	function doFavoriteInternal( page, success, err, addParams ) {
		var params = {
			action: 'favorite',
			title: String( page ),
			token: mw.user.tokens.get( 'favorite' ),
			uselang: mw.config.get( 'wgUserLanguage' )
		};
		function ok( data ) {
			// this doesn't appear to be needed, and it breaks 1.23.
			//success( data.favorite ); 
			
		}
		if ( addParams ) {
			$.extend( params, addParams );
		}
		return this.post( params, { ok: ok, err: err } );
	}

	$.extend( mw.Api.prototype, {
		/**
		 * Convinience method for 'action=favorite'.
		 *
		 * @param page {String|mw.Title} Full page name or instance of mw.Title
		 * @param success {Function} Callback to which the favorite object will be passed.
		 * Favorite object contains properties 'title' (full pagename), 'favorited' (boolean) and
		 * 'message' (parsed HTML of the 'addedfavoritetext' message).
		 * @param err {Function} Error callback (optional)
		 * @return {jqXHR}
		 */
		favorite: function ( page, success, err ) {
			return doFavoriteInternal.call( this, page, success, err );
		},
		/**
		 * Convinience method for 'action=favorite&unfavorite=1'.
		 *
		 * @param page {String|mw.Title} Full page name or instance of mw.Title
		 * @param success {Function} Callback to which the favorite object will be passed.
		 * Favorite object contains properties 'title' (full pagename), 'favorited' (boolean) and
		 * 'message' (parsed HTML of the 'removedfavoritetext' message).
		 * @param err {Function} Error callback (optional)
		 * @return {jqXHR}
		 */
		unfavorite: function ( page, success, err ) {
			return doFavoriteInternal.call( this, page, success, err, { unfavorite: 1 } );
		}

	} );

}( mediaWiki, jQuery ) );