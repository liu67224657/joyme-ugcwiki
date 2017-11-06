$(function () {
	function makeMyTool() {
		console.log("mytool start ---3");
		//Create and register command
		/*var myTemplate = [ {
			type: 'mwTransclusionBlock',
			attributes: {
				mw: {
					parts: [ {
						template: {
							target: {
								href: 'Template:MyTemplate',
								wt: 'MyTemplate'
							},
							params: {
								1: {
									wt: 'my parameter'
								}
							}
						}
					} ]
				}
			}
		}, {
			type: '/mwTransclusionBlock'
		} ];*/

		ve.ui.commandRegistry.register(
			/*new ve.ui.Command( 'mycommand', 'content', 'insert', {
				args: [ myTemplate, false, true ],
				supportedSelections: [ 'linear' ]
			} )*/
			new ve.ui.Command( 'mycommand', 'content', 'insert',
				{
					args: [ {
						type: 'mwTransclusionBlock',
						attributes: {
							mw: {
								parts: [
									{
										template: {
											target: {
												wt: 'Test',
												href: './Template:Test'
											},
											params: {
												1: {
													wt: 'Hello, world!'
												}
											},
											i: 0
										}
									}
								]
							},
							originalMw: '{"parts":[{"template":{"target":{"wt":"Test","href":"./Template:Test"},"params":{"1":{"wt":"Hello, world!"}},"i":0}}]}'
						}
					}, false, true ],
					supportedSelections: [ 'linear' ]
				}
			)
		);

		//Create and register wikitext command
		/*if ( ve.ui.wikitextCommandRegistry ) {
			ve.ui.wikitextCommandRegistry.register(
				// new ve.ui.Command( 'mycommand', 'mwWikitext', 'wrapSelection', {
				new ve.ui.Command( 'mycommand', 'wrapSelection', {
					args: [ '{{MyTemplate|', '}}', 'my parameter' ],
					supportedSelections: [ 'linear' ]
				} )
			);
		}*/

		//Create and register tool
		function MyTool() {
			MyTool.parent.apply( this, arguments );
		}
		OO.inheritClass( MyTool, ve.ui.MWTransclusionDialogTool );

		MyTool.static.name = 'mytool';
		MyTool.static.group = 'object';
		MyTool.static.title = 'My tool';
		MyTool.static.commandName = 'mycommand';
		ve.ui.toolFactory.register( MyTool );
		console.log("mytool:"+MyTool);
	}

//Initialize
	mw.loader.using( 'ext.visualEditor.desktopArticleTarget.init' ).done( function() { console.log("mytool start");
		makeMyTool();
		/*mw.libs.ve.addPlugin( function() { console.log("mytool start ---1");
			// mw.loader.using( [ 'ext.visualEditor.core', 'ext.visualEditor.mwwikitext', 'ext.visualEditor.mwtransclusion' ] )
			mw.loader.using( [ 'ext.visualEditor.core', 'ext.visualEditor.mwtransclusion' ] )
				.done( function() { console.log("mytool start ---2");
					makeMyTool();
				} );
		} );*/
	} );


	ve.ui.MWTestMediaDialogTool = function VeUiMWMediaDialogTool() {
		ve.ui.MWTestMediaDialogTool.super.apply( this, arguments );
	};
	OO.inheritClass( ve.ui.MWTestMediaDialogTool, ve.ui.FragmentWindowTool );
	ve.ui.MWTestMediaDialogTool.static.name = 'testmedia';
	ve.ui.MWTestMediaDialogTool.static.group = 'object';
	ve.ui.MWTestMediaDialogTool.static.icon = 'image';
	ve.ui.MWTestMediaDialogTool.static.title ='testmedia';
	ve.ui.MWTestMediaDialogTool.static.modelClasses = [ ve.dm.MWBlockImageNode, ve.dm.MWInlineImageNode ];
	ve.ui.MWTestMediaDialogTool.static.commandName = 'testmedia';
	ve.ui.MWTestMediaDialogTool.static.autoAddToCatchall = false;
	ve.ui.MWTestMediaDialogTool.static.autoAddToGroup = false;
	ve.ui.toolFactory.register( ve.ui.MWTestMediaDialogTool );

	ve.ui.commandRegistry.register(
		new ve.ui.Command(
			'testmedia', 'window', 'open',
			{ args: [ 'media' ], supportedSelections: [ 'linear' ] }
		)
	);
	function makeUnlinkYearsTool() {
		//Function to modify wikitext
		function unlinkYears( wikitext ) {
			return wikitext.replace( /\[\[(\d+)\]\]/g, '$1' )
				.replace( /\[\[\d+\|(.*?)\]\]/g, '$1' );
		}

		//Create and register command
		function UnlinkYearsCommand() {
			UnlinkYearsCommand.parent.call( this, 'unlinkYears' );
		}
		OO.inheritClass( UnlinkYearsCommand, ve.ui.Command );

		UnlinkYearsCommand.prototype.execute = function( surface ) {
			var surfaceModel, fragment, range, wikitext, data = [], onCompleteText;
			//Get fragment to work on
			surfaceModel = surface.getModel();
			fragment = surfaceModel.getFragment();
			if ( fragment.getSelection().isCollapsed() ) {
				surfaceModel.setLinearSelection(
					new ve.Range( 0, surfaceModel.getDocument().data.getLength() )
				);
				fragment = surfaceModel.getFragment();
				onCompleteText = true;
			}
			//Visual mode
			if ( ve.init.target.getSurface().getMode() !== 'source' ) {
				fragment.annotateContent(
					'clear',
					fragment.getAnnotations( true ).filter( function( annotation ) {
						return annotation.getType() === 'link/mwInternal' &&
							/^\d+$/.test( annotation.getAttribute( 'normalizedTitle' ) );
					} )
				);
				return true;
			}
			//Source mode
			wikitext = fragment.getText( true ).replace( /^\n/, '' ).replace( /\n\n/g, '\n' );
			wikitext = unlinkYears( wikitext );
			wikitext.split( '' ).forEach( function( c ) {
				if ( c === '\n' ) {
					data.push( { type: '/paragraph' } );
					data.push( { type: 'paragraph' } );
				} else {
					data.push( c );
				}
			} );
			if ( onCompleteText ) {
				fragment.insertContent( wikitext );
			} else {
				fragment.insertContent( data );
			}
			if ( onCompleteText ) {
				fragment.collapseToStart().select();
			}
			return true;
		};

		ve.ui.commandRegistry.register( new UnlinkYearsCommand() );

		//Create, register and insert tool
		function UnlinkYearsTool() {
			UnlinkYearsTool.parent.apply( this, arguments );
		}
		OO.inheritClass( UnlinkYearsTool, ve.ui.Tool );

		UnlinkYearsTool.static.name = 'unlinkYears';
		UnlinkYearsTool.static.group = 'utility';
		UnlinkYearsTool.static.title = 'Unlink years';
		UnlinkYearsTool.static.icon = 'noWikiText';
		UnlinkYearsTool.static.commandName = 'unlinkYears';
		UnlinkYearsTool.static.autoAddToCatchall = false;
		UnlinkYearsTool.static.deactivateOnSelect = false;

		UnlinkYearsTool.prototype.onUpdateState = function() {
			UnlinkYearsTool.parent.prototype.onUpdateState.apply( this, arguments );
			this.setActive( false );
		};

		ve.ui.toolFactory.register( UnlinkYearsTool );

		ve.init.mw.DesktopArticleTarget.static.actionGroups[ 1 ].include.push( 'unlinkYears' );
	}

//Initialize
	mw.loader.using( 'ext.visualEditor.desktopArticleTarget.init' ).done( function() {
		mw.libs.ve.addPlugin( function() {
			mw.loader.using( [ 'ext.visualEditor.core' ] )
				.done( function() {
					makeUnlinkYearsTool();
				} );
		} );
	} );

});