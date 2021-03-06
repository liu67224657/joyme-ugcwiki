/*!
 * VisualEditor MediaWiki ArticleTargetLoader.
 *
 * @copyright 2011-2016 VisualEditor Team and others; see AUTHORS.txt
 * @license The MIT License (MIT); see LICENSE.txt
 */

/**
 * Target loader.
 *
 * Light-weight loader that loads ResourceLoader modules for VisualEditor
 * and HTML and page data from the API. Also handles plugin registration.
 *
 * @class mw.libs.ve.targetLoader
 * @singleton
 */
( function () {
	var prefName, prefValue,
		conf = mw.config.get( 'wgVisualEditorConfig' ),
		pluginCallbacks = [],
		modules = [
			'ext.visualEditor.mwcore',
			'ext.visualEditor.mwlink',
			'ext.visualEditor.mwformatting',
			'ext.visualEditor.data',
			'ext.visualEditor.mwtransclusion',
			'ext.visualEditor.mwgallery',
			'ext.visualEditor.mwalienextension',
			'ext.visualEditor.language',
			'ext.visualEditor.icons',
			'ext.visualEditor.mytool'
		]
			// Add modules from $wgVisualEditorPluginModules
			.concat( conf.pluginModules );

	// Allow signing posts in select namespaces
	if ( conf.signatureNamespaces.length ) {
		modules.push( 'ext.visualEditor.mwsignature' );
	}

	// Add preference modules
	for ( prefName in conf.preferenceModules ) {
		prefValue = mw.user.options.get( prefName );
		// Check "0" (T89513)
		if ( prefValue && prefValue !== '0' ) {
			modules.push( conf.preferenceModules[ prefName ] );
		}
	}

	mw.libs.ve = mw.libs.ve || {};

	mw.libs.ve.targetLoader = {
		/**
		 * Add a plugin module or callback.
		 *
		 * If a module name is passed, that module will be loaded alongside the other modules.
		 *
		 * If a callback is passed, it will be executed after the modules have loaded. The callback
		 * may optionally return a jQuery.Promise; if it does, loading won't be complete until
		 * that promise is resolved.
		 *
		 * @param {string|Function} plugin Plugin module name or callback
		 */
		addPlugin: function ( plugin ) {
			if ( typeof plugin === 'string' ) {
				modules.push( plugin );
			} else if ( $.isFunction( plugin ) ) {
				pluginCallbacks.push( plugin );
			}
		},

		/**
		 * Load modules needed for VisualEditor, as well as plugins.
		 *
		 * This loads the base VE modules as well as any registered plugin modules.
		 * Once those are loaded, any registered plugin callbacks are executed,
		 * and we wait for all promises returned by those callbacks to resolve.
		 *
		 * @return {jQuery.Promise} Promise resolved when the loading process is complete
		 */
		loadModules: function () {
			ve.track( 'trace.moduleLoad.enter' );
			return mw.loader.using( modules )
				.then( function () {
					ve.track( 'trace.moduleLoad.exit' );
					pluginCallbacks.push( ve.init.platform.getInitializedPromise.bind( ve.init.platform ) );
					// Execute plugin callbacks and collect promises
					return $.when.apply( $, pluginCallbacks.map( function ( callback ) {
						return callback();
					} ) );
				} );
		},

		/**
		 * Request the page HTML and various metadata from the MediaWiki API (which will use
		 * Parsoid or RESTBase).
		 *
		 * @param {string} pageName Page name to request
		 * @param {string} [oldid] Old revision ID, current if omitted
		 * @param {string} [targetName] Optional target name for tracking
		 * @param {boolean} [modified] The page was been modified before loading (e.g. in source mode)
		 * @return {jQuery.Promise} Abortable promise resolved with a JSON object
		 */
		requestPageData: function ( pageName, oldid, targetName, modified ) {
			var start, apiXhr, restbaseXhr, apiPromise, restbasePromise, dataPromise, pageHtmlUrl,
				switched = false,
				fromEditedState = false,
				data = {
					action: 'visualeditor',
					paction: ( conf.fullRestbaseUrl || conf.restbaseUrl ) ? 'metadata' : 'parse',
					page: pageName,
					uselang: mw.config.get( 'wgUserLanguage' )
				};

			// Only request the API to explicitly load the currently visible revision if we're restoring
			// from oldid. Otherwise we should load the latest version. This prevents us from editing an
			// old version if an edit was made while the user was viewing the page and/or the user is
			// seeing (slightly) stale cache.
			if ( oldid !== undefined ) {
				data.oldid = oldid;
			}
			// Load DOM
			start = ve.now();
			ve.track( 'trace.apiLoad.enter' );

			apiXhr = new mw.Api().get( data );
			apiPromise = apiXhr.then( function ( data, jqxhr ) {
				ve.track( 'trace.apiLoad.exit' );
				ve.track( 'mwtiming.performance.system.apiLoad', {
					bytes: $.byteLength( jqxhr.responseText ),
					duration: ve.now() - start,
					cacheHit: /hit/i.test( jqxhr.getResponseHeader( 'X-Cache' ) ),
					targetName: targetName
				} );
				return data;
			} );

			if ( conf.fullRestbaseUrl || conf.restbaseUrl ) {
				ve.track( 'trace.restbaseLoad.enter' );
				if (
					conf.fullRestbaseUrl &&
					$( '#wpTextbox1' ).val() &&
					!$( '[name=wpSection]' ).val()
				) {
					switched = true;
					fromEditedState = modified;
					window.onbeforeunload = null;
					$( window ).off( 'beforeunload' );
					restbaseXhr = $.ajax( {
						url: conf.fullRestbaseUrl + 'v1/transform/wikitext/to/html/' +
							encodeURIComponent( pageName ) +
							( oldid === undefined ? '' : '/' + oldid ),
						type: 'POST',
						data: {
							title: pageName,
							oldid: oldid,
							wikitext: $( '#wpTextbox1' ).val(),
							stash: 'true'
						},
						// Should be synchronised with ApiVisualEditor.php
						headers: { Accept: 'text/html; charset=utf-8; profile="mediawiki.org/specs/html/1.2.0"' },
						dataType: 'text'
					} );
				} else {
					if ( conf.fullRestbaseUrl ) {
						pageHtmlUrl = conf.fullRestbaseUrl + 'v1/page/html/';
					} else {
						pageHtmlUrl = conf.restbaseUrl;
					}
					restbaseXhr = $.ajax( {
						url: pageHtmlUrl + encodeURIComponent( pageName ) +
							( oldid === undefined ? '' : '/' + oldid ) + '?redirect=false',
						type: 'GET',
						// Should be synchronised with ApiVisualEditor.php
						headers: { Accept: 'text/html; charset=utf-8; profile="mediawiki.org/specs/html/1.2.0"' },
						dataType: 'text'
					} );
				}
				restbasePromise = restbaseXhr.then(
					function ( data, status, jqxhr ) {
						ve.track( 'trace.restbaseLoad.exit' );
						ve.track( 'mwtiming.performance.system.restbaseLoad', {
							bytes: $.byteLength( jqxhr.responseText ),
							duration: ve.now() - start,
							targetName: targetName
						} );
						return [ data, jqxhr.getResponseHeader( 'etag' ) ];
					},
					function ( response ) {
						if ( response.status === 404 ) {
							// Page does not exist, so let the user start with a blank document.
							return $.Deferred().resolve( [ '', undefined ] ).promise();
						} else {
							window.alert( mw.msg( 'visualeditor-loaderror-message', 'HTTP ' + response.status ) );

							mw.log.warn( 'RESTBase load failed: ' + response.statusText );
						}
					}
				);

				dataPromise = $.when( apiPromise, restbasePromise )
					.then( function ( apiData, restbaseData ) {
						if ( apiData.visualeditor ) {
							apiData.visualeditor.content = restbaseData[ 0 ];
							apiData.visualeditor.etag = restbaseData[ 1 ];
							apiData.visualeditor.switched = switched;
							apiData.visualeditor.fromEditedState = fromEditedState;
						}
						return apiData;
					} )
					.promise( { abort: function () {
						apiXhr.abort();
						restbaseXhr.abort();
					} } );
			} else {
				dataPromise = apiPromise.promise( { abort: apiXhr.abort } );
			}

			return dataPromise;
		}
	};
}() );
