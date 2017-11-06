<?php
/**
 * MediaWikiBootstrap Skin
 *
 * @file
 * @ingroup Skins
 * @author Nasir Khan Saikat http://nasirkhn.com
 */

if ( !defined( 'MEDIAWIKI' ) ) {
        die( 'This is an extension to the MediaWiki package and cannot be run standalone.' );
}

$wgExtensionCredits['skin'][] = array(
        'path'          => __FILE__,
        'name'          => 'MediaWikiBootstrap2', 
        'namemsg'       => 'skinname-mediawikibootstrap', 
        'version'       => '1.1.0',
        'url'           => 'https://www.mediawiki.org/wiki/Skin:MediaWikiBootstrap',
        'author'        => '[https://mediawiki.org/wiki/User:nasirkhan Nasir Khan Saikat]',
        'descriptionmsg'=> 'mediawikibootstrap-desc',
        'license'       => 'GPL-2.0+',
);


$wgValidSkinNames['mediawikibootstrap2']         = 'MediaWikiBootstrap2';
$wgAutoloadClasses['SkinMediaWikiBootstrap2']    = __DIR__ . '/MediaWikiBootstrap2.skin.php';
$wgMessagesDirs['MediaWikiBootstrap2']           = __DIR__ . '/i18n';

$wgResourceModules['skins.mediawikibootstrap2'] = array(
		'styles' => array(
				'MediaWikiBootstrap2/css/bootstrap.css'      => array( ),
				'MediaWikiBootstrap2/css/font-awesome.min.css'   => array( ),
				'MediaWikiBootstrap2/css/screen.css'             => array( ),
				'MediaWikiBootstrap2/css/style.css'             => array( ),
				'MediaWikiBootstrap2/css/print.css'              => array( 'media' => 'print' ),
				'MediaWikiBootstrap2/css/joymedialog.css'              => array(),
				'MediaWikiBootstrap2/css/joyme-style.css'             => array( ),
				'MediaWikiBootstrap2/css/daoju.css'             => array( ),
        'MediaWikiBootstrap2/css/wiki-nav.css'             => array( ),
        'MediaWikiBootstrap2/css/comment.css'             => array( ),
		),
		'scripts' => array(
				'MediaWikiBootstrap2/js/bootstrap.min.js',
				'MediaWikiBootstrap2/js/fastclick.min.js',
				'MediaWikiBootstrap2/js/mediawikibootstrap.js',
				'MediaWikiBootstrap2/js/ugcwikiutil.js',
				'MediaWikiBootstrap2/js/bscroll.min.js',
				// 'MediaWikiBootstrap2/js/layer/layer.js',
				// 'MediaWikiBootstrap2/js/UserCenter.js',
				// 'MediaWikiBootstrap2/js/mousewheel.min.js',
				// 'MediaWikiBootstrap2/js/nav-script-tl.js',
				// 'MediaWikiBootstrap2/js/scrollbar.min.js',
				// 'MediaWikiBootstrap2/js/horizontalMove.js',
				'MediaWikiBootstrap2/js/action.js',
				'MediaWikiBootstrap2/js/comment.js'
				
		),
		'remoteBasePath'        => &$GLOBALS['wgStylePath'],
		'localBasePath'         => &$GLOBALS['wgStyleDirectory'],
		'position' => 'top',
);
 



// # Default options to customize skin
 $wgMediaWikiBootstrapSkinLoginLocation = 'footer';
 $wgMediaWikiBootstrapSkinAnonNavbar = false;
 $wgMediaWikiBootstrapSkinUseStandardLayout = false;
 $wgMediaWikiBootstrapSkinDisplaySidebarNavigation = false;
// # Show print/export in navbar by default
 $wgMediaWikiBootstrapSkinSidebarItemsInNavbar = array( 'coll-print_export' );
